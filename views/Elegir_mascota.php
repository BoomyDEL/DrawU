<?php
require_once __DIR__ . '/includes/app.php';

$usuario = requerirLogin(false);
$mascotas = conexion()->query(
    'SELECT id_mascota, nombre, descripcion, ruta_imagen, color_tema
     FROM mascotas
     WHERE es_inicial = TRUE
     ORDER BY id_mascota ASC'
)->fetchAll();

$catalogoJs = [];
foreach ($mascotas as $m) {
    $color = $m['color_tema'] ?? 'morado';
    $catalogoJs[] = [
        'id' => (int) $m['id_mascota'],
        'nombre' => $m['nombre'],
        'estilo' => $m['descripcion'] ?: ('ESTILO ' . strtoupper($color)),
        'tema' => $color === 'aqua' ? 'tema-aqua' : ($color === 'verde' ? 'tema-verde' : ''),
        'globalClass' => claseTemaMascota($color),
        'img' => url($m['ruta_imagen']),
    ];
}
$primera = $catalogoJs[0] ?? [
    'id' => 1,
    'nombre' => 'Dylan',
    'estilo' => 'ESTILO MORADO',
    'tema' => '',
    'globalClass' => '',
    'img' => '../assets/img/personajes/dylan.png',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elige a tu personaje | DrawU</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/elegir_mascota.css">
    <script>
        window.DRAWU = { api: '../api/', mascotasIniciales: <?= json_encode($catalogoJs, JSON_UNESCAPED_UNICODE) ?> };
    </script>
    <script src="../assets/js/api.js?v=<?= filemtime(__DIR__ . '/../assets/js/api.js') ?>"></script>
</head>
<body>

    <main class="page">
        <header class="intro">
            <p class="eyebrow">Antes de iniciar...</p>
            <h1>Elige a tu<br>nuevo mejor amigo</h1>
        </header>

        <div class="wave" aria-hidden="true"></div>
        
        <section class="picker" aria-live="polite">
            <div class="pet-title">
                <div id="petName"><?= e($primera['nombre']) ?></div>
                <div class="outline" id="petNameOutline"><?= e($primera['nombre']) ?></div>
            </div>
            
            <!-- Contenedor con la imagen del personaje centrada -->
            <div class="pet-character-display" id="petCharacterDisplay">
                <img id="petImg" src="<?= e($primera['img']) ?>" alt="Mascota" class="picker-character-img">
            </div>

            <div class="pet-placeholder" id="petDescription"><span><?= e($primera['estilo']) ?></span></div>
            <button class="apply" id="applyButton">Aplicar</button>
        </section>

        <button class="arrow left" id="previous" aria-label="Mascota anterior">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 4 7 12l8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <button class="arrow right" id="next" aria-label="Siguiente mascota">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 4 8 8-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="status" id="status" role="status"></div>
    </main>

    <script src="../assets/js/elegir_mascota.js?v=<?= filemtime(__DIR__ . '/../assets/js/elegir_mascota.js') ?>"></script>
</body>
</html>
