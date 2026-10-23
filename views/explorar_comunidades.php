<?php


$comunidad = [
    'id' => 'boomy',
    'nombre' => 'Boom día, Boomy!',
    'miembros' => '67 mil miembros',
    'actividad' => '10+ publicaciones al día',

    'imagen' => '../assets/img/comunidades/boomy_lentes.png'
];

function escaparComunidad($valor) {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
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

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/explorar_comunidades.css">
</head>

<body class="drawu-explorar-page">

    <!-- Componentes originales del proyecto -->
    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="drawu-explorar">

        <div class="drawu-explorar-contenedor">

            <header class="drawu-explorar-titulo">
                <h1>Comunidades</h1>
            </header>

            <!-- Lista de comunidades -->
            <section
                class="drawu-explorar-grid"
                id="listaComunidades"
                aria-label="Comunidades disponibles"
            >

                <?php for ($i = 1; $i <= 12; $i++): ?>

                    <article
                        class="drawu-explorar-card"
                        data-comunidad="<?= escaparComunidad($comunidad['id']) ?>"
                    >

                        
                        <div class="drawu-explorar-imagen">

                            <picture>

                                <?php if ($i === 1): ?>

                                    
                                    <source
                                        media="(max-width: 650px)"
                                        srcset="../assets/img/comunidades/LSS_Lilly.png"
                                    >

                                <?php endif; ?>

                                <img
                                    src="<?= escaparComunidad($comunidad['imagen']) ?>"
                                    alt="Imagen de la comunidad <?= escaparComunidad($comunidad['nombre']) ?>"
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
                                <?= escaparComunidad($comunidad['nombre']) ?>
                            </h2>

                            <p class="drawu-explorar-detalles">
                                <?= escaparComunidad($comunidad['miembros']) ?>
                                <span aria-hidden="true"> - </span>
                                <?= escaparComunidad($comunidad['actividad']) ?>
                            </p>

                            <!-- Botón para unirse -->
                            <button
                                class="drawu-explorar-unirse"
                                type="button"
                                data-unirse
                                aria-pressed="false"
                            >
                                Unirse a la comunidad
                            </button>

                            <!-- NUEVO: enlace a la página individual -->
                            <a
                                href="comunidad_ind.php?id=<?= urlencode($comunidad['id']) ?>"
                                class="drawu-explorar-ver"
                            >
                                Ver publicaciones
                            </a>

                        </div>


                    </article>

                <?php endfor; ?>

            </section>

            <p
                class="drawu-explorar-vacio"
                id="comunidadesVacias"
                hidden
            >
                No hay comunidades visibles.
                Recarga la página para mostrarlas nuevamente.
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

    <script src="../assets/js/explorar_comunidades.js" defer></script>

</body>
</html>
