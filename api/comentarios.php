
<?php
/**
 * GET: consultar comentarios
 * POST: agregar comentario
 */

require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi(false);
$idPublicacion = entradaEntero('id_publicacion');
$pdo = conexion();

$metodo = $_SERVER['REQUEST_METHOD'];

if (!in_array($metodo, ['GET', 'POST'], true)) {
    responderError('Método no permitido.', 405);
}

// FUNCTION: comprobar si existe la publicación
$existe = $pdo->prepare(
    'SELECT fn_publicacion_existe(?)'
);

$existe->execute([$idPublicacion]);

if (!$existe->fetchColumn()) {
    responderError('La publicación no existe.', 404);
}

// Guardar comentario si es POST
if ($metodo === 'POST') {

    $contenido = entradaTexto('contenido', 500);

    if ($contenido === '') {
        responderError('Escribe un comentario.');
    }

    $guardar = $pdo->prepare(
        'INSERT INTO comentarios
         (id_publicacion, id_usuario, contenido)
         VALUES (?, ?, ?)'
    );

    $guardar->execute([
        $idPublicacion,
        $usuario['id_usuario'],
        $contenido
    ]);
}

// VIEW: consultar comentarios
$consulta = $pdo->prepare(
    'SELECT id, id_usuario, usuario, contenido, fecha
     FROM vista_comentarios_detalle
     WHERE id_publicacion = ?
     ORDER BY fecha ASC, id ASC'
);

$consulta->execute([$idPublicacion]);

$comentarios = $consulta->fetchAll();

// Responder a JavaScript
responder([
    'ok' => true,
    'comentarios' => $comentarios,
    'total' => count($comentarios)
]);
