<?php
/**
 * POST id_usuario → bloquear / desbloquear. Al bloquear se deja de seguir.
 * Respuesta: { ok, bloqueado, seguidores }
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];
$idOtro = entradaEntero('id_usuario');
$pdo = conexion();

if ($idOtro === $yo) {
    responderError('No puedes bloquearte a ti mismo.');
}
$existe = $pdo->prepare('SELECT 1 FROM usuarios WHERE id_usuario = ?');
$existe->execute([$idOtro]);
if (!$existe->fetchColumn()) {
    responderError('El usuario no existe.', 404);
}

$borrar = $pdo->prepare('DELETE FROM bloqueos WHERE id_bloqueador = ? AND id_bloqueado = ?');
$borrar->execute([$yo, $idOtro]);
$bloqueado = false;
if ($borrar->rowCount() === 0) {
    $pdo->beginTransaction();
    $pdo->prepare('INSERT INTO bloqueos (id_bloqueador, id_bloqueado) VALUES (?, ?)')->execute([$yo, $idOtro]);
    $pdo->prepare('DELETE FROM seguimientos WHERE id_seguidor = ? AND id_seguido = ?')->execute([$yo, $idOtro]);
    $pdo->commit();
    $bloqueado = true;
}

$total = $pdo->prepare('SELECT COUNT(*) FROM seguimientos WHERE id_seguido = ?');
$total->execute([$idOtro]);

responder(['ok' => true, 'bloqueado' => $bloqueado, 'seguidores' => (int) $total->fetchColumn()]);
