<?php
// table.php - visualització i CRUD genèric per a una taula
require 'config.php';
require 'helpers.php';

session_start();

// Generar token CSRF senzill
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];

$tables = get_tables($conn);
$table = isset($_GET['table']) ? $_GET['table'] : null;
if (!$table || !in_array($table, $tables, true)) {
    die("Taula invàlida o no seleccionada.");
}

// obtenir metadades
$columns = get_columns($conn, $table);
if (empty($columns)) {
    die("No s'han pogut obtenir columnes per la taula " . h($table));
}
$pk_cols = get_primary_keys_from_columns($columns);

// Simple map per traduir noms de camp a català (afegir més segons calgui)
function field_label(string $f): string {
    static $map = [
        'register_id' => 'ID',
        'id' => 'ID',
        'name' => 'Nom',
        'institution_id' => 'ID Centre',
        'institution_name' => 'Centre',
        'created' => 'Creat',
        'modified' => 'Modificat',
        'addresses_road_name' => 'Carrer',
        'addresses_town' => 'Municipi',
        'addresses_zip_code' => 'Codi postal',
        'addresses_district_name' => 'Districte',
        'address' => 'Adreça',
        'geo_epgs_4326_lat' => 'Latitud',
        'geo_epgs_4326_lon' => 'Longitud',
        'values_attribute_name' => 'Atribut',
        'values_value' => 'Valor',
        'secondary_filters_name' => 'Categoria',
        'values_description' => 'Descripció',
        'timetable' => 'Horari',
        'start_date' => "Data d'inici",
        'end_date' => "Data de fi"
    ];
    return $map[$f] ?? ucfirst(str_replace(['_', '-'], [' ', ' '], $f));
}

// Helper: decideix si el tipus és data
function is_date_type(string $type): bool {
    $t = strtolower($type);
    return str_contains($t, 'date') || str_contains($t, 'timestamp');
}

