<?php
// index.php - Llistat de taules i enllaç a operacions CRUD / import
require 'config.php';
require 'helpers.php';

$tables = get_tables($conn);

function table_count(mysqli $conn, string $table): ?int {
    $tbl = $conn->real_escape_string($table);
    $res = $conn->query("SELECT COUNT(*) AS c FROM `{$tbl}`");
    return $res ? (int)$res->fetch_assoc()['c'] : null;
}
?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <title>Gestor BD - Educació</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-6">

  <header class="mb-6">
    <h1 class="text-3xl font-bold text-green-600">Gestor de la base de dades <span class="text-gray-900">Educació</span></h1>
  </header>

  <div class="flex flex-wrap items-center justify-between mb-4 gap-2">
    <div class="flex gap-2">
      <a href="index.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Refresca</a>
      <a href="import.php" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Importar CSV/JSON</a>
    </div>
    <div class="text-sm text-gray-600">Taules detectades: <?php echo count($tables); ?></div>
  </div>

  <section class="bg-white shadow rounded p-4 mb-6">
    <h2 class="font-semibold mb-2">Taules disponibles:</h2>
    <?php if (empty($tables)): ?>
      <p class="text-gray-500 text-sm">No s'han trobat taules. Importa un dump SQL o crea-les amb un client MySQL.</p>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($tables as $t): 
          $cnt = table_count($conn, $t); ?>
          <li class="p-3 border rounded hover:shadow flex flex-col md:flex-row md:justify-between md:items-center">
            <div>
              <strong class="text-gray-800"><?php echo h($t); ?></strong>
              <span class="text-sm text-gray-500"> — <?php echo is_null($cnt) ? 'mida desconeguda' : $cnt . ' registres'; ?></span>
            </div>
            <div class="flex gap-2 mt-2 md:mt-0">
              <a href="table.php?table=<?php echo urlencode($t); ?>" class="text-blue-600 hover:underline">Veure / CRUD</a>
              <a href="import.php?table=<?php echo urlencode($t); ?>" class="text-green-600 hover:underline">Importar</a>
              <a href="export.php?table=<?php echo urlencode($t); ?>" class="text-purple-600 hover:underline">Exportar CSV</a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <section class="bg-white shadow rounded p-4">
    <h2 class="font-semibold mb-2">Ús ràpid:</h2>
    <ol class="list-decimal list-inside space-y-1 text-gray-700 text-sm">
      <li>Fes clic a "Veure / CRUD" per a una taula i podràs afegir, editar i eliminar registres (si la taula té clau primària).</li>
      <li>Fes clic a "Importar" per pujar dades. El sistema mapearà columnes amb el mateix nom i inserir dades via prepared statements.</li>
      <li>"Exportar CSV" crearà un fitxer CSV descarregable amb les dades de la taula.</li>
    </ol>
    <p class="text-xs text-gray-500 mt-2">Nota: si algun enllaç (per exemple export.php) no està implementat encara, es pot crear fàcilment.</p>
  </section>

</body>
</html>
