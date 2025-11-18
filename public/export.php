<?php require_once "includes/header.php"; ?>
<?php
require 'config.php';
$table = $_GET['table'] ?? '';
$format = $_GET['format'] ?? 'csv';

if (!$table) die("Taula no especificada.");

$tbl = $conn->real_escape_string($table);
$res = $conn->query("SELECT * FROM `{$tbl}`");
if (!$res) die("Error consultant la taula.");

$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;

if ($format === 'json') {
    header('Content-Type: application/json');
    header("Content-Disposition: attachment; filename=\"{$table}.json\"");
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"{$table}.csv\"");
    $out = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($out, array_keys($data[0]));
        foreach ($data as $row) fputcsv($out, $row);
    }
    fclose($out);
}
?>