// processar accions POST: insert / update / delete
$message = '';
$errors = [];
$preserve = []; // per preservar valors al formulari d'insert

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check (només si hi ha token)
    $posted_csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $posted_csrf)) {
        $message = "Token de seguretat invàlid. Torna-ho a intentar.";
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'insert') {
            // preparar insert: excloure columnes AUTO_INCREMENT amb Extra posant 'auto_increment'
            $insert_cols = [];
            $insert_types = '';
            $insert_values = [];
            foreach ($columns as $c) {
                if (strpos($c['Extra'], 'auto_increment') !== false) continue;
                $field = $c['Field'];
                // conservar per a repintat del formulari en cas d'error
                $preserve[$field] = $_POST[$field] ?? '';
                if (isset($_POST[$field])) {
                    $insert_cols[] = "`{$field}`";
                    $insert_types .= coltype_to_bindchar($c['Type']);
                    $insert_values[] = $_POST[$field] === '' ? null : $_POST[$field];
                }
            }
            if (!empty($insert_cols)) {
                $placeholders = implode(',', array_fill(0, count($insert_cols), '?'));
                $safe_table = $conn->real_escape_string($table);
                $sql = "INSERT INTO `{$safe_table}` (" . implode(',', $insert_cols) . ") VALUES ({$placeholders})";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    if ($insert_types === '') $insert_types = str_repeat('s', count($insert_values));
                    bind_params_dynamic($stmt, $insert_types, $insert_values);
                    if ($stmt->execute()) {
                        $message = "Registre insertat correctament.";
                        $preserve = []; // esborrar valors preservats
                    } else {
                        $message = "Error insertant: " . h($stmt->error);
                    }
                    $stmt->close();
                } else {
                    $message = "Error preparing INSERT: " . h($conn->error);
                }
            } else {
                $message = "No hi ha columnes per inserir.";
            }
        } elseif ($action === 'update') {
            // necessita PKs
            if (empty($pk_cols)) {
                $message = "No es pot actualitzar: la taula no té clau primària definida.";
            } else {
                $set_parts = [];
                $set_types = '';
                $set_values = [];
                foreach ($columns as $c) {
                    $f = $c['Field'];
                    if (in_array($f, $pk_cols, true)) continue; // no posar PK a SET
                    if (isset($_POST[$f])) {
                        $set_parts[] = "`{$f}` = ?";
                        $set_types .= coltype_to_bindchar($c['Type']);
                        $set_values[] = $_POST[$f] === '' ? null : $_POST[$f];
                    }
                }
                // bind per PKs
                $where_parts = [];
                $where_types = '';
                $where_values = [];
                foreach ($pk_cols as $pk) {
                    if (!isset($_POST['pk_' . $pk])) {
                        $message = "Falta valor de la clau primària {$pk}.";
                        break;
                    }
                    $where_parts[] = "`{$pk}` = ?";
                    // trobar tipus del pk
                    foreach ($columns as $c) {
                        if ($c['Field'] === $pk) {
                            $where_types .= coltype_to_bindchar($c['Type']);
                            break;
                        }
                    }
                    $where_values[] = $_POST['pk_' . $pk];
                }
                if ($message === '') {
                    if (empty($set_parts)) {
                        $message = "No hi ha camps per actualitzar.";
                    } else {
                        $safe_table = $conn->real_escape_string($table);
                        $sql = "UPDATE `{$safe_table}` SET " . implode(',', $set_parts) . " WHERE " . implode(' AND ', $where_parts) . " LIMIT 1";
                        $stmt = $conn->prepare($sql);
                        if ($stmt) {
                            $types = $set_types . $where_types;
                            $params = array_merge($set_values, $where_values);
                            bind_params_dynamic($stmt, $types, $params);
                            if ($stmt->execute()) {
                                $message = "Registre actualitzat.";
                            } else {
                                $message = "Error actualitzant: " . h($stmt->error);
                            }
                            $stmt->close();
                        } else {
                            $message = "Error preparing UPDATE: " . h($conn->error);
                        }
                    }
                }
            }
        } elseif ($action === 'delete') {
            if (empty($pk_cols)) {
                $message = "No es pot eliminar: la taula no té clau primària definida.";
            } else {
                $where_parts = [];
                $where_types = '';
                $where_values = [];
                foreach ($pk_cols as $pk) {
                    if (!isset($_POST['pk_' . $pk])) {
                        $message = "Falta valor de la clau primària {$pk}.";
                        break;
                    }
                    $where_parts[] = "`{$pk}` = ?";
                    foreach ($columns as $c) {
                        if ($c['Field'] === $pk) {
                            $where_types .= coltype_to_bindchar($c['Type']);
                            break;
                        }
                    }
                    $where_values[] = $_POST['pk_' . $pk];
                }
                if ($message === '') {
                    $safe_table = $conn->real_escape_string($table);
                    $sql = "DELETE FROM `{$safe_table}` WHERE " . implode(' AND ', $where_parts) . " LIMIT 1";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        bind_params_dynamic($stmt, $where_types, $where_values);
                        if ($stmt->execute()) {
                            $message = "Registre eliminat.";
                        } else {
                            $message = "Error eliminant: " . h($stmt->error);
                        }
                        $stmt->close();
                    } else {
                        $message = "Error preparing DELETE: " . h($conn->error);
                    }
                }
            }
        }
    }
}

// obtenir dades per llistat (limit)
$limit = 200;
$safe_table = $conn->real_escape_string($table);
$data = [];
$res = $conn->query("SELECT * FROM `{$safe_table}` LIMIT {$limit}");
if ($res) {
    while ($r = $res->fetch_assoc()) $data[] = $r;
    $res->free();
} else {
    $message = "Error consultant la taula: " . h($conn->error);
}

