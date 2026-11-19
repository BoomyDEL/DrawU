<?php
/**
 * Mascotas / estilos de perfil.
 *
 * POST accion=elegir, id_mascota   → primera mascota (solo mascotas iniciales, gratis)
 * POST accion=obtener, id_mascota  → aplica la mascota; si no la tiene, la compra
 *                                     con Drawings (tabla compras) y la aplica.
 * Respuesta: { ok, activa, drawings, desbloqueadas: [ids], comprada }
 */
require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();
$accion = entradaTexto('accion');
$idMascota = entradaEntero('id_mascota');

$mascota = $pdo->prepare('SELECT id_mascota, nombre, es_inicial FROM mascotas WHERE id_mascota = ?');
$mascota->execute([$idMascota]);
$mascota = $mascota->fetch();
if (!$mascota) {
    responderError('La mascota no existe.', 404);
}

function estadoMascotas(PDO $pdo, int $yo, bool $comprada = false): void
{
    $u = $pdo->prepare('SELECT drawings, id_mascota_activa FROM usuarios WHERE id_usuario = ?');
    $u->execute([$yo]);
    $datos = $u->fetch();
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

$tiene = $pdo->prepare('SELECT 1 FROM usuarios_mascotas WHERE id_usuario = ? AND id_mascota = ?');
$tiene->execute([$yo, $idMascota]);
$laTiene = (bool) $tiene->fetchColumn();

if ($accion === 'elegir') {
    if (!$laTiene) {
        if (!$mascota['es_inicial']) {
            responderError('Esta mascota no se puede elegir al inicio.');
        }
        $pdo->prepare('INSERT INTO usuarios_mascotas (id_usuario, id_mascota) VALUES (?, ?)')->execute([$yo, $idMascota]);
    }
    $pdo->prepare('UPDATE usuarios SET id_mascota_activa = ? WHERE id_usuario = ?')->execute([$idMascota, $yo]);
    estadoMascotas($pdo, $yo);
}

if ($accion === 'obtener') {
    if ($laTiene) {
        $pdo->prepare('UPDATE usuarios SET id_mascota_activa = ? WHERE id_usuario = ?')->execute([$idMascota, $yo]);
        estadoMascotas($pdo, $yo);
    }

    $articulo = $pdo->prepare("SELECT id_articulo, precio_drawings FROM articulos
                               WHERE tipo = 'mascota' AND id_mascota = ? AND activo = TRUE LIMIT 1");
    $articulo->execute([$idMascota]);
    $articulo = $articulo->fetch();
    if (!$articulo) {
        responderError('Este estilo no está a la venta.');
    }
    $precio = (int) $articulo['precio_drawings'];

    $pdo->beginTransaction();
    // Bloquea la fila del usuario para evitar compras simultáneas
    $saldo = $pdo->prepare('SELECT drawings FROM usuarios WHERE id_usuario = ? FOR UPDATE');
    $saldo->execute([$yo]);
    $drawings = (int) $saldo->fetchColumn();
    if ($drawings < $precio) {
        $pdo->rollBack();
        $faltan = $precio - $drawings;
        responderError("No tienes suficientes Draws. Te faltan {$faltan} Draws para obtener {$mascota['nombre']}.");
    }

    $pdo->prepare('UPDATE usuarios SET drawings = drawings - ?, id_mascota_activa = ? WHERE id_usuario = ?')
        ->execute([$precio, $idMascota, $yo]);
    $pdo->prepare('INSERT IGNORE INTO compras (id_usuario, id_articulo, precio_pagado) VALUES (?, ?, ?)')
        ->execute([$yo, $articulo['id_articulo'], $precio]);
    $pdo->prepare('INSERT IGNORE INTO usuarios_mascotas (id_usuario, id_mascota) VALUES (?, ?)')
        ->execute([$yo, $idMascota]);
    $pdo->commit();

    estadoMascotas($pdo, $yo, true);
}

responderError('Acción no válida.');
