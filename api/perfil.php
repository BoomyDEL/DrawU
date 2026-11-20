
<?php
/**
 * Editar perfil del usuario actual.
 *
 * POST accion=nombre, nombre_usuario
 * POST accion=descripcion, descripcion
 * POST accion=foto, foto
 * POST accion=tema, tema
 */

require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];

$pdo = conexion();
$accion = entradaTexto('accion');

switch ($accion) {

    case 'nombre':

        $nombre = entradaTexto('nombre_usuario');

        if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $nombre)) {
            responderError(
                'El nombre debe tener de 3 a 50 caracteres.'
            );
        }

        // FUNCTION: comprobar disponibilidad
        $consulta = $pdo->prepare(
            'SELECT fn_nombre_disponible(?, ?)'
        );

        $consulta->execute([$nombre, $yo]);

        if (!(bool) $consulta->fetchColumn()) {
            responderError(
                'Ese nombre de usuario ya está en uso.'
            );
        }

        try {
            $guardar = $pdo->prepare(
                'UPDATE usuarios
                 SET nombre_usuario = ?
                 WHERE id_usuario = ?'
            );

            $guardar->execute([$nombre, $yo]);

        } catch (PDOException $ex) {
            // Por si otro usuario tomó el nombre
            // entre la comprobación y el UPDATE.
            if ($ex->getCode() === '23000') {
                responderError(
                    'Ese nombre de usuario ya está en uso.',
                    409
                );
            }

            throw $ex;
        }

        break;


    case 'descripcion':

        $descripcion = entradaTexto('descripcion', 500);

        $guardar = $pdo->prepare(
            'UPDATE usuarios
             SET descripcion_perfil = ?
             WHERE id_usuario = ?'
        );

        $guardar->execute([
            $descripcion !== '' ? $descripcion : null,
            $yo
        ]);

        break;


    case 'foto':

        $ruta = guardarArchivoSubido(
            'foto',
            'perfiles',
            TIPOS_IMAGEN,
            5 * 1048576
        );

        if ($ruta === null) {
            responderError('Selecciona una imagen.');
        }

        $guardar = $pdo->prepare(
            'UPDATE usuarios
             SET imagen_perfil = ?
             WHERE id_usuario = ?'
        );

        $guardar->execute([$ruta, $yo]);

        break;


    case 'tema':

        $tema = entradaTexto('tema') === 'oscuro'
            ? 'oscuro'
            : 'claro';

        $guardar = $pdo->prepare(
            'UPDATE usuarios
             SET tema = ?
             WHERE id_usuario = ?'
        );

        $guardar->execute([$tema, $yo]);

        break;


    default:
        responderError('Acción no válida.');
}


$consulta = $pdo->prepare(
    'SELECT
        nombre_usuario,
        descripcion_perfil,
        imagen_perfil,
        tema

     FROM vista_usuario_completo

     WHERE id_usuario = ?'
);

$consulta->execute([$yo]);

$perfil = $consulta->fetch();

if (!$perfil) {
    responderError('No se encontró el perfil.', 404);
}


// ===================================
// RESPUESTA A JAVASCRIPT
// ===================================

switch ($accion) {

    case 'nombre':
        responder([
            'ok' => true,
            'nombre_usuario' => $perfil['nombre_usuario']
        ]);

    case 'descripcion':
        responder([
            'ok' => true,
            'descripcion' => $perfil['descripcion_perfil'] ?? ''
        ]);

    case 'foto':
        responder([
            'ok' => true,
            'url' => url($perfil['imagen_perfil'])
        ]);

    case 'tema':
        responder([
            'ok' => true,
            'tema' => $perfil['tema']
        ]);
}
