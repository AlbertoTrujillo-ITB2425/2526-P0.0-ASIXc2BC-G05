<?php require_once "includes/header.php"; ?>
<?php
require 'includes/config.php';
session_start();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check duplicats
    $check = $conn->query("SELECT id FROM users WHERE username='$user'");
    if ($check->num_rows > 0) {
        $msg = "Aquest usuari ja existeix.";
    } else {
        $conn->query("INSERT INTO users (username, password) VALUES ('$user', '$pass')");
        header("Location: login.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="ca">
<head><title>Registre</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-8 rounded shadow-md w-96">
    <h1 class="text-2xl font-bold mb-6 text-center text-green-600">Crear Compte</h1>
    <?php if($msg): ?><div class="text-red-600 mb-4"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
        <input type="text" name="username" placeholder="Nou Usuari" required class="w-full border p-2 rounded">
        <input type="password" name="password" placeholder="Contrasenya" required class="w-full border p-2 rounded">
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Registrar-se</button>
    </form>
    <div class="mt-4 text-center text-sm"><a href="login.php" class="text-blue-500">Ja tens compte? Entra</a></div>
  </div>
</body>
</html>
