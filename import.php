<?php
// import.php - Importar CSV/JSON con previsualización y mapping
// Interfaz mejorada (Tailwind CSS), flujo en 2 pasos: PREVIEW -> IMPORT
// Requiere: config.php, helpers.php (get_tables, get_columns, h, bind_params_dynamic, coltype_to_bindchar)

require 'config.php';
require 'helpers.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

$tables = get_tables($conn);
$table = $_REQUEST['table'] ?? ($tables[0] ?? null);
if (!$table || !in_array($table, $tables, true)) {
    $table = null;
}

$columns = $table ? get_columns($conn, $table) : [];
$col_names = array_map(fn($c) => $c['Field'], $columns);

$max_rows = 5000; // límite absoluto para import
$preview_limit = 12; // filas para la previsualización
$message = '';
$error = '';
$step = $_POST['step'] ?? 'form';

// Helpers internos
function tmp_store_uploaded(string $tmpPath, string $name): string {
    $dir = sys_get_temp_dir();
    $dest = $dir . DIRECTORY_SEPARATOR . 'import_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($name));
    if (!move_uploaded_file($tmpPath, $dest)) {
        // fallback: try copy
        if (!copy($tmpPath, $dest)) return '';
    }
    return $dest;
}
function cleanup_tmp(string $path) {
    if ($path && strpos(realpath($path), sys_get_temp_dir()) === 0 && file_exists($path)) {
        @unlink($path);
    }
}

// Manejo PREVIEW (subida y parseo)
$preview = null;
$tempfile = $_POST['tempfile'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'preview') {
    if (!$table) {
        $error = "Selecciona una taula vàlida abans d'importar.";
    } elseif (!isset($_FILES['datafile']) || $_FILES['datafile']['error'] !== UPLOAD_ERR_OK) {
        $error = "Error en la càrrega del fitxer.";
    } else {
        $file = $_FILES['datafile'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $delimiter = $_POST['delim'] ?? ',';
        $has_header = isset($_POST['has_header']) && $_POST['has_header'] === '1';
        $tempfile = tmp_store_uploaded($file['tmp_name'], $file['name']);
        if (!$tempfile) {
            $error = "No s'ha pogut moure el fitxer temporal per a la previsualització.";
        } else {
            try {
                if ($ext === 'json') {
                    $content = file_get_contents($tempfile);
                    $arr = json_decode($content, true);
                    if (!is_array($arr)) throw new Exception("JSON invàlid o no és una llista d'objectes.");
                    // normalizar: array de asociativos
                    $preview_rows = array_values(array_filter($arr, 'is_array'));
                    $preview_rows = array_slice($preview_rows, 0, $preview_limit);
                    $headers = [];
                    if (count($preview_rows) > 0) {
                        $headers = array_keys($preview_rows[0]);
                    }
                    $preview = [
                        'type' => 'json',
                        'headers' => $headers,
                        'rows' => $preview_rows,
                        'tempfile' => $tempfile
                    ];
                } else {
                    // CSV: leer con fgetcsv (soporta delimitador elegido)
                    $handle = fopen($tempfile, 'r');
                    if (!$handle) throw new Exception("No s'ha pogut obrir el fitxer CSV.");
                    $rows = [];
                    $headers = [];
                    $rowIdx = 0;
                    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                        if ($rowIdx === 0 && $has_header) {
                            $headers = array_map('trim', $data);
                            $rowIdx++;
                            continue;
                        }
                        if (!$has_header && $rowIdx === 0 && empty($headers)) {
                            // generar headers genéricos col_1..col_n
                            for ($i = 0; $i < count($data); $i++) $headers[] = 'col_' . ($i + 1);
                        }
                        // mapear a asociativo con headers
                        $assoc = [];
                        for ($i = 0; $i < count($data); $i++) {
                            $key = $headers[$i] ?? ('col_' . ($i + 1));
                            $assoc[$key] = $data[$i];
                        }
                        $rows[] = $assoc;
                        $rowIdx++;
                        if (count($rows) >= $preview_limit) break;
                    }
                    fclose($handle);
                    $preview = [
                        'type' => 'csv',
                        'headers' => $headers,
                        'rows' => $rows,
                        'tempfile' => $tempfile,
                        'has_header' => $has_header,
                        'delimiter' => $delimiter
                    ];
                }
            } catch (Exception $ex) {
                $error = $ex->getMessage();
                cleanup_tmp($tempfile);
                $tempfile = '';
            }
        }
    }
}

