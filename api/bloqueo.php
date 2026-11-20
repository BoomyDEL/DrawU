<?php

require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();

$yo = (int) $usuario['id_usuario'];
$idOtro = entradaEntero('id_usuario');

$pdo = conexion();


if ($idOtro === $yo) {
    responderError('No puedes bloquearte a ti mismo.');
}

try {
    $pdo->beginTransaction();

    // Comprobar que el otro usuario existe
    $existe = $pdo->prepare(
        'SELECT id_usuario
         FROM usuarios
         WHERE id_usuario = ?
         FOR UPDATE'
    );

    $existe->execute([$idOtro]);

    if (!$existe->fetchColumn()) {
        $pdo->rollBack();
        responderError('El usuario no existe.', 404);
    }

    // Intentar desbloquear
    $borrar = $pdo->prepare(
        'DELETE FROM bloqueos
         WHERE id_bloqueador = ?
           AND id_bloqueado = ?'
    );

    $borrar->execute([$yo, $idOtro]);

    // Si no estaba bloqueado, bloquearlo
    if ($borrar->rowCount() === 0) {

        $bloquear = $pdo->prepare(
            'INSERT INTO bloqueos
             (id_bloqueador, id_bloqueado)
             VALUES (?, ?)'
        );

        $bloquear->execute([$yo, $idOtro]);

    }

    $consulta = $pdo->prepare(
        'SELECT
            v.total_seguidores,
            fn_usuario_bloqueado(?, ?) AS bloqueado

         FROM vista_total_seguidores v

         WHERE v.id_usuario = ?'
    );

    $consulta->execute([
        $yo,
        $idOtro,
        $idOtro
    ]);

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
    'bloqueado' => (bool) $resultado['bloqueado'],
    'seguidores' => (int) $resultado['total_seguidores']
]);

