<?php require_once "includes/header.php"; ?>
<?php
// google-callback.php - Processa la resposta de Google
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/config.php';

session_start();

// --- CONFIGURACIÓ (Hauria d'estar a config, però ho deixem aquí per simplicitat) ---
$clientID = '429847794012-15n1jiqcf1enbtkimige9qtj2558lnkj.apps.googleusercontent.com';
$clientSecret = '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';

// CAMBIO IMPORTANTE AQUÍ TAMBIÉN:
$redirectUri = 'http://192.168.5.80.nip.io/public/google-callback.php'; 

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    try {
        // 1. Canviar el codi (code) per un token d'accés
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            throw new Exception("Error obtenint token: " . $token['error']);
        }
        
        $client->setAccessToken($token['access_token']);

        // 2. Obtenir dades de l'usuari de Google
        $google_oauth = new Google_Service_Oauth2($client);
        $google_info = $google_oauth->userinfo->get();
        
        $google_id = $google_info->id;
        $email = $google_info->email;
        $name = $google_info->name;

        // 3. Buscar si l'usuari ja existeix a la nostra BD (per Google ID)
        $stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->bind_param("s", $google_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if ($user) {
            // CAS A: L'usuari ja existeix -> LOGIN DIRECTE
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
        } else {
            // CAS B: Primera vegada -> REGISTRE AUTOMÀTIC
            // Creem un nom d'usuari basat en el mail (abans de l'@)
            $username = explode('@', $email)[0];
            
            // Assegurar que el username no estigui agafat per un altre usuari
            $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
            if ($check->num_rows > 0) {
                $username .= '_' . rand(1000, 9999); // Afegir numero si està repetit
            }

            // Contrasenya aleatòria (no la farà servir mai, entra per Google)
            $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, password, google_id, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
            $stmt->bind_param("sss", $username, $dummy_pass, $google_id);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';
            } else {
                throw new Exception("Error registrant usuari a la BD: " . $conn->error);
            }
        }

        // Èxit! Redirigir al panell principal
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        die("<div style='color:red; padding:20px; text-align:center;'>
                <h2>Error d'autenticació amb Google</h2>
                <p>" . htmlspecialchars($e->getMessage()) . "</p>
                <a href='login.php'>Tornar</a>
             </div>");
    }
} else {
    // Si intenten entrar directament sense codi de Google
    header("Location: login.php");
    exit;
}
?>
