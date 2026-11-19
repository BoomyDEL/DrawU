<?php
/**
 * POST mensaje → guarda un mensaje de ayuda y soporte técnico.
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$mensaje = entradaTexto('mensaje', 1000);
if ($mensaje === '') {
    responderError('Describe tu problema o duda.');
}

conexion()->prepare('INSERT INTO mensajes_soporte (id_usuario, mensaje) VALUES (?, ?)')
    ->execute([$usuario['id_usuario'], $mensaje]);

responder(['ok' => true]);
