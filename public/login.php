<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require __DIR__ . '/includes/config.php'; // conexión a la BD

// --- LOGIN NORMAL ---
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE username = '$user' LIMIT 1");
    if ($row = $res->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['username']  = $row['username'];
            $_SESSION['role']      = $row['role'];
            header("Location: index.php");
            exit;
        }
    }
    $error = "Usuari o contrasenya incorrectes.";
}
?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <title>Login - Gestor BD</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-8 rounded-xl shadow-lg w-96 border border-gray-200">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Benvingut/da</h1>
        <p class="text-gray-500 text-sm">Inicia sessió per continuar</p>
    </div>

    <?php if($error): ?>
        <div class="bg-red-50 text-red-600 p-3 mb-4 rounded-lg text-sm border border-red-100 flex items-center gap-2">
            <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- FORMULARI STANDARD -->
    <form method="post" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Usuari</label>
            <input type="text" name="username" required class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contrasenya</label>
            <input type="password" name="password" required class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 font-bold shadow transition transform active:scale-95">
            Iniciar Sessió
        </button>
    </form>

    <div class="mt-6 text-center text-sm">
        <span class="text-gray-500">No tens compte?</span>
        <a href="register.php" class="text-blue-600 hover:underline font-medium">Registra't</a>
    </div>
  </div>
</body>
</html>
