<?php
/**
 * Componente Footer
 * Uso: <?php include __DIR__ . '/includes/footer.php'; ?>
 */
$footerBase = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'views') ? '' : 'views/';
?>
<link rel="stylesheet" href="<?= $footerBase ?>includes/footer.css">

<footer class="footer">
    <div class="footer-logo">Logo</div>
    <div class="footer-info">
        <p>&copy; <?= date('Y') ?> DrawU. Todos los derechos reservados.</p>
        <p>E-mail: <a href="mailto:ayuda@drawu.com">ayuda@drawu.com</a></p>
    </div>
</footer>
