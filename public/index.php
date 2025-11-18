<?php require_once "includes/header.php"; ?>
<?php
// index.php - Panell Principal (Dashboard)

// 1. ACTIVAR REPORT D'ERRORS (Per veure problemes immediatament)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. VERIFICACIÓ DE FITXERS NECESSARIS (Depuració automàtica)
$required_files = [
    'includes/config.php',
    'includes/helpers.php',
    'includes/auth.php'
];

foreach ($required_files as $file) {
    $path = __DIR__ . '/' . $file;
    if (!file_exists($path)) {
        die("<div style='color:red; padding:20px; border:1px solid red; margin:20px; font-family:sans-serif;'>
                <strong>Error Crític:</strong> No s'ha trobat el fitxer necessari: <code>" . htmlspecialchars($path) . "</code><br>
                Verifica que la carpeta 'includes' estigui al mateix lloc que 'index.php'.
             </div>");
    }
    require_once $path;
}

// 3. VERIFICAR SI LES FUNCIONS EXISTEIXEN
if (!function_exists('get_tables')) {
    die("Error: El fitxer 'includes/helpers.php' s'ha carregat, però falta la funció 'get_tables'. Revisa el contingut de helpers.php.");
}

// 4. PROTECCIÓ D'ACCÉS (LOGIN)
require_login(); 

// 5. OBTENIR DADES
try {
    // Obtenim llistat de taules
    $tables = get_tables($conn);
    
    // Si no hi ha taules, comprovem nom de la BD per informació
    $db_name = 'Desconeguda';
    if ($res = $conn->query("SELECT DATABASE()")) {
        $db_name = $res->fetch_row()[0];
    }

} catch (Exception $e) {
    die("Error connectant amb la base de dades: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <title>Gestor BD - Panell Principal</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col">

  <nav class="bg-white shadow border-b px-6 py-4 flex justify-between items-center sticky top-0 z-50">
    <div class="flex items-center gap-4">
        <div class="bg-blue-600 text-white p-2 rounded shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800 leading-none">GestorBD</h1>
            <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider"><?php echo htmlspecialchars($db_name); ?></span>
        </div>
    </div>
    
    <div class="flex items-center gap-6">
        <div class="text-right hidden sm:block">
            <p class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuari'); ?></p>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo is_admin() ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                <?php echo is_admin() ? 'Administrador' : 'Usuari'; ?>
            </span>
        </div>
        <a href="logout.php" class="group flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200">
            <span>Sortir</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </a>
    </div>
  </nav>

  <main class="flex-grow p-6 max-w-7xl mx-auto w-full">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Taules Disponibles</h2>
            <p class="text-sm text-gray-500">Selecciona una taula per veure, editar o analitzar dades.</p>
        </div>
        <div class="flex gap-3">
            <a href="index.php" class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition shadow-sm font-medium text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresca
            </a>
            <a href="import.php" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm font-medium text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Importar Nova Taula
            </a>
        </div>
    </div>

    <?php if (empty($tables)): ?>
        <div class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl shadow-sm border border-dashed border-gray-300 text-center">
            <div class="bg-blue-50 p-4 rounded-full mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">No s'han trobat taules</h3>
            <p class="text-gray-500 max-w-md mt-2 mb-6">La base de dades està buida actualment. Comença important el teu primer fitxer de dades.</p>
            <a href="import.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow font-medium transition">
                Començar Importació
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($tables as $t): 
                // Amagar taula 'users' als usuaris normals per seguretat visual
                if ($t === 'users' && !is_admin()) continue;

                // Obtenir comptador de forma segura
                $cnt = function_exists('table_count') ? table_count($conn, $t) : '?'; 
            ?>
            <div class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-blue-300 overflow-hidden flex flex-col relative">
                
                <div class="h-1.5 bg-gradient-to-r from-blue-400 to-blue-600 w-full"></div>

                <div class="p-6 flex-grow relative">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-xl text-gray-800 truncate w-3/4" title="<?php echo htmlspecialchars($t); ?>">
                            <?php echo htmlspecialchars($t); ?>
                        </h3>
                        <span class="bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-md border border-blue-100">
                            <?php echo $cnt; ?>
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Tipus: Taula MyISAM/InnoDB</p>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                    <a href="table.php?table=<?php echo urlencode($t); ?>" class="text-blue-600 font-semibold text-sm hover:text-blue-800 hover:underline flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Veure Dades <span class="text-lg">&rarr;</span>
                    </a>
                    
                    <div class="flex items-center gap-4">
                        <a href="import.php?table=<?php echo urlencode($t); ?>" title="Afegir dades (Importar)" class="text-gray-400 hover:text-green-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </a>
                        <a href="export.php?table=<?php echo urlencode($t); ?>&format=csv" title="Descarregar CSV" class="text-gray-400 hover:text-purple-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m-4-4v12" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

  </main>
  
  <footer class="bg-white border-t p-6 mt-auto">
    <div class="max-w-7xl mx-auto text-center text-gray-400 text-sm">
        &copy; <?php echo date('Y'); ?> Gestor de Bases de Dades Educació.
    </div>
  </footer>

</body>
</html>
