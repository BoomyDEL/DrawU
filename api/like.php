<?php
/**
 * POST id_publicacion → da o quita "me gusta".
 * Respuesta: { ok, liked, total }
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$idPublicacion = entradaEntero('id_publicacion');
$pdo = conexion();

$existe = $pdo->prepare('SELECT 1 FROM publicaciones WHERE id_publicacion = ?');
$existe->execute([$idPublicacion]);
if (!$existe->fetchColumn()) {
    responderError('La publicación no existe.', 404);
}

$borrar = $pdo->prepare('DELETE FROM likes_publicaciones WHERE id_publicacion = ? AND id_usuario = ?');
$borrar->execute([$idPublicacion, $usuario['id_usuario']]);
$liked = false;
if ($borrar->rowCount() === 0) {
    $pdo->prepare('INSERT IGNORE INTO likes_publicaciones (id_publicacion, id_usuario) VALUES (?, ?)')
        ->execute([$idPublicacion, $usuario['id_usuario']]);
    $liked = true;
}

$total = $pdo->prepare('SELECT COUNT(*) FROM likes_publicaciones WHERE id_publicacion = ?');
$total->execute([$idPublicacion]);

responder(['ok' => true, 'liked' => $liked, 'total' => (int) $total->fetchColumn()]);
