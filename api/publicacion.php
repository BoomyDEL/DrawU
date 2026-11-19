<?php
/**
 * Crear publicaciones (multipart/form-data).
 *
 * modo=normal: archivo? (imagen o video), descripcion?, id_comunidad?, id_reto?
 * modo=aviso:  id_comunidad, titulo, mensaje, archivo? (imagen)
 *
 * Participar en un reto vigente por primera vez da RECOMPENSA_RETO Drawings.
 */
require_once __DIR__ . '/../views/includes/app.php';

const RECOMPENSA_RETO = 20;

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();
$modo = entradaTexto('modo') ?: 'normal';

/** Verifica que el usuario sea miembro de la comunidad. */
function validarMiembro(PDO $pdo, int $idComunidad, int $yo): void
{
    $consulta = $pdo->prepare('SELECT 1 FROM comunidades_usuarios WHERE id_comunidad = ? AND id_usuario = ?');
    $consulta->execute([$idComunidad, $yo]);
    if (!$consulta->fetchColumn()) {
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

    $pdo->prepare("INSERT INTO publicaciones (id_usuario, id_comunidad, tipo, titulo, descripcion, ruta_imagen)
                   VALUES (?, ?, 'aviso', ?, ?, ?)")
        ->execute([$yo, $idComunidad, $titulo, $mensaje, $imagen]);

    responder(['ok' => true, 'id_publicacion' => (int) $pdo->lastInsertId()]);
}

// --- Publicación normal ---
$descripcion = entradaTexto('descripcion', 5000);
$idReto = entradaEntero('id_reto') ?: null;

if ($idReto !== null) {
    $reto = $pdo->prepare('SELECT 1 FROM retos_del_dia WHERE id_reto = ? AND CURDATE() BETWEEN fecha_inicio AND fecha_fin');
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

$pdo->beginTransaction();

$recompensa = 0;
if ($idReto !== null) {
    $previa = $pdo->prepare('SELECT 1 FROM publicaciones WHERE id_usuario = ? AND id_reto = ? LIMIT 1');
    $previa->execute([$yo, $idReto]);
    if (!$previa->fetchColumn()) {
        $recompensa = RECOMPENSA_RETO;
    }
}

$pdo->prepare('INSERT INTO publicaciones (id_usuario, id_comunidad, id_reto, tipo, descripcion, ruta_imagen)
               VALUES (?, ?, ?, ?, ?, ?)')
    ->execute([$yo, $idComunidad, $idReto, $tipo, $descripcion ?: null, $rutaImagen]);
$idPublicacion = (int) $pdo->lastInsertId();

if ($rutaVideo !== null) {
    $pdo->prepare('INSERT INTO videos (id_publicacion, ruta_video) VALUES (?, ?)')
        ->execute([$idPublicacion, $rutaVideo]);
}
if ($recompensa > 0) {
    $pdo->prepare('UPDATE usuarios SET drawings = drawings + ? WHERE id_usuario = ?')
        ->execute([$recompensa, $yo]);
}

$pdo->commit();

responder([
    'ok' => true,
    'id_publicacion' => $idPublicacion,
    'tipo' => $tipo,
    'recompensa' => $recompensa,
]);
