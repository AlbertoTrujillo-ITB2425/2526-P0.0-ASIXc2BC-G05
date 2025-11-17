<?php
// includes/config.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = '192.168.5.80'; // O 'localhost' si està en la mateixa màquina
$user = 'bchecker';
$pass = 'bchecker121';
$db   = 'Educacio';

try {
    $conn = new mysqli($host, $user, $pass, $db, 3306);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Error de connexió: " . $e->getMessage());
}
?>
