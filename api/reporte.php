
<?php


require_once __DIR__ . '/../views/includes/app.php';

// Obtener usuario conectado
$usuario = iniciarApi();

$yo = (int) $usuario['id_usuario'];
$idReportado = entradaEntero('id_usuario');
$motivo = entradaTexto('motivo');
$detalle = entradaTexto('detalle', 500);

$pdo = conexion();

// Validar motivo
if (!in_array(
    $motivo,
    ['spam', 'acoso', 'inapropiado', 'plagio', 'otro'],
    true
)) {
    responderError('Elige un motivo.');
}

// Evitar reportarse a uno mismo
if ($idReportado === $yo) {
    responderError('No puedes reportarte a ti mismo.');
}

// FUNCTION: comprobar que el usuario existe
$consulta = $pdo->prepare(
    'SELECT fn_usuario_existe(?)'
);

$consulta->execute([$idReportado]);

if (!$consulta->fetchColumn()) {
    responderError('El usuario no existe.', 404);
}

// Guardar reporte
$guardar = $pdo->prepare(
    'INSERT INTO reportes
     (id_reportador, id_reportado, motivo, detalle)
     VALUES (?, ?, ?, ?)'
);

$guardar->execute([
    $yo,
    $idReportado,
    $motivo,
    $detalle !== '' ? $detalle : null
]);

// El TRIGGER comprueba automáticamente
// que el usuario no se reporte a sí mismo.

responder(['ok' => true]);

