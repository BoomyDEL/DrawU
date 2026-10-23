<?php
/**
 * Componente Header
 * Uso: <?php include __DIR__ . '/ruta/a/views/includes/header/header.php'; ?>
 * Calcula automáticamente la ruta base para que funcione desde la raíz o desde /views.
 */
$headerBase = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'views') ? '../' : '';
?>
<link rel="stylesheet" href="<?= $headerBase ?>views/includes/header/header.css">

<header class="header">
    <a href="<?= $headerBase ?>index.php" class="header-logo">Logo</a>

    <div class="header-actions">
        <form class="header-search" action="#" method="get">
            <input type="text" name="q" aria-label="Buscar">
            <button type="submit" aria-label="Buscar">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.5" y1="15.5" x2="21" y2="21"/></svg>
            </button>
        </form>

        <div class="header-coins" title="Monedas">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2h2.4c3.1 0 5.6-2.5 5.6-5.6C23 5.4 18 2 12 2zM6.5 13a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3-4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3 4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
            <span class="header-coins-count">0</span>
        </div>

        <button class="header-icon" aria-label="Notificaciones">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 16V11a6 6 0 0 0-12 0v5l-2 2h16z"/><path d="M10 21h4"/></svg>
        </button>

        <a href="usuario_propio.php" class="header-icon" aria-label="Perfil">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </a>
    </div>
</header>