// util per seleccionar valor previst (preserve post)
function pv($field, $row = null) {
    global $preserve;
    if (isset($preserve[$field])) return h($preserve[$field]);
    if ($row !== null && array_key_exists($field, $row)) return h($row[$field]);
    return '';
}
?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <title>Taula <?php echo h($table); ?> - CRUD</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Tailwind Play CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
  <div class="max-w-7xl mx-auto p-6">
    <a class="text-slate-600 hover:text-slate-900" href="index.php">&larr; Tornar</a>
    <h2 class="text-2xl font-semibold mt-4 mb-4">Taula: <span class="font-mono text-slate-700"><?php echo h($table); ?></span></h2>

    <?php if ($message): ?>
      <div class="mb-4 p-3 rounded border bg-white text-slate-800"><?php echo h($message); ?></div>
    <?php endif; ?>

    <!-- Nova secció: Afegir registre amb acordió i millor estil -->
    <div class="mb-6">
      <div class="bg-white p-4 rounded shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <strong class="text-lg">Afegir nou registre</strong>
          <div class="text-sm text-slate-500"><?php echo count($columns); ?> camps</div>
        </div>

        <details class="group" open>
          <summary class="cursor-pointer flex items-center justify-between p-3 rounded bg-slate-50 hover:bg-slate-100">
            <span class="text-sm text-slate-700">Obrir formulari d'inserció</span>
            <svg class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </summary>

          <form method="post" class="space-y-4 p-4" autocomplete="off" novalidate>
            <input type="hidden" name="action" value="insert">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <?php foreach ($columns as $c): ?>
                <?php
                  // no mostrar camps auto_increment en el formulari d'inserció
                  if (strpos($c['Extra'], 'auto_increment') !== false) continue;
                  $field = $c['Field'];
                  $type = $c['Type'];
                  $value = pv($field);
                ?>
                <div>
                  <label for="f_<?php echo h($field); ?>" class="block text-sm font-medium text-slate-700 mb-1">
                    <?php echo h(field_label($field)); ?>
                    <?php if ($c['Null'] === 'NO' && $c['Default'] === null): ?>
                      <span class="text-rose-600" title="Camp obligatori">*</span>
                    <?php endif; ?>
                  </label>

                  <?php if (is_date_type($type)): ?>
                    <input id="f_<?php echo h($field); ?>" type="date" name="<?php echo h($field); ?>" value="<?php echo $value; ?>" class="w-full border rounded p-2 text-sm">
                  <?php elseif (stripos($type, 'text') !== false || stripos($type, 'blob') !== false): ?>
                    <textarea id="f_<?php echo h($field); ?>" name="<?php echo h($field); ?>" rows="3" class="w-full border rounded p-2 text-sm"><?php echo $value; ?></textarea>
                  <?php else: ?>
                    <input id="f_<?php echo h($field); ?>" type="text" name="<?php echo h($field); ?>" value="<?php echo $value; ?>" maxlength="255" class="w-full border rounded p-2 text-sm" >
                  <?php endif; ?>
                  <?php if (!empty($c['Comment'])): ?>
                    <div class="text-xs text-slate-400 mt-1"><?php echo h($c['Comment']); ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-3">
              <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Insertar
              </button>
              <a href="import.php?table=<?php echo urlencode($table); ?>" class="ml-3 text-sm text-slate-600 hover:underline">Importar CSV/JSON a aquesta taula</a>
              <?php if (!empty($preserve)): ?>
                <span class="ml-auto text-sm text-amber-600">S'ha preservat l'entrada després d'un error.</span>
              <?php endif; ?>
            </div>
          </form>
        </details>
      </div>
    </div>

    <h3 class="text-lg font-medium mb-2">Registres (màx <?php echo $limit; ?>)</h3>

    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <?php foreach ($columns as $c): ?>
              <th class="px-3 py-2 text-left text-sm font-medium text-slate-600 border-b"><?php echo h(field_label($c['Field'])); ?></th>
            <?php endforeach; ?>
            <th class="px-3 py-2 text-left text-sm font-medium text-slate-600 border-b">Accions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-100">
          <?php if (empty($data)): ?>
            <tr><td class="p-4" colspan="<?php echo count($columns)+1; ?>">No hi ha dades a la taula.</td></tr>
          <?php else: ?>
            <?php foreach ($data as $row): ?>
              <tr>
                <?php foreach ($columns as $c): $f = $c['Field']; ?>
                  <td class="px-3 py-2 align-top text-sm text-slate-700"><?php echo nl2br(h($row[$f] ?? '')); ?></td>
                <?php endforeach; ?>
                <td class="px-3 py-2 align-top text-sm">
                  <?php if (!empty($pk_cols)): ?>
                    <?php
                      // construir parametres PK de forma segura per GET edit
                      $pkvals = array_intersect_key($row, array_flip($pk_cols));
                      $edit_param = rawurlencode(json_encode($pkvals, JSON_UNESCAPED_UNICODE));
                    ?>
                    <a class="text-blue-600 hover:underline mr-3" href="?table=<?php echo urlencode($table); ?>&edit=<?php echo $edit_param; ?>">Editar</a>
                    <form method="post" class="inline" onsubmit="return confirm('Eliminar registre?');">
                      <?php foreach ($pk_cols as $pk): ?>
                        <input type="hidden" name="pk_<?php echo h($pk); ?>" value="<?php echo h($row[$pk]); ?>">
                      <?php endforeach; ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                      <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                    </form>
                  <?php else: ?>
                    <span class="text-amber-600">No editable (no PK)</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php
    // Si s'ha demanat editar un registre via GET edit=JSON(pk=>val)
    if (isset($_GET['edit']) && !empty($pk_cols)) {
        $edit_raw = rawurldecode($_GET['edit']);
        $edit_pk_vals = json_decode($edit_raw, true);
        if (is_array($edit_pk_vals)) {
            // obtenir fila actual
            $where_parts = [];
            $types = '';
            $values = [];
            foreach ($pk_cols as $pk) {
                if (!isset($edit_pk_vals[$pk])) {
                    echo "<p class='text-red-600 mt-4'>Falta PK {$pk} per a l'edició.</p>";
                    $edit_pk_vals = null;
                    break;
                }
                $where_parts[] = "`{$pk}` = ?";
                // troba tipus
                foreach ($columns as $c) {
                    if ($c['Field'] === $pk) {
                        $types .= coltype_to_bindchar($c['Type']);
                        break;
                    }
                }
                $values[] = $edit_pk_vals[$pk];
            }
            if ($edit_pk_vals) {
                $safe_table = $conn->real_escape_string($table);
                $sql = "SELECT * FROM `{$safe_table}` WHERE " . implode(' AND ', $where_parts) . " LIMIT 1";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    bind_params_dynamic($stmt, $types, $values);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $rowdata = $res->fetch_assoc();
                    $stmt->close();
                    if ($rowdata) {
                        // renderitzar formulari d'edició
                        ?>
                        <div class="mt-6 bg-white p-4 rounded shadow">
                          <h3 class="text-lg font-medium mb-3">Editar registre</h3>
                          <form method="post" class="space-y-3">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                              <?php foreach ($columns as $c):
                                  $f = $c['Field'];
                                  $val = $rowdata[$f] ?? '';
                              ?>
                                <div>
                                  <label class="block text-sm font-medium text-slate-700 mb-1"><?php echo h(field_label($f)); ?></label>
                                  <?php if (is_date_type($c['Type'])): ?>
                                    <input type="date" name="<?php echo h($f); ?>" value="<?php echo h($val); ?>" class="w-full border rounded p-2 text-sm">
                                  <?php elseif (stripos($c['Type'], 'text') !== false): ?>
                                    <textarea name="<?php echo h($f); ?>" class="w-full border rounded p-2 text-sm"><?php echo h($val); ?></textarea>
                                  <?php else: ?>
                                    <input type="text" name="<?php echo h($f); ?>" value="<?php echo h($val); ?>" class="w-full border rounded p-2 text-sm">
                                  <?php endif; ?>
                                </div>
                              <?php endforeach; ?>
                            </div>

                            <?php foreach ($pk_cols as $pk): ?>
                              <input type="hidden" name="pk_<?php echo h($pk); ?>" value="<?php echo h($rowdata[$pk]); ?>">
                            <?php endforeach; ?>

                            <div class="pt-2">
                              <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Guardar canvis</button>
                            </div>
                          </form>
                        </div>
                        <?php
                    } else {
                        echo "<p class='text-red-600 mt-4'>No s'ha trobat el registre per a editar.</p>";
                    }
                } else {
                    echo "<p class='text-red-600 mt-4'>Error preparing select per editar: " . h($conn->error) . "</p>";
                }
            }
        } else {
            echo "<p class='text-red-600 mt-4'>Paràmetres d'edició invàlids.</p>";
        }
    }
    ?>

  </div>
</body>
</html>
