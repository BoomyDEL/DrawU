
<?php


require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];

$pdo = conexion();
$accion = entradaTexto('accion');


// ===================================
// UNIRSE O SALIR DE UNA COMUNIDAD
// ===================================

if ($accion === 'unirse') {

    $idComunidad = entradaEntero('id_comunidad');

    if ($idComunidad <= 0) {
        responderError('Comunidad no válida.');
    }

    try {
        $pdo->beginTransaction();

        // Buscar la comunidad y su creador
        $comunidad = $pdo->prepare(
            'SELECT id_creador
             FROM comunidades
             WHERE id_comunidad = ?
             FOR UPDATE'
        );

        $comunidad->execute([$idComunidad]);

        $idCreador = $comunidad->fetchColumn();

        if ($idCreador === false) {
            $pdo->rollBack();
            responderError('La comunidad no existe.', 404);
        }

        if ((int) $idCreador === $yo) {

            // El creador siempre debe ser miembro
            $pdo->prepare(
                'INSERT IGNORE INTO comunidades_usuarios
                 (id_comunidad, id_usuario)
                 VALUES (?, ?)'
            )->execute([$idComunidad, $yo]);

        } else {

            // Si ya es miembro, salir
            $borrar = $pdo->prepare(
                'DELETE FROM comunidades_usuarios
                 WHERE id_comunidad = ?
                   AND id_usuario = ?'
            );

            $borrar->execute([$idComunidad, $yo]);

            // Si no era miembro, unirse
            if ($borrar->rowCount() === 0) {

                $pdo->prepare(
                    'INSERT IGNORE INTO comunidades_usuarios
                     (id_comunidad, id_usuario)
                     VALUES (?, ?)'
                )->execute([$idComunidad, $yo]);
            }
        }

        // VIEW + FUNCTION
        $consulta = $pdo->prepare(
            'SELECT
                v.total_miembros,
                fn_es_miembro_comunidad(?, ?) AS es_miembro

             FROM vista_comunidades_detalle v

             WHERE v.id_comunidad = ?'
        );

        $consulta->execute([
            $idComunidad,
            $yo,
            $idComunidad
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
        'es_miembro' => (bool) $resultado['es_miembro'],
        'miembros' => (int) $resultado['total_miembros']
    ]);
}


// ===================================
// CREAR UNA COMUNIDAD
// ===================================

if ($accion === 'crear') {

    $nombre = entradaTexto('nombre', 150);
    $privacidad = entradaTexto('privacidad');
    $descripcion = entradaTexto('descripcion', 1000);

    // Validaciones
    if ($nombre === '') {
        responderError('Escribe el nombre de la comunidad.');
    }

    if (!in_array($privacidad, ['publica', 'privada'], true)) {
        responderError('Elige la privacidad de la comunidad.');
    }

    // Guardar imágenes
    $foto = guardarArchivoSubido(
        'foto',
        'comunidades',
        TIPOS_IMAGEN,
        5 * 1048576
    );

    $banner = guardarArchivoSubido(
        'banner',
        'comunidades',
        TIPOS_IMAGEN,
        8 * 1048576
    );

    // Crear slug
    $slug = generarSlug($nombre);

    try {
        $pdo->beginTransaction();

        // Crear comunidad
        $guardar = $pdo->prepare(
            'INSERT INTO comunidades
             (id_creador, slug, titulo, descripcion,
              privacidad, imagen_perfil, imagen_banner)

             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $guardar->execute([
            $yo,
            $slug,
            $nombre,
            $descripcion ?: null,
            $privacidad,
            $foto,
            $banner ?? $foto
        ]);

        // Obtener el ID de la nueva comunidad
        $idComunidad = (int) $pdo->lastInsertId();

        // El TRIGGER agrega al creador
        // a comunidades_usuarios automáticamente.

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
        'url' => 'comunidad_ind.php?id=' . rawurlencode($slug)
    ]);
}


// Acción no reconocida
responderError('Acción no válida.');
