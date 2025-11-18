<?php require_once "includes/header.php"; ?>
<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Verifica si el usuario es Admin o está en la lista de Super-Usuarios
 */
function is_admin() {
    // 1. Comprobar si la base de datos dice que es 'admin'
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return true;
    }

    // 2. LISTA VIP: Usuarios específicos que SIEMPRE pueden borrar/editar todo
    // Asegúrate de escribirlos exactamente igual que en el login
    $super_users = ['isard', 'pirineus']; 

    if (isset($_SESSION['username']) && in_array($_SESSION['username'], $super_users)) {
        return true;
    }

    return false;
}

// Helper para verificar propiedad
function can_manage_record($record_owner_id) {
    // Admin (y los de la lista VIP) lo pueden todo
    if (is_admin()) return true;
    
    // Si el registro no tiene dueño (NULL), asumimos que es público o del sistema
    // Opcional: Si prefieres que registros antiguos SOLO los toque admin, cambia esto a false.
    if ($record_owner_id === null) return false; 
    
    return (int)$record_owner_id === (int)current_user_id();
}
?>