// Manejo IMPORT (usar el tempfile generado en preview)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'import') {
    $tempfile = $_POST['tempfile'] ?? '';
    $mapping = $_POST['mapping'] ?? []; // mapping[index_or_key] => target_column
    $ext = strtolower(pathinfo($tempfile, PATHINFO_EXTENSION));
    $delimiter = $_POST['delimiter'] ?? ',';
    $has_header = isset($_POST['has_header']) && $_POST['has_header'] === '1';
    $inserted = 0;
    $errors = 0;

    if (!$table || !$tempfile || !file_exists($tempfile) || strpos(realpath($tempfile), sys_get_temp_dir()) !== 0) {
        $error = "Fitxer temporal no vàlid o caducat. Torna a fer la previsualització abans d'importar.";
    } else {
        // Construir lista de columnas a insertar (unique, en orden del mapping)
        $insert_cols = array_values(array_unique(array_values(array_filter($mapping))));
        if (empty($insert_cols)) {
            $error = "No hi ha cap mapping a columnes vàlid. Selecciona a quines columnes de la taula mapeges les columnes del fitxer.";
        } else {
            // preparar statement
            $placeholders = implode(',', array_fill(0, count($insert_cols), '?'));
            $cols_sql = implode(',', array_map(fn($c) => "`{$c}`", $insert_cols));
            $sql = "INSERT INTO `{$conn->real_escape_string($table)}` ({$cols_sql}) VALUES ({$placeholders})";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $error = "Error preparing INSERT: " . $conn->error;
            } else {
                // inferir tipos de las columnas insert_cols
                $types = '';
                $col_meta = [];
                foreach ($insert_cols as $ic) {
                    foreach ($columns as $c) {
                        if ($c['Field'] === $ic) { $col_meta[$ic] = $c; $types .= coltype_to_bindchar($c['Type']); break; }
                    }
                }

                // Abrir y leer fichero, insertar según mapping
                if (substr($tempfile, -5) === '.json' || (isset($_POST['source_type']) && $_POST['source_type'] === 'json')) {
                    $content = file_get_contents($tempfile);
                    $arr = json_decode($content, true);
                    if (!is_array($arr)) {
                        $error = "JSON invàlid al fitxer temporal.";
                    } else {
                        foreach ($arr as $item) {
                            if ($inserted + $errors >= $max_rows) break;
                            if (!is_array($item)) continue;
                            $vals = [];
                            // mapping keys pueden ser nombres (JSON) o índices (CSV)
                            foreach ($insert_cols as $ic) {
                                // buscar en mapping por valor ic: mapping[k] == ic => k es la clave origen
                                $k = null;
                                foreach ($mapping as $orig => $target) {
                                    if ($target === $ic) { $k = $orig; break; }
                                }
                                $v = null;
                                if ($k !== null) {
                                    // buscar clave en item case-insensitive
                                    foreach ($item as $ik => $iv) {
                                        if (strcasecmp($ik, $k) === 0) { $v = $iv; break; }
                                    }
                                }
                                $vals[] = ($v === '') ? null : $v;
                            }
                            bind_params_dynamic($stmt, $types, $vals);
                            if ($stmt->execute()) $inserted++; else $errors++;
                        }
                    }
                } else {
                    // CSV import (stream)
                    $handle = fopen($tempfile, 'r');
                    if (!$handle) {
                        $error = "No s'ha pogut obrir el fitxer temporal per importar.";
                    } else {
                        $rowIdx = 0;
                        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                            if ($inserted + $errors >= $max_rows) break;
                            if ($rowIdx === 0 && $has_header) { $rowIdx++; continue; }
                            // si no hay header, mapping keys serán nombres tipo 'col_1' ... o índices '0','1'
                            $vals = [];
                            foreach ($insert_cols as $ic) {
                                $k = null;
                                foreach ($mapping as $orig => $target) {
                                    if ($target === $ic) { $k = $orig; break; }
                                }
                                $v = null;
                                if ($k !== null) {
                                    // orig puede ser número de columna (string) o nombre de header.
                                    if (is_numeric($k)) {
                                        $idx = (int)$k;
                                        $v = $data[$idx] ?? null;
                                    } else {
                                        // si mapping fue por nombre (cuando header present), buscar índice del header en POST['preview_headers']
                                        if (!empty($_POST['preview_headers'])) {
                                            $headers = json_decode($_POST['preview_headers'], true);
                                            $idx = array_search($k, $headers, true);
                                            if ($idx !== false) $v = $data[$idx] ?? null;
                                        }
                                    }
                                }
                                $vals[] = ($v === '') ? null : $v;
                            }
                            bind_params_dynamic($stmt, $types, $vals);
                            if ($stmt->execute()) $inserted++; else $errors++;
                            $rowIdx++;
                        }
                        fclose($handle);
                    }
                }

                $stmt->close();
                $message = "Import finalitzat. Inserits: {$inserted}. Errors: {$errors}.";
                // cleanup fichero temporal
                cleanup_tmp($tempfile);
                $tempfile = '';
            }
        }
    }
}

