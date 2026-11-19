<?php
/**
 * POST id_usuario → seguir / dejar de seguir a un usuario.
 * Respuesta: { ok, siguiendo, seguidores }
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$idSeguido = entradaEntero('id_usuario');
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();

if ($idSeguido === $yo) {
    responderError('No puedes seguirte a ti mismo.');
}
$existe = $pdo->prepare('SELECT 1 FROM usuarios WHERE id_usuario = ?');
$existe->execute([$idSeguido]);
if (!$existe->fetchColumn()) {
    responderError('El usuario no existe.', 404);
}

$borrar = $pdo->prepare('DELETE FROM seguimientos WHERE id_seguidor = ? AND id_seguido = ?');
$borrar->execute([$yo, $idSeguido]);
$siguiendo = false;
if ($borrar->rowCount() === 0) {
    $bloqueado = $pdo->prepare('SELECT 1 FROM bloqueos WHERE id_bloqueador = ? AND id_bloqueado = ?');
    $bloqueado->execute([$yo, $idSeguido]);
    if ($bloqueado->fetchColumn()) {
        responderError('Desbloquea a este usuario para poder seguirlo.');
    }
    $pdo->prepare('INSERT IGNORE INTO seguimientos (id_seguidor, id_seguido) VALUES (?, ?)')
        ->execute([$yo, $idSeguido]);
    $siguiendo = true;
}

$total = $pdo->prepare('SELECT COUNT(*) FROM seguimientos WHERE id_seguido = ?');
$total->execute([$idSeguido]);

responder(['ok' => true, 'siguiendo' => $siguiendo, 'seguidores' => (int) $total->fetchColumn()]);
