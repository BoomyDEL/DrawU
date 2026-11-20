
<?php

require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();

$modo = entradaTexto('modo') ?: 'normal';

// FUNCTION: comprobar membresía de comunidad
function validarMiembro(PDO $pdo, int $idComunidad, int $yo): void
{
    $consulta = $pdo->prepare(
        'SELECT fn_es_miembro_comunidad(?, ?)'
    );

    $consulta->execute([$idComunidad, $yo]);

    if (!$consulta->fetchColumn()) {
        responderError(
            'Primero únete a la comunidad para publicar en ella.',
            403
        );
    }
}

// Comprobar comunidad, si se indicó una
$idComunidad = entradaEntero('id_comunidad') ?: null;

if ($idComunidad !== null) {
    validarMiembro($pdo, $idComunidad, $yo);
}




if ($modo === 'aviso') {

    $titulo = entradaTexto('titulo', 180);
    $mensaje = entradaTexto('mensaje', 5000);

    if ($idComunidad === null) {
        responderError('Selecciona una comunidad.');
    }

    if ($titulo === '' || $mensaje === '') {
        responderError('Completa el título y el mensaje del aviso.');
    }

    $imagen = guardarArchivoSubido(
        'archivo',
        'publicaciones',
        TIPOS_IMAGEN,
        8 * 1048576
    );

    $guardar = $pdo->prepare(
        "INSERT INTO publicaciones
         (id_usuario, id_comunidad, tipo,
          titulo, descripcion, ruta_imagen)
         VALUES (?, ?, 'aviso', ?, ?, ?)"
    );

    $guardar->execute([
        $yo,
        $idComunidad,
        $titulo,
        $mensaje,
        $imagen
    ]);

    responder([
        'ok' => true,
        'id_publicacion' => (int) $pdo->lastInsertId()
    ]);
}



if ($modo !== 'normal') {
    responderError('Modo de publicación no válido.');
}

$descripcion = entradaTexto('descripcion', 5000);
$idReto = entradaEntero('id_reto') ?: null;

// VIEW: comprobar que el reto sigue vigente
if ($idReto !== null) {

    $consultaReto = $pdo->prepare(
        'SELECT 1
         FROM vista_retos_vigentes
         WHERE id_reto = ?'
    );

    $consultaReto->execute([$idReto]);

    if (!$consultaReto->fetchColumn()) {
        responderError('Este reto ya no está vigente.');
    }
}



$tipo = 'texto';
$rutaImagen = null;
$rutaVideo = null;

if (
    !empty($_FILES['archivo']) &&
    $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE
) {

    $mime = $_FILES['archivo']['error'] === UPLOAD_ERR_OK
        ? (new finfo(FILEINFO_MIME_TYPE))
            ->file($_FILES['archivo']['tmp_name'])
        : '';

    if (isset(TIPOS_VIDEO[$mime])) {

        $rutaVideo = guardarArchivoSubido(
            'archivo',
            'videos',
            TIPOS_VIDEO,
            100 * 1048576
        );

        $tipo = 'video';

    } else {

        $rutaImagen = guardarArchivoSubido(
            'archivo',
            'publicaciones',
            TIPOS_IMAGEN,
            8 * 1048576
        );

        $tipo = 'imagen';
    }
}

if ($tipo === 'texto' && $descripcion === '') {
    responderError(
        'Selecciona un archivo o escribe una descripción.'
    );
}


try {
    $pdo->beginTransaction();

    $guardar = $pdo->prepare(
        'INSERT INTO publicaciones
         (id_usuario, id_comunidad, id_reto,
          tipo, descripcion, ruta_imagen)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $guardar->execute([
        $yo,
        $idComunidad,
        $idReto,
        $tipo,
        $descripcion ?: null,
        $rutaImagen
    ]);

    $idPublicacion = (int) $pdo->lastInsertId();


    // Guardar el video, si existe
    if ($rutaVideo !== null) {

        $guardarVideo = $pdo->prepare(
            'INSERT INTO videos
             (id_publicacion, ruta_video)
             VALUES (?, ?)'
        );

        $guardarVideo->execute([
            $idPublicacion,
            $rutaVideo
        ]);
    }

    // Consultar si ESTA publicación recibió recompensa
    $recompensa = 0;

    if ($idReto !== null) {

        $consultaRecompensa = $pdo->prepare(
            'SELECT drawings_ganados
             FROM recompensas_reto
             WHERE id_usuario = ?
               AND id_reto = ?
               AND id_publicacion = ?'
        );

        $consultaRecompensa->execute([
            $yo,
            $idReto,
            $idPublicacion
        ]);

        $recompensa = (int) (
            $consultaRecompensa->fetchColumn() ?: 0
        );
    }

    $pdo->commit();

} catch (Throwable $ex) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    throw $ex;
}


// ====================================
// RESPUESTA A JAVASCRIPT
// ====================================

responder([
    'ok' => true,
    'id_publicacion' => $idPublicacion,
    'tipo' => $tipo,
    'recompensa' => $recompensa
]);