?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <title>Importar a <?php echo h($table); ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
  <div class="max-w-6xl mx-auto p-6">
    <a class="text-slate-600 hover:text-slate-900" href="table.php?table=<?php echo urlencode($table); ?>">&larr; Tornar a la taula</a>
    <h1 class="text-2xl font-semibold mt-4 mb-4">Importar fitxer a: <span class="font-mono text-slate-700"><?php echo h($table); ?></span></h1>

    <?php if ($error): ?>
      <div class="mb-4 p-3 rounded border bg-red-50 text-red-800"><?php echo h($error); ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
      <div class="mb-4 p-3 rounded border bg-emerald-50 text-emerald-800"><?php echo h($message); ?></div>
    <?php endif; ?>

    <?php if ($step === 'form' || !$preview): ?>
      <div class="bg-white p-4 rounded shadow mb-6">
        <form method="post" enctype="multipart/form-data" class="space-y-4">
          <input type="hidden" name="step" value="preview">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700">Taula</label>
              <select name="table" class="mt-1 block w-full border rounded p-2" required>
                <?php foreach ($tables as $t): ?>
                  <option value="<?php echo h($t); ?>" <?php echo ($t === $table) ? 'selected' : ''; ?>><?php echo h($t); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Fitxer (CSV o JSON)</label>
              <input type="file" name="datafile" accept=".csv,.txt,.json" class="mt-1 block w-full" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Opcions</label>
              <div class="mt-1 flex gap-2">
                <label class="inline-flex items-center"><input type="checkbox" name="has_header" value="1" checked> <span class="ml-2 text-sm">CSV amb capçalera</span></label>
              </div>
              <div class="mt-2">
                <label class="text-sm">Delimitador CSV</label>
                <input type="text" name="delim" value="," class="mt-1 w-24 border rounded p-1 text-sm">
                <p class="text-xs text-slate-500 mt-1">Per Excel europeu pots usar ;</p>
              </div>
            </div>
          </div>

          <div class="pt-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Previsualitzar</button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($preview): ?>
      <div class="bg-white p-4 rounded shadow mb-6">
        <h2 class="text-lg font-medium mb-2">Previsualització del fitxer (primeres <?php echo count($preview['rows']); ?> files)</h2>

        <div class="mb-4">
          <div class="text-sm text-slate-600">Detalls: tipus <strong><?php echo h($preview['type']); ?></strong>,
            <?php if ($preview['type'] === 'csv'): ?>
              delimitador <strong><?php echo h($preview['delimiter']); ?></strong>, capçalera: <strong><?php echo $preview['has_header'] ? 'sí' : 'no'; ?></strong>
            <?php endif; ?>
          </div>
        </div>

        <div class="overflow-x-auto mb-4">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <?php foreach ($preview['headers'] as $h): ?>
                  <th class="px-3 py-2 text-left text-sm font-medium text-slate-600"><?php echo h($h); ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-100">
              <?php if (empty($preview['rows'])): ?>
                <tr><td class="p-4" colspan="<?php echo max(1, count($preview['headers'])); ?>">No s'han trobat files a previsualitzar.</td></tr>
              <?php else: ?>
                <?php foreach ($preview['rows'] as $r): ?>
                  <tr>
                    <?php foreach ($preview['headers'] as $hk): ?>
                      <td class="px-3 py-2 text-sm"><?php echo h($r[$hk] ?? ''); ?></td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <form method="post" class="space-y-4">
          <input type="hidden" name="step" value="import">
          <input type="hidden" name="tempfile" value="<?php echo h($preview['tempfile']); ?>">
          <input type="hidden" name="source_type" value="<?php echo h($preview['type']); ?>">
          <input type="hidden" name="has_header" value="<?php echo ($preview['type'] === 'csv' && !empty($preview['has_header'])) ? '1' : '0'; ?>">
          <input type="hidden" name="delimiter" value="<?php echo h($preview['delimiter'] ?? ','); ?>">
          <input type="hidden" name="preview_headers" value="<?php echo h(json_encode($preview['headers'], JSON_UNESCAPED_UNICODE)); ?>">

          <div>
            <p class="text-sm text-slate-600 mb-2">Mapeig: selecciona per cada columna del fitxer la columna de la taula on vols inserir-ne el valor. Deixa en blanc per ignorar-la.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <?php foreach ($preview['headers'] as $idx => $h): ?>
                <div class="p-3 border rounded bg-slate-50">
                  <div class="text-sm font-medium mb-2"><?php echo h($h); ?></div>
                  <select name="mapping[<?php echo h(is_numeric($idx) ? $idx : $h); ?>]" class="w-full border rounded p-2">
                    <option value="">-- Ignorar --</option>
                    <?php foreach ($col_names as $cn): ?>
                      <option value="<?php echo h($cn); ?>"><?php echo h($cn); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="pt-3 flex items-center gap-3">
            <button class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Importar (fins a <?php echo $max_rows; ?> files)</button>
            <a href="import.php?table=<?php echo urlencode($table); ?>" class="text-sm text-slate-600 hover:underline">Cancel·lar i tornar</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="bg-white p-4 rounded shadow">
      <h3 class="font-medium mb-2">Columnes de la taula <?php echo h($table); ?></h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-slate-700">
        <?php foreach ($columns as $c): ?>
          <div class="p-2 border rounded bg-white">
            <div class="font-medium"><?php echo h($c['Field']); ?></div>
            <div class="text-xs text-slate-500"><?php echo h($c['Type']); ?> <?php echo h($c['Key']); ?> <?php echo h($c['Extra']); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</body>
</html>
