<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$user = 'bchecker';
$pass = 'bchecker121';
$db = 'Educacio';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexió fallida: " . $conn->connect_error);
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function detect_column_types(array $rows, array $columns): array {
    $types = array_fill_keys($columns, 'TEXT');
    foreach ($columns as $col) {
        $samples = array_column($rows, $col);
        $samples = array_filter($samples, fn($v) => $v !== '');
        $samples = array_slice($samples, 0, 10);
        if (empty($samples)) continue;

        if (array_reduce($samples, fn($ok, $v) => $ok && is_numeric($v), true)) {
            $types[$col] = (array_reduce($samples, fn($ok, $v) => $ok && strpos($v, '.') !== false, false)) ? 'FLOAT' : 'INT';
        } elseif (array_reduce($samples, fn($ok, $v) => $ok && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v), true)) {
            $types[$col] = 'DATE';
        }
    }
    return $types;
}

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && isset($_POST['table_name'])) {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '_', $_POST['table_name']);
    $file = $_FILES['file']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    if (!$table || !$file || !in_array($ext, ['csv', 'json'])) {
        $message = "Nom de taula o fitxer invàlid.";
    } else {
        $columns = [];
        $rows = [];

        if ($ext === 'json') {
            $json = json_decode(file_get_contents($file), true);
            if (is_array($json) && count($json)) {
                $columns = array_keys($json[0]);
                $rows = $json;
            } else {
                $message = "Fitxer JSON invàlid.";
            }
        } elseif ($ext === 'csv') {
            $handle = fopen($file, 'r');
            $columns = fgetcsv($handle, 0, ',', '"');
            while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
                if (count($row) !== count($columns)) {
                    $errors[] = "Fila amb nombre incorrecte de columnes: " . implode(',', $row);
                    continue;
                }
                $rows[] = array_combine($columns, $row);
            }
            fclose($handle);
        }

        if ($columns && $rows) {
            $types = detect_column_types($rows, $columns);
            $defs = [];
            foreach ($columns as $col) {
                $safe = $conn->real_escape_string($col);
                $defs[] = "`$safe` " . $types[$col];
            }
            $sql_create = "CREATE TABLE `$table` (" . implode(',', $defs) . ")";
            if ($conn->query($sql_create)) {
                $inserted = 0;
                foreach ($rows as $row) {
                    $fields = array_keys($row);
                    $values = array_map(fn($v) => $v === '' ? 'NULL' : "'" . $conn->real_escape_string($v) . "'", array_values($row));
                    $sql_insert = "INSERT INTO `$table` (`" . implode('`,`', $fields) . "`) VALUES (" . implode(',', $values) . ")";
                    if ($conn->query($sql_insert)) {
                        $inserted++;
                    } else {
                        $errors[] = "Error insertant fila: " . $conn->error;
                    }
                }
                $message = "Taula '$table' creada amb $inserted registres.";
            } else {
                $message = "Error creant la taula: " . $conn->error;
            }
        }
    }
}
?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <title>Importar taula nova</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
  <h1 class="text-2xl font-bold mb-4">Importar fitxer com a nova taula</h1>

  <?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4"><?php echo h($message); ?></div>
  <?php endif; ?>
  <?php if (!empty($errors)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
      <strong>Errors:</strong>
      <ul class="list-disc pl-5">
        <?php foreach ($errors as $e): ?>
          <li><?php echo h($e); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow max-w-xl space-y-4">
    <div>
      <label class="block mb-1 font-medium">Nom de la nova taula:</label>
      <input type="text" name="table_name" required class="w-full border rounded p-2">
    </div>
    <div>
      <label class="block mb-1 font-medium">Fitxer CSV o JSON:</label>
      <input type="file" name="file" required class="w-full">
    </div>
    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Importar</button>
  </form>
</body>
</html>
