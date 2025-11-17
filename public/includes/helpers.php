<?php
// includes/helpers.php - Versión Blindada contra errores de permisos

/**
 * Escapa text per a sortida HTML segura (XSS Protection)
 */
function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Retorna llistat de totes les taules de la base de dades
 */
function get_tables(mysqli $conn): array {
    $tables = [];
    try {
        $res = $conn->query("SHOW TABLES");
        if ($res) {
            while ($r = $res->fetch_array(MYSQLI_NUM)) {
                $tables[] = $r[0];
            }
            $res->free();
        }
    } catch (Exception $e) {
        return [];
    }
    return $tables;
}

/**
 * Compta el número de files d'una taula de forma ràpida
 */
function table_count(mysqli $conn, string $table): int {
    try {
        $safe_table = $conn->real_escape_string($table);
        $res = $conn->query("SELECT COUNT(*) as c FROM `{$safe_table}`");
        if ($res) {
            $row = $res->fetch_assoc();
            return (int)$row['c'];
        }
    } catch (Exception $e) {
        return 0;
    }
    return 0;
}

/**
 * Obté metadades de les columnes (Camp, Tipus, Clau, etc.)
 */
function get_columns(mysqli $conn, string $table): array {
    $cols = [];
    $safe = $conn->real_escape_string($table);
    try {
        $res = $conn->query("SHOW COLUMNS FROM `{$safe}`");
        if ($res) {
            while ($c = $res->fetch_assoc()) {
                $cols[] = $c; 
            }
            $res->free();
        }
    } catch (Exception $e) {
        return [];
    }
    return $cols;
}

/**
 * Detecta quines columnes formen la Clau Primària (PK)
 */
function get_primary_keys_from_columns(array $columns): array {
    $pks = [];
    foreach ($columns as $c) {
        if (isset($c['Key']) && strtoupper($c['Key']) === 'PRI') {
            $pks[] = $c['Field'];
        }
    }
    return $pks;
}

/**
 * Tradueix tipus SQL (int, varchar) a caràcters de bind_param
 */
function coltype_to_bindchar(string $colType): string {
    $t = strtolower($colType);
    if (preg_match('/int|year/i', $t)) return 'i';
    if (preg_match('/float|double|decimal/i', $t)) return 'd';
    return 's'; 
}

/**
 * Màgia per fer bind_param dinàmic
 */
function bind_params_dynamic(mysqli_stmt $stmt, string $types, array $params): bool {
    if (empty($params)) return true;
    
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    
    return call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

/**
 * Comprova si una taula té columna 'created_by' i si no, intenta crear-la.
 * ARA AMB PROTECCIÓ TRY-CATCH
 */
function ensure_ownership_column(mysqli $conn, string $table) {
    $safe = $conn->real_escape_string($table);
    
    // Obtenim columnes actuals
    $cols = get_columns($conn, $table);
    $has_col = false;
    foreach($cols as $c) {
        if($c['Field'] === 'created_by') $has_col = true;
    }
    
    if (!$has_col) {
        try {
            // Intentem modificar la taula
            $conn->query("ALTER TABLE `$safe` ADD COLUMN `created_by` INT NULL DEFAULT NULL, ADD INDEX (`created_by`)");
            return true;
        } catch (mysqli_sql_exception $e) {
            // Si falla per permisos (Error 1142) o un altre motiu, ho capturem.
            // Retornem false per indicar que no tenim control de propietat.
            error_log("No s'ha pogut afegir columna created_by a $table: " . $e->getMessage());
            return false;
        }
    }
    return true;
}

/**
 * Construeix clàusula WHERE per cerca global
 */
function build_search_where(mysqli $conn, array $columns, string $search_term): string {
    if (trim($search_term) === '') return '';
    
    $safe_term = $conn->real_escape_string($search_term);
    $parts = [];
    foreach ($columns as $c) {
        $parts[] = "`" . $c['Field'] . "` LIKE '%$safe_term%'";
    }
    
    if (empty($parts)) return '';
    return "(" . implode(' OR ', $parts) . ")";
}
?>
