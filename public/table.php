<?php
// table.php - Versión Final Robusta (Compatible con cualquier permiso de BD)

// 1. CONFIGURACIÓ I SEGURETAT
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

// Bloquejar accés si no està loguejat
require_login();

$current_user_id = current_user_id();
$is_admin = is_admin(); // Recorda: isard i pirineus són admins a auth.php

// 2. VALIDAR TAULA
$tables = get_tables($conn);
$table = $_GET['table'] ?? null;

if (!$table || !in_array($table, $tables, true)) {
    header("Location: index.php");
    exit;
}

// Intentar crear columna (silenciós si falla per permisos)
ensure_ownership_column($conn, $table);

// Obtenir estructura REAL actual de la taula
$columns = get_columns($conn, $table);
$pk_cols = get_primary_keys_from_columns($columns);
$safe_table = $conn->real_escape_string($table);

// DETECTOR INTEL·LIGENT: ¿Existeix realment la columna created_by?
// Això evita l'error "Unknown column" si no tenim permisos per crear-la.
$has_owner_col = false;
foreach ($columns as $c) {
    if ($c['Field'] === 'created_by') $has_owner_col = true;
}

// 3. GESTIÓ DE PETICIONS (POST)
$message = '';
$msg_type = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error de seguretat: Token invàlid.");
    }

    $action = $_POST['action'] ?? '';

    try {
        // --- INSERT ---
        if ($action === 'insert') {
            $cols = []; $vals = []; $types = '';
            
            // NOMÉS intentem guardar el propietari si la columna existeix
            if ($has_owner_col) {
                $cols[] = 'created_by'; 
                $vals[] = $current_user_id; 
                $types = 'i';
            }

            foreach ($columns as $c) {
                $field = $c['Field'];
                // Ignorem camps auto_increment i created_by (ja gestionat)
                if (strpos($c['Extra'], 'auto_increment') !== false || $field === 'created_by') continue;
                
                if (isset($_POST[$field])) {
                    $cols[] = "`$field`";
                    $vals[] = $_POST[$field] === '' ? null : $_POST[$field];
                    $types .= coltype_to_bindchar($c['Type']);
                }
            }
            
            // Si no hi ha columnes per inserir (tots nulls), evitar error SQL
            if (empty($cols) && empty($vals)) {
                 $sql = "INSERT INTO `$safe_table` () VALUES ()"; // Insert buit
            } else {
                 $sql = "INSERT INTO `$safe_table` (" . implode(',', $cols) . ") VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")";
            }

            $stmt = $conn->prepare($sql);
            if(!$stmt) throw new Exception("Error SQL: " . $conn->error);
            
            if (!empty($vals)) {
                bind_params_dynamic($stmt, $types, $vals);
            }
            
            if ($stmt->execute()) {
                $message = "Registre creat correctament.";
                $msg_type = 'success';
            } else throw new Exception($stmt->error);

        // --- UPDATE / DELETE ---
        } elseif ($action === 'update' || $action === 'delete') {
            
            // A. Identificar fila amb PKs
            $where_parts = []; $pk_vals = []; $pk_types = '';
            foreach ($pk_cols as $pk) {
                if (!isset($_POST['pk_' . $pk])) throw new Exception("Falta PK: $pk");
                $where_parts[] = "`$pk` = ?";
                $pk_vals[] = $_POST['pk_' . $pk];
                foreach($columns as $c) if($c['Field'] === $pk) $pk_types .= coltype_to_bindchar($c['Type']);
            }

            // B. Verificar Propietat (NOMÉS SI LA COLUMNA EXISTEIX)
            if ($has_owner_col && !$is_admin) {
                $check_sql = "SELECT created_by FROM `$safe_table` WHERE " . implode(' AND ', $where_parts);
                $check_stmt = $conn->prepare($check_sql);
                bind_params_dynamic($check_stmt, $pk_types, $pk_vals);
                $check_stmt->execute();
                $owner_res = $check_stmt->get_result()->fetch_assoc();
                
                if (!$owner_res) throw new Exception("Registre no trobat.");
                
                // Si té amo i no ets tu, error
                if ($owner_res['created_by'] !== null && $owner_res['created_by'] != $current_user_id) {
                     throw new Exception("⛔ No tens permís per modificar aquest registre.");
                }
            }
            
            // C. Executar Acció
            if ($action === 'delete') {
                $sql = "DELETE FROM `$safe_table` WHERE " . implode(' AND ', $where_parts);
                $stmt = $conn->prepare($sql);
                bind_params_dynamic($stmt, $pk_types, $pk_vals);
                if ($stmt->execute()) {
                    $message = "Registre eliminat.";
                    $msg_type = 'success';
                } else throw new Exception($stmt->error);

            } elseif ($action === 'update') {
                $set_parts = []; $set_vals = []; $set_types = '';
                foreach ($columns as $c) {
                    $f = $c['Field'];
                    if (in_array($f, $pk_cols) || $f === 'created_by') continue;
                    if (isset($_POST[$f])) {
                        $set_parts[] = "`$f` = ?";
                        $set_vals[] = $_POST[$f] === '' ? null : $_POST[$f];
                        $set_types .= coltype_to_bindchar($c['Type']);
                    }
                }
                if ($set_parts) {
                    $sql = "UPDATE `$safe_table` SET " . implode(',', $set_parts) . " WHERE " . implode(' AND ', $where_parts);
                    $stmt = $conn->prepare($sql);
                    $all_vals = array_merge($set_vals, $pk_vals);
                    $all_types = $set_types . $pk_types;
                    bind_params_dynamic($stmt, $all_types, $all_vals);
                    if ($stmt->execute()) {
                        $message = "Actualitzat correctament. Tornant...";
                        $msg_type = 'success';
                        echo "<script>setTimeout(() => window.location.href='?table=".urlencode($table)."', 1000);</script>";
                    } else throw new Exception($stmt->error);
                }
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $msg_type = 'error';
    }
}

