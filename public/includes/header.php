<?php require_once "includes/header.php"; ?>
<?php
// includes/head.php
// Aquest fitxer gestiona la càrrega d'estils de forma intel·ligent.
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🗃️</text></svg>">

<!-- ESTRATÈGIA D'ESTILS: PRIORITAT ONLINE -> FALLBACK LOCAL -->

<!-- 1. Intentem carregar Tailwind CSS des del CDN oficial (Més bonic i ràpid amb internet) -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- 2. Configuració de Tailwind (Opcional, per personalitzar colors) -->
<script>
  if (typeof tailwind !== 'undefined') {
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: '#2563eb',
          }
        }
      }
    }
  }
</script>

<!-- 3. Script de Rescat: Si Tailwind falla, carreguem l'estil local -->
<script>
    // Si l'objecte 'tailwind' no existeix, el CDN ha fallat
    if (typeof tailwind === 'undefined') {
        console.warn("⚠️ El servidor de Tailwind no respon (Mode Offline). Carregant 'assets/style.css' local...");
        
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'assets/style.css'; 
        document.head.appendChild(link);
        
        // Afegim una classe al body per saber que estem en mode offline (opcional per debug)
        document.documentElement.classList.add('offline-mode');
    }
</script>

<!-- 4. Fallback per a navegadors antics sense JavaScript -->
<noscript>
    <link rel="stylesheet" href="assets/style.css">
</noscript>

<style>
    /* Petits ajustos globals */
    body { font-family: system-ui, -apple-system, sans-serif; }
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity:0; transform: translateY(-5px); } to { opacity:1; transform: translateY(0); } }
</style>
