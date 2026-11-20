
<?php
require_once __DIR__ . '/../views/includes/app.php';

// Obtener usuario y publicación
$usuario = iniciarApi();
$idUsuario = (int) $usuario['id_usuario'];
$idPublicacion = entradaEntero('id_publicacion');

$pdo = conexion();

try {
    $pdo->beginTransaction();

    // Comprobar que la publicación exista
    // y bloquearla durante esta operación
    $existe = $pdo->prepare(
        'SELECT id_publicacion
         FROM publicaciones
         WHERE id_publicacion = ?
         FOR UPDATE'
    );

    $existe->execute([$idPublicacion]);

    if (!$existe->fetchColumn()) {
        $pdo->rollBack();
        responderError('La publicación no existe.', 404);
    }

    
    $borrar = $pdo->prepare(
        'DELETE FROM likes_publicaciones
         WHERE id_publicacion = ?
         AND id_usuario = ?'
    );

    $borrar->execute([$idPublicacion, $idUsuario]);

    
    if ($borrar->rowCount() === 0) {
        $agregar = $pdo->prepare(
            'INSERT IGNORE INTO likes_publicaciones
             (id_publicacion, id_usuario)
             VALUES (?, ?)'
        );

        $agregar->execute([$idPublicacion, $idUsuario]);
    }

    // Consultar la VIEW y la FUNCTION
    $consulta = $pdo->prepare(
        'SELECT
            v.total_likes,
            fn_usuario_dio_like(
                v.id_publicacion, ?
            ) AS liked
         FROM vista_total_likes v
         WHERE v.id_publicacion = ?'
    );

    $consulta->execute([$idUsuario, $idPublicacion]);

    $resultado = $consulta->fetch();

    $pdo->commit();

} catch (Throwable $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    throw $ex;
}

responder([
    'ok' => true,
    'liked' => (bool) $resultado['liked'],
    'total' => (int) $resultado['total_likes']
]);
