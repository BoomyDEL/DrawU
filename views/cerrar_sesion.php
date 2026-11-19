<?php
/**
 * Cierra la sesión del usuario y regresa al login.
 */
require_once __DIR__ . '/includes/app.php';

cerrarSesionUsuario();
header('Location: pagina_login.php');
exit;
