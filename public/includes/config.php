<?php
// config.php

// Parámetros de conexión
$host = getenv('DB_HOST') ?: '192.168.5.80';
$port = getenv('DB_PORT') ?: 3306;
$user = getenv('DB_USER') ?: 'bchecker';
$pass = getenv('DB_PASS') ?: 'bchecker121';
$db   = getenv('DB_NAME') ?: 'Educacio';

// Crear conexión
$mysqli = new mysqli($host, $user, $pass, $db, (int)$port);

// Verificar conexión
if ($mysqli->connect_errno) {
    error_log("MySQL connection failed: " . $mysqli->connect_error);
    die("No es possible connectar amb la base de dades.");
}

// Establecer charset
if (!$mysqli->set_charset("utf8mb4")) {
    error_log("Error configurant charset: " . $mysqli->error);
    die("No s'ha pogut establir el charset UTF-8.");
}

// Exportar la conexión
$conn = $mysqli;
?>
