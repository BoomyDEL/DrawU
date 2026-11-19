<?php
/**
 * POST id_usuario, motivo, detalle? → guarda un reporte de usuario.
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$idReportado = entradaEntero('id_usuario');
$motivo = entradaTexto('motivo');
$detalle = entradaTexto('detalle', 500);

if (!in_array($motivo, ['spam', 'acoso', 'inapropiado', 'plagio', 'otro'], true)) {
    responderError('Elige un motivo.');
}
if ($idReportado === (int) $usuario['id_usuario']) {
    responderError('No puedes reportarte a ti mismo.');
}
$existe = conexion()->prepare('SELECT 1 FROM usuarios WHERE id_usuario = ?');
$existe->execute([$idReportado]);
if (!$existe->fetchColumn()) {
    responderError('El usuario no existe.', 404);
}

conexion()->prepare('INSERT INTO reportes (id_reportador, id_reportado, motivo, detalle) VALUES (?, ?, ?, ?)')
    ->execute([$usuario['id_usuario'], $idReportado, $motivo, $detalle !== '' ? $detalle : null]);

responder(['ok' => true]);
