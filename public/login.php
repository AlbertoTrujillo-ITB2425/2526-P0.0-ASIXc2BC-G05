<?php
// login.php - Accés amb Usuari/Contrasenya o Google
require __DIR__ . '/vendor/autoload.php'; // Carrega la llibreria de Google
require __DIR__ . '/includes/config.php';

session_start();

$clientID = '429847794012-15n1jiqcf1enbtkimige9qtj2558lnkj.apps.googleusercontent.com';
$clientSecret = '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';

// CAMBIO IMPORTANTE AQUÍ: Usamos nip.io
$redirectUri = 'http://192.168.5.80.nip.io/public/google-callback.php'; 

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

$google_login_url = $client->createAuthUrl();

// --- LÒGICA LOGIN NORMAL ---
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE username = '$user' LIMIT 1");
    if ($row = $res->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
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
  <script src="https://cdn.tailwindcss.com"></script>
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

    <a href="<?php echo filter_var($google_login_url, FILTER_SANITIZE_URL); ?>" 
       class="flex items-center justify-center gap-3 w-full bg-white text-gray-700 border border-gray-300 py-2.5 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium mb-6">
        <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
        Entrar amb Google
    </a>

    <div class="relative mb-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
        <div class="relative flex justify-center text-xs uppercase"><span class="px-2 bg-white text-gray-400">O utilitza el teu correu</span></div>
    </div>

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
