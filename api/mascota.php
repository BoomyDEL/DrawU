
<?php
/**
 * POST accion=elegir:
 * Elegir la primera mascota gratis.
 *
 * POST accion=obtener:
 * Activar una mascota o comprarla con Drawings.
 */

require_once __DIR__ . '/../views/includes/app.php';

$usuario = iniciarApi();
$yo = (int) $usuario['id_usuario'];

$pdo = conexion();
$accion = entradaTexto('accion');
$idMascota = entradaEntero('id_mascota');

// Validar acción
if (!in_array($accion, ['elegir', 'obtener'], true)) {
    responderError('Acción no válida.');
}

// VIEW: obtener datos y precio de la mascota
$consulta = $pdo->prepare(
    'SELECT id_mascota, nombre, es_inicial,
            id_articulo, precio_drawings
     FROM vista_catalogo_mascotas
     WHERE id_mascota = ?'
);

$consulta->execute([$idMascota]);
$mascota = $consulta->fetch();

if (!$mascota) {
    responderError('La mascota no existe.', 404);
}


// ===================================
// DEVOLVER EL ESTADO DE LAS MASCOTAS
// ===================================

function estadoMascotas(
    PDO $pdo,
    int $yo,
    bool $comprada = false
): void {

    $u = $pdo->prepare(
        'SELECT drawings, id_mascota_activa
         FROM usuarios
         WHERE id_usuario = ?'
    );

    $u->execute([$yo]);
    $datos = $u->fetch();

    $lista = $pdo->prepare(
        'SELECT id_mascota
         FROM usuarios_mascotas
         WHERE id_usuario = ?'
    );

    $lista->execute([$yo]);

    responder([
        'ok' => true,
        'activa' => (int) $datos['id_mascota_activa'],
        'drawings' => (int) $datos['drawings'],
        'desbloqueadas' => array_map(
            'intval',
            $lista->fetchAll(PDO::FETCH_COLUMN)
        ),
        'comprada' => $comprada
    ]);
}


// ===================================
// ELEGIR O COMPRAR UNA MASCOTA
// ===================================

try {
    $pdo->beginTransaction();

    // Bloquear la fila del usuario durante la operación
    $consultaSaldo = $pdo->prepare(
        'SELECT drawings, id_mascota_activa
         FROM usuarios
         WHERE id_usuario = ?
         FOR UPDATE'
    );

    $consultaSaldo->execute([$yo]);
    $datosUsuario = $consultaSaldo->fetch();

    if (!$datosUsuario) {
        $pdo->rollBack();
        responderError('El usuario no existe.', 404);
    }

    $drawings = (int) $datosUsuario['drawings'];

    // FUNCTION: comprobar si ya tiene la mascota
    $consultaTiene = $pdo->prepare(
        'SELECT fn_usuario_tiene_mascota(?, ?)'
    );

    $consultaTiene->execute([$yo, $idMascota]);
    $laTiene = (bool) $consultaTiene->fetchColumn();

    $comprada = false;


    // ===================================
    // ACCIÓN: ELEGIR MASCOTA INICIAL
    // ===================================

    if ($accion === 'elegir') {

        if (!$laTiene) {

            // Solo mascotas iniciales
            if (!(bool) $mascota['es_inicial']) {
                $pdo->rollBack();

                responderError(
                    'Esta mascota no se puede elegir al inicio.'
                );
            }

            // Evitar elegir varias mascotas iniciales gratis
            if ($datosUsuario['id_mascota_activa'] !== null) {
                $pdo->rollBack();

                responderError(
                    'Ya elegiste tu primera mascota.'
                );
            }

            $guardar = $pdo->prepare(
                'INSERT INTO usuarios_mascotas
                 (id_usuario, id_mascota)
                 VALUES (?, ?)'
            );

            $guardar->execute([$yo, $idMascota]);
        }
    }


    // ===================================
    // ACCIÓN: OBTENER O ACTIVAR
    // ===================================

    if ($accion === 'obtener' && !$laTiene) {

        // Verificar si está a la venta
        if ($mascota['id_articulo'] === null) {
            $pdo->rollBack();

            responderError(
                'Este estilo no está a la venta.'
            );
        }

        $precio = (int) $mascota['precio_drawings'];

        if ($precio < 0) {
            $pdo->rollBack();
            responderError('Precio de mascota no válido.');
        }

        // Comprobar saldo
        if ($drawings < $precio) {

            $faltan = $precio - $drawings;

            $pdo->rollBack();

            responderError(
                "No tienes suficientes Draws. " .
                "Te faltan {$faltan} Draws para obtener " .
                $mascota['nombre'] . "."
            );
        }

        // Descontar Drawings
        $descontar = $pdo->prepare(
            'UPDATE usuarios
             SET drawings = drawings - ?
             WHERE id_usuario = ?'
        );

        $descontar->execute([$precio, $yo]);

        // Registrar la compra
        $guardarCompra = $pdo->prepare(
            'INSERT INTO compras
             (id_usuario, id_articulo, precio_pagado)
             VALUES (?, ?, ?)'
        );

        $guardarCompra->execute([
            $yo,
            $mascota['id_articulo'],
            $precio
        ]);

        // El TRIGGER desbloquea la mascota
        // en usuarios_mascotas automáticamente.

        $comprada = true;
    }


    // ===================================
    // ACTIVAR LA MASCOTA
    // ===================================

    $activar = $pdo->prepare(
        'UPDATE usuarios
         SET id_mascota_activa = ?
         WHERE id_usuario = ?'
    );

    $activar->execute([$idMascota, $yo]);

    // Confirmar todas las operaciones
    $pdo->commit();

} catch (Throwable $ex) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    throw $ex;
}


// Devolver estado actualizado
estadoMascotas($pdo, $yo, $comprada);
