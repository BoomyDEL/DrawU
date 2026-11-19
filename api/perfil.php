<?php
/**
 * Editar el perfil del usuario actual.
 *
 * POST accion=nombre, nombre_usuario
 * POST accion=descripcion, descripcion
 * POST accion=foto, foto (archivo)
 * POST accion=tema, tema (claro|oscuro)
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();

switch (entradaTexto('accion')) {
    case 'nombre':
        $nombre = entradaTexto('nombre_usuario');
        if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $nombre)) {
            responderError('El nombre de usuario debe tener de 3 a 50 caracteres (letras, números, "_" o ".").');
        }
        $ocupado = $pdo->prepare('SELECT 1 FROM usuarios WHERE nombre_usuario = ? AND id_usuario <> ?');
        $ocupado->execute([$nombre, $yo]);
        if ($ocupado->fetchColumn()) {
            responderError('Ese nombre de usuario ya está en uso.');
        }
        $pdo->prepare('UPDATE usuarios SET nombre_usuario = ? WHERE id_usuario = ?')->execute([$nombre, $yo]);
        responder(['ok' => true, 'nombre_usuario' => $nombre]);

    case 'descripcion':
        $descripcion = entradaTexto('descripcion', 500);
        $pdo->prepare('UPDATE usuarios SET descripcion_perfil = ? WHERE id_usuario = ?')
            ->execute([$descripcion !== '' ? $descripcion : null, $yo]);
        responder(['ok' => true, 'descripcion' => $descripcion]);

    case 'foto':
        $ruta = guardarArchivoSubido('foto', 'perfiles', TIPOS_IMAGEN, 5 * 1048576);
        if ($ruta === null) {
            responderError('Selecciona una imagen.');
        }
        $pdo->prepare('UPDATE usuarios SET imagen_perfil = ? WHERE id_usuario = ?')->execute([$ruta, $yo]);
        responder(['ok' => true, 'url' => url($ruta)]);

    case 'tema':
        $tema = entradaTexto('tema') === 'oscuro' ? 'oscuro' : 'claro';
        $pdo->prepare('UPDATE usuarios SET tema = ? WHERE id_usuario = ?')->execute([$tema, $yo]);
        responder(['ok' => true, 'tema' => $tema]);
}

responderError('Acción no válida.');
