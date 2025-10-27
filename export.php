// Comentario agregado para commit de prueba - 27/10/2025
<?php
// export.php
// Exportar dades a CSV.
// - Si s'indica ?table=nom_taula exporta només aquesta taula amb columnes per columnes.
// - Si no s'indica table exporta TOTES les taules en un únic CSV amb dos camps: table,data (data = fila JSON).
// Requereix config.php i helpers.php (get_tables, get_columns, h, etc.)

require 'config.php';
require 'helpers.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

$tableParam = isset($_GET['table']) ? trim($_GET['table']) : '';
$delimiter = isset($_GET['delim']) ? $_GET['delim'] : ','; // opcional: ?delim=;
$filenameDate = date('Ymd_His');

$tables = get_tables($conn);
if ($tableParam !== '' && !in_array($tableParam, $tables, true)) {
    http_response_code(400);
    echo "Taula invàlida.";
    exit;
}

if ($tableParam !== '') {
    // Export per taula: columnes separades
    $table = $tableParam;
    $safe_table = $conn->real_escape_string($table);
    $columns = get_columns($conn, $table);
    $colNames = array_map(function($c){ return $c['Field']; }, $columns);

    $filename = "export_{$table}_{$filenameDate}.csv";

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache');

    // BOM per Excel
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    if ($out === false) {
        http_response_code(500);
        echo "No es pot obrir la sortida.";
        exit;
    }

    // escriure capçalera
    fputcsv($out, $colNames, $delimiter);

    // usar un resultat per streaming (mysqli_use_result)
    $sql = "SELECT * FROM `{$safe_table}`";
    $res = $conn->query($sql, MYSQLI_USE_RESULT);
    if ($res === false) {
        fclose($out);
        http_response_code(500);
        echo "Error en la consulta: " . $conn->error;
        exit;
    }

    while ($row = $res->fetch_assoc()) {
        // Assegurar que tots els valors són strings o null
        $line = [];
        foreach ($colNames as $cn) {
            $val = $row[$cn] ?? '';
            if (is_array($val) || is_object($val)) {
                $val = json_encode($val, JSON_UNESCAPED_UNICODE);
            } elseif ($val === null) {
                $val = '';
            }
            $line[] = $val;
        }
        fputcsv($out, $line, $delimiter);
        // Força l'escriptura
        flush();
    }
    $res->close();
    fclose($out);
    exit;
} else {
    // Export TOT: una fila per cada registre de cada taula
    // Columnes: table, data (data = JSON amb tots els camps)
    $filename = "export_all_tables_{$filenameDate}.csv";

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache');

    // BOM per Excel
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    if ($out === false) {
        http_response_code(500);
        echo "No es pot obrir la sortida.";
        exit;
    }

    // capçalera: table,data
    fputcsv($out, ['table', 'data'], $delimiter);

    foreach ($tables as $table) {
        $safe_table = $conn->real_escape_string($table);
        // utilitzar USE_RESULT per evitar carregar tot a memòria
        $sql = "SELECT * FROM `{$safe_table}`";
        $res = $conn->query($sql, MYSQLI_USE_RESULT);
        if ($res === false) {
            // escrivim una línia d'error amb data buida per no trencar el CSV
            fputcsv($out, [$table, json_encode(['error' => $conn->error], JSON_UNESCAPED_UNICODE)], $delimiter);
            continue;
        }

        while ($row = $res->fetch_assoc()) {
            // netejar dades prèvies: transformar a tipus exportables
            foreach ($row as $k => $v) {
                if (is_resource($v)) {
                    $row[$k] = '[resource]';
                } elseif (is_object($v) || is_array($v)) {
                    $row[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
                } elseif ($v === null) {
                    $row[$k] = null;
                } else {
                    $row[$k] = (string)$v;
                }
            }
            $json = json_encode($row, JSON_UNESCAPED_UNICODE);
            fputcsv($out, [$table, $json], $delimiter);
            flush();
        }
        $res->close();

        // afegir línia buida per separació visual opcional (comentable)
        // fputcsv($out, ['', '']);
    }

    fclose($out);
    exit;
}
