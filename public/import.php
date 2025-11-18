<?php require_once "includes/header.php"; ?>
<?php
// import.php - Importador blindado contra errores de permisos (CREATE)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/auth.php';

// 1. Seguretat
require_login();
$current_user_id = current_user_id();

// Funció per detectar tipus (INT, FLOAT, DATE, TEXT)
function detect_column_types(array $rows, array $columns): array {
    $types = array_fill_keys($columns, 'TEXT');
    foreach ($columns as $col) {
        $samples = array_column($rows, $col);
        $samples = array_filter($samples, fn($v) => $v !== '');
        $samples = array_slice($samples, 0, 50);
        
        if (empty($samples)) continue;

        $is_numeric = array_reduce($samples, fn($ok, $v) => $ok && is_numeric($v), true);
        if ($is_numeric) {
            $has_decimals = array_reduce($samples, fn($ok, $v) => $ok && strpos($v, '.') !== false, false);
            $types[$col] = $has_decimals ? 'FLOAT' : 'INT';
        } elseif (array_reduce($samples, fn($ok, $v) => $ok && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v), true)) {
            $types[$col] = 'DATE';
        }
    }
    return $types;
}

$message = '';
$msg_type = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'], $_POST['table_name'])) {
    
    $raw_table = $_POST['table_name'];
    $table = preg_replace('/[^a-zA-Z0-9_]/', '_', $raw_table);
    $file = $_FILES['file']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    if (!$table || !$file || !in_array($ext, ['csv', 'json'])) {
        $message = "Nom de taula o format de fitxer invàlid.";
        $msg_type = 'error';
    } else {
        $columns = [];
        $rows = [];

        // 1. LLEGIR DADES
        if ($ext === 'json') {
            $content = file_get_contents($file);
            $json = json_decode($content, true);
            if (is_array($json) && count($json)) {
                $columns = array_keys($json[0]);
                $rows = $json;
            } else {
                $message = "JSON invàlid o buit.";
                $msg_type = 'error';
            }
        } elseif ($ext === 'csv') {
            if (($handle = fopen($file, 'r')) !== false) {
                $line1 = fgets($handle); rewind($handle);
                $separator = (substr_count($line1, ';') > substr_count($line1, ',')) ? ';' : ',';
                $columns = fgetcsv($handle, 0, $separator);
                // Netejar noms de columnes (UTF-8 BOM i espais)
                if ($columns) {
                    $columns = array_map(fn($c) => preg_replace('/[\x00-\x1F\x7F]/u', '', trim($c)), $columns);
                    while (($row = fgetcsv($handle, 0, $separator)) !== false) {
                        if (count($row) === count($columns)) {
                            $rows[] = array_combine($columns, $row);
                        }
                    }
                } else {
                    $message = "CSV buit o il·legible.";
                    $msg_type = 'error';
                }
                fclose($handle);
            }
        }

        // 2. CREAR TAULA I INSERTAR
        if ($columns && $rows) {
            $types = detect_column_types($rows, $columns);
            $defs = [];
            foreach ($columns as $col) {
                $safe_col = $conn->real_escape_string($col);
                $defs[] = "`$safe_col` " . $types[$col];
            }
            
            // Verificar si existeix
            try {
                $check = $conn->query("SHOW TABLES LIKE '$table'");
                if ($check && $check->num_rows > 0) {
                    $message = "La taula '$table' ja existeix. Tria un altre nom.";
                    $msg_type = 'error';
                } else {
                    // INTENT 1: Crear taula AMB columna de propietari
                    $defs_full = $defs;
                    $defs_full[] = "`created_by` INT NULL";
                    $sql_create_full = "CREATE TABLE `$table` (id INT AUTO_INCREMENT PRIMARY KEY, " . implode(',', $defs_full) . ", INDEX(`created_by`))";
                    
                    $created = false;
                    $has_owner_col = false;

                    try {
                        $created = $conn->query($sql_create_full);
                        $has_owner_col = true;
                    } catch (mysqli_sql_exception $e) {
                        // Si falla (per permisos d'index o columnes), provem versió simple
                        // Si l'error és CREATE DENIED, fallarà també aquí i ho capturem a sota
                        $sql_create_simple = "CREATE TABLE `$table` (id INT AUTO_INCREMENT PRIMARY KEY, " . implode(',', $defs) . ")";
                        try {
                            $created = $conn->query($sql_create_simple);
                        } catch (mysqli_sql_exception $e2) {
                             throw new Exception("NO TENS PERMISOS: L'usuari de la base de dades no pot crear taules noves.<br>Detall: " . $e2->getMessage());
                        }
                    }

                    if ($created) {
                        $conn->begin_transaction();
                        try {
                            $inserted = 0;
                            foreach ($rows as $row) {
                                $fields = array_map(fn($c) => "`".$conn->real_escape_string($c)."`", array_keys($row));
                                $values = array_map(fn($v) => $v === '' ? 'NULL' : "'" . $conn->real_escape_string($v) . "'", array_values($row));
                                
                                if ($has_owner_col) {
                                    $fields[] = "`created_by`";
                                    $values[] = $current_user_id;
                                }

                                $sql_insert = "INSERT INTO `$table` (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
                                $conn->query($sql_insert);
                                $inserted++;
                            }
                            $conn->commit();
                            $message = "Èxit! Taula '$table' creada amb $inserted registres.";
                            $msg_type = 'success';
                        } catch (Exception $e) {
                            $conn->rollback();
                            $message = "Error insertant dades: " . $e->getMessage();
                            $msg_type = 'error';
                        }
                    }
                }
            } catch (Exception $e) {
                $message = "Error Crític de Base de Dades: " . $e->getMessage();
                $msg_type = 'error';
            }
        }
    }
}
?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <title>Importar - Gestor BD</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans p-6 flex justify-center min-h-screen">

  <div class="w-full max-w-2xl mt-10">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Importar Nova Taula</h1>
        <a href="index.php" class="text-blue-600 hover:underline">← Tornar</a>
    </div>

    <?php if ($message): ?>
      <div class="p-4 mb-6 rounded border-l-4 shadow-sm <?php echo $msg_type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700'; ?>">
        <div class="flex gap-2">
            <span><?php echo $msg_type === 'success' ? '✅' : '❌'; ?></span>
            <div><?php echo $message; ?></div>
        </div>
      </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
      <p class="text-sm text-gray-500 mb-6">Puja un fitxer CSV o JSON. El sistema crearà automàticament una taula nova a la base de dades amb l'estructura detectada.</p>
      
      <form method="post" enctype="multipart/form-data" class="space-y-6">
        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">Nom de la nova taula</label>
          <input type="text" name="table_name" required placeholder="Ex: alumnes_2024" 
                 class="w-full border border-gray-300 rounded p-3 focus:ring-2 focus:ring-blue-500 outline-none transition">
          <p class="text-xs text-gray-400 mt-1">Només lletres, números i guions baixos.</p>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">Fitxer de dades</label>
          <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 hover:bg-gray-50 transition text-center">
            <input type="file" name="file" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            <div class="pointer-events-none">
                <span class="text-blue-600 font-medium">Selecciona un fitxer</span> o arrossega'l aquí
                <p class="text-xs text-gray-500 mt-1">Formats: .csv, .json</p>
            </div>
          </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition shadow-lg transform active:scale-95">
          Processar i Crear
        </button>
      </form>
    </div>
    
    <div class="mt-8 p-4 bg-blue-50 rounded text-sm text-blue-800">
        <strong>Nota:</strong> Assegura't que la primera fila del CSV conté els noms de les columnes.
    </div>
  </div>
</body>
</html>
