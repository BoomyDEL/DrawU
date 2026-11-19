<?php
/**
 * POST accion=unirse, id_comunidad                        → unirse / salir
 * POST accion=crear, nombre, privacidad, foto?, banner?   → crea la comunidad
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();
$accion = entradaTexto('accion');

if ($accion === 'unirse') {
    $idComunidad = entradaEntero('id_comunidad');
    $comunidad = $pdo->prepare('SELECT id_creador FROM comunidades WHERE id_comunidad = ?');
    $comunidad->execute([$idComunidad]);
    $idCreador = $comunidad->fetchColumn();
    if ($idCreador === false) {
        responderError('La comunidad no existe.', 404);
    }

    $borrar = $pdo->prepare('DELETE FROM comunidades_usuarios WHERE id_comunidad = ? AND id_usuario = ?');
    $esMiembro = false;
    if ((int) $idCreador === $yo) {
        $esMiembro = true; // El creador no puede abandonar su comunidad
        $pdo->prepare('INSERT IGNORE INTO comunidades_usuarios (id_comunidad, id_usuario) VALUES (?, ?)')
            ->execute([$idComunidad, $yo]);
    } else {
        $borrar->execute([$idComunidad, $yo]);
        if ($borrar->rowCount() === 0) {
            $pdo->prepare('INSERT IGNORE INTO comunidades_usuarios (id_comunidad, id_usuario) VALUES (?, ?)')
                ->execute([$idComunidad, $yo]);
            $esMiembro = true;
        }
    }

    $total = $pdo->prepare('SELECT COUNT(*) FROM comunidades_usuarios WHERE id_comunidad = ?');
    $total->execute([$idComunidad]);
    responder(['ok' => true, 'es_miembro' => $esMiembro, 'miembros' => (int) $total->fetchColumn()]);
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

    $pdo->beginTransaction();
    $pdo->prepare('INSERT INTO comunidades (id_creador, slug, titulo, descripcion, privacidad, imagen_perfil, imagen_banner)
                   VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$yo, $slug, $nombre, $descripcion ?: null, $privacidad, $foto, $banner ?? $foto]);
    $idComunidad = (int) $pdo->lastInsertId();
    $pdo->prepare('INSERT INTO comunidades_usuarios (id_comunidad, id_usuario) VALUES (?, ?)')
        ->execute([$idComunidad, $yo]);
    $pdo->commit();

    responder(['ok' => true, 'id_comunidad' => $idComunidad, 'slug' => $slug, 'url' => 'comunidad_ind.php?id=' . rawurlencode($slug)]);
}

responderError('Acción no válida.');