// 4. MODE DE VISTA: ¿EDITAR O LLISTAR?
$view_mode = 'list';
$edit_data = null;

if (isset($_GET['edit'])) {
    try {
        $edit_pk_vals = json_decode(rawurldecode($_GET['edit']), true);
        if (!$edit_pk_vals) throw new Exception("ID invàlid.");

        $where_parts = []; $vals = []; $types = '';
        foreach ($pk_cols as $pk) {
            $where_parts[] = "`$pk` = ?";
            $vals[] = $edit_pk_vals[$pk];
            foreach($columns as $col) if($col['Field'] === $pk) $types .= coltype_to_bindchar($col['Type']);
        }
        
        $stmt = $conn->prepare("SELECT * FROM `$safe_table` WHERE " . implode(' AND ', $where_parts));
        bind_params_dynamic($stmt, $types, $vals);
        $stmt->execute();
        $edit_data = $stmt->get_result()->fetch_assoc();
        
        if ($edit_data) {
            if ($has_owner_col && !$is_admin && $edit_data['created_by'] !== null && $edit_data['created_by'] != $current_user_id) {
                $message = "No pots editar aquest registre perquè no és teu.";
                $msg_type = 'error';
                $view_mode = 'list';
            } else {
                $view_mode = 'edit';
            }
        } else {
            $message = "Registre no trobat.";
        }

    } catch (Exception $e) { $message = "Error: ".$e->getMessage(); }
}


