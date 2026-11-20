
<?php


require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();

$yo = (int) $usuario['id_usuario'];
$idSeguido = entradaEntero('id_usuario');

$pdo = conexion();

// No puedes seguirte a ti mismo
if ($idSeguido === $yo) {
    responderError('No puedes seguirte a ti mismo.');
}

try {
    $pdo->beginTransaction();

    // Comprobar que el usuario existe
    $existe = $pdo->prepare(
        'SELECT id_usuario
         FROM usuarios
         WHERE id_usuario = ?
         FOR UPDATE'
    );

    $existe->execute([$idSeguido]);

    if (!$existe->fetchColumn()) {
        $pdo->rollBack();
        responderError('El usuario no existe.', 404);
    }

    // Intentar dejar de seguir
    $borrar = $pdo->prepare(
        'DELETE FROM seguimientos
         WHERE id_seguidor = ?
           AND id_seguido = ?'
    );

    $borrar->execute([$yo, $idSeguido]);

    $siguiendo = false;

    // Si no seguías al usuario, empezar a seguirlo
    if ($borrar->rowCount() === 0) {

        // FUNCTION: comprobar bloqueo
        $bloqueado = $pdo->prepare(
            'SELECT fn_usuario_bloqueado(?, ?)'
        );

        $bloqueado->execute([$yo, $idSeguido]);

        if ($bloqueado->fetchColumn()) {
            $pdo->rollBack();

            responderError(
                'Desbloquea a este usuario para poder seguirlo.',
                403
            );
        }

        // Agregar seguimiento
        $seguir = $pdo->prepare(
            'INSERT INTO seguimientos
             (id_seguidor, id_seguido)
             VALUES (?, ?)'
        );

        $seguir->execute([$yo, $idSeguido]);

        // El TRIGGER comprueba que el
        // seguimiento esté permitido.

        $siguiendo = true;
    }

    // VIEW: consultar el total de seguidores
    $total = $pdo->prepare(
        'SELECT total_seguidores
         FROM vista_total_seguidores
         WHERE id_usuario = ?'
    );

    $total->execute([$idSeguido]);

    $seguidores = (int) $total->fetchColumn();

    $pdo->commit();

} catch (Throwable $ex) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Si el TRIGGER rechazó el seguimiento
    if (
        $ex instanceof PDOException &&
        $ex->getCode() === '45000'
    ) {
        responderError(
            'No se permite seguir a este usuario.',
            403
        );
    }

    throw $ex;
}

// Responder a JavaScript
responder([
    'ok' => true,
    'siguiendo' => $siguiendo,
    'seguidores' => $seguidores
]);

