// Comentario agregado para commit de prueba - 27/10/2025
<?php
// helpers.php - funcions utilitàries per l'aplicació

/**
 * Retorna llistat de taules de la BD
 * @param mysqli $conn
 * @return array
 */
function get_tables($conn) {
    $tables = [];
    $res = $conn->query("SHOW TABLES");
    if ($res) {
        while ($r = $res->fetch_array(MYSQLI_NUM)) {
            $tables[] = $r[0];
        }
        $res->free();
    }
    return $tables;
}

/**
 * Retorna metadades de columnes per una taula (SHOW COLUMNS)
 * @param mysqli $conn
 * @param string $table
 * @return array
 */
function get_columns($conn, $table) {
    $cols = [];
    $safe = $conn->real_escape_string($table);
    $res = $conn->query("SHOW COLUMNS FROM `{$safe}`");
    if ($res) {
        while ($c = $res->fetch_assoc()) {
            $cols[] = $c; // Field, Type, Null, Key, Default, Extra
        }
        $res->free();
    }
    return $cols;
}

/**
 * Troba la/les columnes que són PRIMARY KEY. Retorna array de noms (pot ser compost)
 * @param array $columns (resultat de get_columns)
 * @return array
 */
function get_primary_keys_from_columns($columns) {
    $pks = [];
    foreach ($columns as $c) {
        if (isset($c['Key']) && strtoupper($c['Key']) === 'PRI') {
            $pks[] = $c['Field'];
        }
    }
    return $pks;
}

/**
 * Inferir tipus de params per bind (s|i|d|b). Simplificat: int -> i, else -> s
 * @param string $colType (ex: int(11), varchar(100))
 * @return string
 */
function coltype_to_bindchar($colType) {
    $t = strtolower($colType);
    if (strpos($t, 'int') !== false || strpos($t, 'tinyint') !== false || strpos($t, 'smallint') !== false || strpos($t, 'mediumint') !== false || strpos($t, 'bigint') !== false) {
        return 'i';
    }
    if (strpos($t, 'decimal') !== false || strpos($t, 'float') !== false || strpos($t, 'double') !== false) {
        return 'd';
    }
    return 's';
}

/**
 * Helper per fer bind dinàmic en mysqli (usa referències)
 * @param mysqli_stmt $stmt
 * @param string $types
 * @param array $params
 * @return bool
 */
function bind_params_dynamic($stmt, $types, $params) {
    // mysqli_stmt::bind_param requereix referències
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    array_unshift($refs, $types);
    return call_user_func_array(array($stmt, 'bind_param'), $refs);
}

/**
 * Escape per mostrar text en HTML
 */
function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
