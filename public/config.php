<?php
// config.php

$host = getenv('DB_HOST') ?: '192.168.5.80';
$port = getenv('DB_PORT') ?: 3306;
$user = getenv('DB_USER') ?: 'bchecker';
$pass = getenv('DB_PASS') ?: 'bchecker121';
$db   = getenv('DB_NAME') ?: 'Educacio';

// Crear la conexión directamente con la base de datos
$mysqli = new mysqli($host, $user, $pass, $db, (int)$port);

// Comprobar conexión
if ($mysqli->connect_error) {
    die("Error de conexión a MySQL ({$host}:{$port}): " . $mysqli->connect_error);
}

// Establecer charset UTF-8
if (!$mysqli->set_charset("utf8mb4")) {
    die("Error estableciendo charset utf8mb4: " . $mysqli->error);
}

$conn = $mysqli;

?>
