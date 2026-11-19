<?php
/**
 * GET  ?id_publicacion=N            → lista de comentarios
 * POST id_publicacion, contenido    → agrega un comentario y devuelve la lista
 * Respuesta: { ok, comentarios: [{id, usuario, id_usuario, contenido, fecha}], total }
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi(false);
$idPublicacion = entradaEntero('id_publicacion');
$pdo = conexion();

$existe = $pdo->prepare('SELECT 1 FROM publicaciones WHERE id_publicacion = ?');
$existe->execute([$idPublicacion]);
if (!$existe->fetchColumn()) {
    responderError('La publicación no existe.', 404);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido = entradaTexto('contenido', 500);
    if ($contenido === '') {
        responderError('Escribe un comentario.');
    }
    $pdo->prepare('INSERT INTO comentarios (id_publicacion, id_usuario, contenido) VALUES (?, ?, ?)')
        ->execute([$idPublicacion, $usuario['id_usuario'], $contenido]);
}

$consulta = $pdo->prepare(
    'SELECT c.id_comentario AS id, c.id_usuario, u.nombre_usuario AS usuario, c.contenido, c.fecha_comentario AS fecha
     FROM comentarios c
     JOIN usuarios u ON u.id_usuario = c.id_usuario
     WHERE c.id_publicacion = ?
     ORDER BY c.fecha_comentario ASC, c.id_comentario ASC'
);
$consulta->execute([$idPublicacion]);
$comentarios = $consulta->fetchAll();

responder(['ok' => true, 'comentarios' => $comentarios, 'total' => count($comentarios)]);
