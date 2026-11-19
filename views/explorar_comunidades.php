<?php
require_once __DIR__ . '/includes/app.php';

$usuario = requerirLogin();
$busqueda = trim((string) ($_GET['q'] ?? ''));
$comunidades = obtenerComunidades(['busqueda' => $busqueda !== '' ? $busqueda : null]);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Explorar comunidades | DrawU</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/explorar_comunidades.css?v=<?= filemtime(__DIR__ . '/../assets/css/explorar_comunidades.css') ?>">
</head>

<body class="drawu-explorar-page">

    <!-- Componentes originales del proyecto -->
    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="drawu-explorar">

        <div class="drawu-explorar-contenedor">

            <header class="drawu-explorar-titulo">
                <h1><?= $busqueda !== '' ? ('Comunidades: "' . e($busqueda) . '"') : 'Comunidades' ?></h1>
            </header>

            <!-- Lista de comunidades -->
            <section
                class="drawu-explorar-grid"
                id="listaComunidades"
                aria-label="Comunidades disponibles"
            >

                <?php foreach ($comunidades as $comunidad):
                    $imgCom = url($comunidad['imagen_perfil'] ?: $comunidad['imagen_banner'] ?: 'assets/img/comunidades/boomy_lentes.png');
                    $unido = !empty($comunidad['es_miembro']);
                    $miembrosTxt = plural((int) $comunidad['total_miembros'], 'miembro', 'miembros');
                    $hoy = (int) $comunidad['publicaciones_hoy'];
                    $actividadTxt = $hoy > 0 ? plural($hoy, 'publicación hoy', 'publicaciones hoy') : ($comunidad['privacidad'] === 'privada' ? 'Comunidad privada' : 'Comunidad pública');
                ?>

                    <article
                        class="drawu-explorar-card"
                        data-comunidad="<?= e($comunidad['slug']) ?>"
                        data-comunidad-id="<?= (int) $comunidad['id_comunidad'] ?>"
                    >

                        <div class="drawu-explorar-imagen">

                            <picture>
                                <img
                                    src="<?= e($imgCom) ?>"
                                    alt="Imagen de la comunidad <?= e($comunidad['titulo']) ?>"
                                    loading="lazy"
                                    data-imagen-comunidad
                                >
                            </picture>

                            <button
                                class="drawu-explorar-cerrar"
                                type="button"
                                aria-label="Ocultar esta tarjeta"
                                title="Ocultar tarjeta"
                            >
                                &times;
                            </button>

                        </div>

                        <div class="drawu-explorar-informacion">

                            <h2>
                                <?= e($comunidad['titulo']) ?>
                            </h2>

                            <p class="drawu-explorar-detalles">
                                <span data-miembros-count><?= e($miembrosTxt) ?></span>
                                <span aria-hidden="true"> - </span>
                                <?= e($actividadTxt) ?>
                            </p>

                            <!-- Botón para unirse -->
                            <button
                                class="drawu-explorar-unirse<?= $unido ? ' unido' : '' ?>"
                                type="button"
                                data-unirse
                                aria-pressed="<?= $unido ? 'true' : 'false' ?>"
                            >
                                <?= $unido ? 'Unido a la comunidad' : 'Unirse a la comunidad' ?>
                            </button>

                            <!-- Enlace a la página individual -->
                            <a
                                href="comunidad_ind.php?id=<?= urlencode($comunidad['slug']) ?>"
                                class="drawu-explorar-ver"
                            >
                                Ver publicaciones
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </section>

            <p
                class="drawu-explorar-vacio"
                id="comunidadesVacias"
                <?= count($comunidades) > 0 ? 'hidden' : '' ?>
            >
                No hay comunidades disponibles<?= $busqueda !== '' ? ' para tu búsqueda' : '' ?>.
            </p>

        </div>

    </main>

    <div
        class="drawu-explorar-aviso"
        id="avisoComunidad"
        role="status"
        aria-live="polite"
    ></div>

    <!-- Footer original -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="../assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>" defer></script>
    <script src="../assets/js/explorar_comunidades.js?v=<?= filemtime(__DIR__ . '/../assets/js/explorar_comunidades.js') ?>" defer></script>

</body>
</html>
