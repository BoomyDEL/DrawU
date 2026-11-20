<?php
/**
 * API para publicaciones normales y avisos.
 * La primera recompensa por reto la otorga exclusivamente el TRIGGER
 * trg_recompensa_primera_publicacion_reto (20 Drawings).
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();
$modo = entradaTexto('modo') ?: 'normal';
if (!in_array($modo, ['normal', 'aviso'], true)) {
    responderError('Modo de publicación no válido.');
}

function validarMiembro(PDO $pdo, int $idComunidad, int $yo): void
{
    $consulta = $pdo->prepare('SELECT fn_es_miembro_comunidad(?, ?)');
    $consulta->execute([$idComunidad, $yo]);
    if (!(bool) $consulta->fetchColumn()) {
        responderError('Primero únete a la comunidad para publicar en ella.', 403);
    }
}

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
    $imagen = guardarArchivoSubido('archivo', 'publicaciones', TIPOS_IMAGEN, 8 * 1048576);
    $guardar = $pdo->prepare(
        "INSERT INTO publicaciones (id_usuario, id_comunidad, tipo, titulo, descripcion, ruta_imagen)
         VALUES (?, ?, 'aviso', ?, ?, ?)"
    );
    $guardar->execute([$yo, $idComunidad, $titulo, $mensaje, $imagen]);
    responder(['ok' => true, 'id_publicacion' => (int) $pdo->lastInsertId()]);
}

$descripcion = entradaTexto('descripcion', 5000);
$idReto = entradaEntero('id_reto') ?: null;
if ($idReto !== null) {
    $reto = $pdo->prepare('SELECT 1 FROM vista_retos_vigentes WHERE id_reto = ?');
    $reto->execute([$idReto]);
    if (!$reto->fetchColumn()) {
        responderError('Este reto ya no está vigente.');
    }
}

$tipo = 'texto';
$rutaImagen = null;
$rutaVideo = null;
if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $mime = $_FILES['archivo']['error'] === UPLOAD_ERR_OK
        ? (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['archivo']['tmp_name'])
        : '';
    if (isset(TIPOS_VIDEO[$mime])) {
        $rutaVideo = guardarArchivoSubido('archivo', 'videos', TIPOS_VIDEO, 100 * 1048576);
        $tipo = 'video';
    } else {
        $rutaImagen = guardarArchivoSubido('archivo', 'publicaciones', TIPOS_IMAGEN, 8 * 1048576);
        $tipo = 'imagen';
    }
}
if ($tipo === 'texto' && $descripcion === '') {
    responderError('Selecciona un archivo o escribe una descripción.');
}

try {
    $pdo->beginTransaction();
    $guardar = $pdo->prepare(
        'INSERT INTO publicaciones (id_usuario, id_comunidad, id_reto, tipo, descripcion, ruta_imagen)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $guardar->execute([$yo, $idComunidad, $idReto, $tipo, $descripcion ?: null, $rutaImagen]);
    $idPublicacion = (int) $pdo->lastInsertId();

    if ($rutaVideo !== null) {
        $video = $pdo->prepare('INSERT INTO videos (id_publicacion, ruta_video) VALUES (?, ?)');
        $video->execute([$idPublicacion, $rutaVideo]);
    }

    // El trigger acaba de registrar y pagar (o no) esta participación.
    $recompensa = 0;
    if ($idReto !== null) {
        $consulta = $pdo->prepare(
            'SELECT drawings_ganados FROM recompensas_reto
             WHERE id_usuario = ? AND id_reto = ? AND id_publicacion = ?'
        );
        $consulta->execute([$yo, $idReto, $idPublicacion]);
        $recompensa = (int) ($consulta->fetchColumn() ?: 0);
    }
    $pdo->commit();
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $ex;
}

responder([
    'ok' => true,
    'id_publicacion' => $idPublicacion,
    'tipo' => $tipo,
    'recompensa' => $recompensa,
]);
