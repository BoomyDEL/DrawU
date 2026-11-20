<?php
/**
 * Elegir, activar o comprar mascotas. El TRIGGER
 * trg_compra_desbloquea_mascota agrega la mascota al registrar una compra.
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();
$accion = entradaTexto('accion');
$idMascota = entradaEntero('id_mascota');
if (!in_array($accion, ['elegir', 'obtener'], true)) {
    responderError('Acción no válida.');
}

$consulta = $pdo->prepare(
    'SELECT id_mascota, nombre, es_inicial, id_articulo, precio_drawings
     FROM vista_catalogo_mascotas WHERE id_mascota = ?'
);
$consulta->execute([$idMascota]);
$mascota = $consulta->fetch();
if (!$mascota) {
    responderError('La mascota no existe.', 404);
}

function estadoMascotas(PDO $pdo, int $yo, bool $comprada = false): void
{
    $consulta = $pdo->prepare('SELECT drawings, id_mascota_activa FROM usuarios WHERE id_usuario = ?');
    $consulta->execute([$yo]);
    $datos = $consulta->fetch();
    $lista = $pdo->prepare('SELECT id_mascota FROM usuarios_mascotas WHERE id_usuario = ?');
    $lista->execute([$yo]);
    responder([
        'ok' => true,
        'activa' => (int) $datos['id_mascota_activa'],
        'drawings' => (int) $datos['drawings'],
        'desbloqueadas' => array_map('intval', $lista->fetchAll(PDO::FETCH_COLUMN)),
        'comprada' => $comprada,
    ]);
}

try {
    $pdo->beginTransaction();
    $consulta = $pdo->prepare('SELECT drawings, id_mascota_activa FROM usuarios WHERE id_usuario = ? FOR UPDATE');
    $consulta->execute([$yo]);
    $datos = $consulta->fetch();
    if (!$datos) {
        $pdo->rollBack();
        responderError('El usuario no existe.', 404);
    }
    $consulta = $pdo->prepare('SELECT fn_usuario_tiene_mascota(?, ?)');
    $consulta->execute([$yo, $idMascota]);
    $laTiene = (bool) $consulta->fetchColumn();
    $comprada = false;

    if ($accion === 'elegir' && !$laTiene) {
        if (!(bool) $mascota['es_inicial']) {
            $pdo->rollBack();
            responderError('Esta mascota no se puede elegir al inicio.');
        }
        if ($datos['id_mascota_activa'] !== null) {
            $pdo->rollBack();
            responderError('Ya elegiste tu primera mascota.');
        }
        $pdo->prepare('INSERT INTO usuarios_mascotas (id_usuario, id_mascota) VALUES (?, ?)')
            ->execute([$yo, $idMascota]);
    }

    if ($accion === 'obtener' && !$laTiene) {
        if ($mascota['id_articulo'] === null) {
            $pdo->rollBack();
            responderError('Este estilo no está a la venta.');
        }
        $precio = (int) $mascota['precio_drawings'];
        $drawings = (int) $datos['drawings'];
        if ($drawings < $precio) {
            $pdo->rollBack();
            $faltan = $precio - $drawings;
            responderError("No tienes suficientes Draws. Te faltan {$faltan} Draws para obtener {$mascota['nombre']}.");
        }
        $pdo->prepare('UPDATE usuarios SET drawings = drawings - ? WHERE id_usuario = ?')
            ->execute([$precio, $yo]);
        // ¡No INSERT IGNORE! Queremos detectar errores de compra.
        $pdo->prepare('INSERT INTO compras (id_usuario, id_articulo, precio_pagado) VALUES (?, ?, ?)')
            ->execute([$yo, $mascota['id_articulo'], $precio]);
        // trg_compra_desbloquea_mascota se encarga de usuarios_mascotas.
        $comprada = true;
    }

    $pdo->prepare('UPDATE usuarios SET id_mascota_activa = ? WHERE id_usuario = ?')
        ->execute([$idMascota, $yo]);
    $pdo->commit();
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $ex;
}

estadoMascotas($pdo, $yo, $comprada);
