<?php
/**
 * POST accion=unirse → alterna membresía.
 * POST accion=crear  → crea comunidad; trg_creador_miembro inserta al creador.
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();
$accion = entradaTexto('accion');

if ($accion === 'unirse') {
    $idComunidad = entradaEntero('id_comunidad');
    if ($idComunidad <= 0) {
        responderError('Comunidad no válida.');
    }
    try {
        $pdo->beginTransaction();
        $consulta = $pdo->prepare('SELECT id_creador FROM comunidades WHERE id_comunidad = ? FOR UPDATE');
        $consulta->execute([$idComunidad]);
        $idCreador = $consulta->fetchColumn();
        if ($idCreador === false) {
            $pdo->rollBack();
            responderError('La comunidad no existe.', 404);
        }
        if ((int) $idCreador === $yo) {
            // El creador nunca debe poder abandonar su comunidad.
            $pdo->prepare(
                'INSERT IGNORE INTO comunidades_usuarios (id_comunidad, id_usuario) VALUES (?, ?)'
            )->execute([$idComunidad, $yo]);
        } else {
            $borrar = $pdo->prepare(
                'DELETE FROM comunidades_usuarios WHERE id_comunidad = ? AND id_usuario = ?'
            );
            $borrar->execute([$idComunidad, $yo]);
            if ($borrar->rowCount() === 0) {
                $pdo->prepare(
                    'INSERT INTO comunidades_usuarios (id_comunidad, id_usuario) VALUES (?, ?)'
                )->execute([$idComunidad, $yo]);
            }
        }
        $consulta = $pdo->prepare(
            'SELECT c.total_miembros, fn_es_miembro_comunidad(?, ?) AS es_miembro
             FROM vista_comunidades_detalle c WHERE c.id_comunidad = ?'
        );
        $consulta->execute([$idComunidad, $yo, $idComunidad]);
        $estado = $consulta->fetch();
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $ex;
    }
    responder([
        'ok' => true,
        'es_miembro' => (bool) $estado['es_miembro'],
        'miembros' => (int) $estado['total_miembros'],
    ]);
}

if ($accion === 'crear') {
    $nombre = entradaTexto('nombre', 150);
    $privacidad = entradaTexto('privacidad');
    $descripcion = entradaTexto('descripcion', 1000);
    if ($nombre === '') {
        responderError('Escribe el nombre de la comunidad.');
    }
    if (!in_array($privacidad, ['publica', 'privada'], true)) {
        responderError('Elige la privacidad de la comunidad.');
    }
    $foto = guardarArchivoSubido('foto', 'comunidades', TIPOS_IMAGEN, 5 * 1048576);
    $banner = guardarArchivoSubido('banner', 'comunidades', TIPOS_IMAGEN, 8 * 1048576);
    $slug = generarSlug($nombre);
    try {
        $pdo->beginTransaction();
        $crear = $pdo->prepare(
            'INSERT INTO comunidades
             (id_creador, slug, titulo, descripcion, privacidad, imagen_perfil, imagen_banner)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $crear->execute([$yo, $slug, $nombre, $descripcion ?: null, $privacidad, $foto, $banner ?? $foto]);
        $idComunidad = (int) $pdo->lastInsertId();
        // trg_creador_miembro inserta automáticamente en comunidades_usuarios.
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $ex;
    }
    responder([
        'ok' => true,
        'id_comunidad' => $idComunidad,
        'slug' => $slug,
        'url' => 'comunidad_ind.php?id=' . rawurlencode($slug),
    ]);
}
responderError('Acción no válida.');