// 5. CONSULTA DE LLISTAT
$data = [];
$total_records = 0; $total_pages = 0;
$search_query = $_GET['q'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

if ($view_mode === 'list') {
    $per_page = 50;
    $offset = ($page - 1) * $per_page;
    $where_clauses = [];
    
    if ($search_query) {
        $search_sql = build_search_where($conn, $columns, $search_query);
        if ($search_sql) $where_clauses[] = $search_sql;
    }
    $sql_where = empty($where_clauses) ? '' : "WHERE " . implode(' AND ', $where_clauses);

    try {
        $count_res = $conn->query("SELECT COUNT(*) as c FROM `$safe_table` $sql_where");
        $total_records = $count_res ? $count_res->fetch_assoc()['c'] : 0;
    } catch(Exception $e) { $total_records = 0; }
    
    $total_pages = ceil($total_records / $per_page);

    $sql = "SELECT * FROM `$safe_table` $sql_where ORDER BY 1 DESC LIMIT $per_page OFFSET $offset";
    try {
        $res = $conn->query($sql);
        if ($res) while ($row = $res->fetch_assoc()) $data[] = $row;
    } catch (Exception $e) { $message = "Error carregant dades: " . $e->getMessage(); }
}

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <title>Taula: <?php echo htmlspecialchars($table); ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans min-h-screen pb-10">

  <nav class="bg-white shadow border-b px-6 py-3 sticky top-0 z-20 flex flex-wrap justify-between items-center gap-4">
    <div class="flex items-center gap-4">
        <a href="index.php" class="text-gray-500 hover:text-blue-600 transition flex items-center gap-1">
            &larr; Enrere
        </a>
        <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-sm font-mono"><?php echo htmlspecialchars($table); ?></span>
        </h1>
    </div>
    
    <?php if ($view_mode === 'list'): ?>
    <form method="GET" class="flex-grow max-w-xl relative">
        <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cercar..." class="w-full pl-10 pr-4 py-2 border rounded-full shadow-sm outline-none focus:ring-2 focus:ring-blue-500">
    </form>
    <?php endif; ?>
  </nav>

  <main class="max-w-[95%] mx-auto mt-6">
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded border-l-4 shadow-sm <?php echo $msg_type === 'success' ? 'bg-green-50 border-green-500 text-green-800' : 'bg-red-50 border-red-500 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($view_mode === 'edit' && $edit_data): ?>
        <div class="bg-white rounded-lg shadow-lg max-w-4xl mx-auto overflow-hidden">
            <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h2 class="text-white font-bold text-lg">✏️ Editant Registre</h2>
                <a href="?table=<?php echo urlencode($table); ?>" class="text-blue-100 hover:text-white text-sm">Cancel·lar</a>
            </div>
            <form method="post" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <?php foreach ($pk_cols as $pk): ?>
                    <input type="hidden" name="pk_<?php echo $pk; ?>" value="<?php echo htmlspecialchars($edit_data[$pk]); ?>">
                <?php endforeach; ?>

                <?php foreach ($columns as $c): 
                    $f = $c['Field'];
                    if (in_array($f, $pk_cols) || $f === 'created_by') continue;
                    $val = $edit_data[$f];
                ?>
                    <div class="flex flex-col">
                        <label class="text-sm font-semibold text-gray-600 mb-1"><?php echo $f; ?></label>
                        <?php if (strpos($c['Type'], 'text') !== false): ?>
                            <textarea name="<?php echo $f; ?>" rows="4" class="border rounded p-2 focus:ring-2 focus:ring-blue-500 outline-none"><?php echo htmlspecialchars($val ?? ''); ?></textarea>
                        <?php else: ?>
                            <input type="text" name="<?php echo $f; ?>" value="<?php echo htmlspecialchars($val ?? ''); ?>" class="border rounded p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="md:col-span-2 border-t pt-4 flex justify-end gap-3">
                    <a href="?table=<?php echo urlencode($table); ?>" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Cancel·lar</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 shadow">Guardar Canvis</button>
                </div>
            </form>
        </div>

    <?php else: ?>
        
        <div class="bg-white rounded shadow-sm border border-gray-200 mb-6">
            <details class="group">
                <summary class="flex justify-between p-4 bg-gray-50 cursor-pointer hover:bg-gray-100 select-none">
                    <span class="font-bold text-green-700 flex items-center gap-2">➕ Afegir Nou Registre</span>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <div class="p-6 border-t bg-white">
                    <form method="post" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input type="hidden" name="action" value="insert">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <?php foreach ($columns as $c): 
                            if ($c['Extra'] === 'auto_increment' || $c['Field'] === 'created_by') continue;
                        ?>
                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase truncate block"><?php echo $c['Field']; ?></label>
                                <input type="text" name="<?php echo $c['Field']; ?>" class="w-full border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-green-500 outline-none">
                            </div>
                        <?php endforeach; ?>
                        <div class="md:col-span-4 flex justify-end mt-2">
                            <button type="submit" class="bg-green-600 text-white font-bold py-2 px-6 rounded hover:bg-green-700">Crear</button>
                        </div>
                    </form>
                </div>
            </details>
        </div>

        <div class="bg-white border rounded shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 whitespace-nowrap">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50 z-10 border-r">Accions</th>
                        <?php foreach ($columns as $c): ?>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo htmlspecialchars($c['Field']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php if (empty($data)): ?>
                        <tr><td colspan="100" class="px-6 py-8 text-center text-gray-500">No hi ha dades.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): 
                            $owner = $row['created_by'] ?? null;
                            $can_edit = $is_admin || ($has_owner_col && $owner == $current_user_id);
                            // SI LA COLUMNA NO EXISTE, DEJAMOS EDITAR A TODOS (O puedes restringirlo a solo admin)
                            if (!$has_owner_col) $can_edit = true;
                        ?>
                        <tr class="<?php echo $can_edit ? 'hover:bg-blue-50' : 'bg-gray-50 text-gray-500'; ?> group transition">
                            <td class="px-4 py-2 sticky left-0 bg-inherit border-r z-10 flex items-center gap-3">
                                <?php if ($can_edit): ?>
                                    <?php 
                                        $pk_vals_assoc = [];
                                        foreach($pk_cols as $pk) $pk_vals_assoc[$pk] = $row[$pk];
                                        $edit_link = '?table=' . urlencode($table) . '&edit=' . rawurlencode(json_encode($pk_vals_assoc));
                                    ?>
                                    <a href="<?php echo $edit_link; ?>" class="text-blue-600 hover:scale-110 transition transform" title="Editar">✏️</a>

                                    <form method="post" onsubmit="return confirm('Eliminar?');" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <?php foreach ($pk_cols as $pk): ?>
                                            <input type="hidden" name="pk_<?php echo $pk; ?>" value="<?php echo htmlspecialchars($row[$pk]); ?>">
                                        <?php endforeach; ?>
                                        <button class="text-red-500 hover:scale-110 transition transform" title="Eliminar">🗑</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-300 cursor-not-allowed">🔒</span>
                                <?php endif; ?>
                            </td>
                            <?php foreach ($columns as $c): 
                                $val = $row[$c['Field']];
                                if ($c['Field'] === 'created_by' && $has_owner_col) $val = ($val == $current_user_id) ? 'Tu' : $val;
                            ?>
                                <td class="px-4 py-2 text-sm max-w-xs truncate"><?php echo htmlspecialchars($val ?? ''); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center mt-6 gap-2">
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?table=<?php echo urlencode($table); ?>&page=<?php echo $i; ?>" class="px-3 py-1 border rounded <?php echo $i==$page?'bg-blue-600 text-white':'bg-white'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
  </main>
</body>
</html>
