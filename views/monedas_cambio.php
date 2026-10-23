<?php
/**
 * Vista: monedas_cambio.php
 * Permite canjear estilos de mascota utilizando las monedas (Draws / Drawings).
 */
$currentPage = 'monedas_cambio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drawings y estilo | DrawU</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/monedas_cambio.css">
</head>
<body>

    <!-- Header superior común -->
    <?php include __DIR__ . '/includes/header/header.php'; ?>

    <div class="monedas-layout">
        <!-- Barra de navegación lateral -->
        <?php include __DIR__ . '/includes/navbar.php'; ?>

        <!-- Contenedor principal de la página -->
        <main class="monedas-page" id="monedasPage">
            
            <!-- Encabezado superior con título y contador de Drawings -->
            <section class="monedas-top-header">
                <div class="top-header-left">
                    <h1 class="main-title">Disfruta</h1>
                    <p class="subtitle">de los estilos de<br>perfil que tenemos para ti</p>
                    <a href="#" class="btn-conseguir-drawings" id="btnConseguirDrawings">¿Conseguir más Drawings?</a>
                </div>

                <div class="top-header-right">
                    <div class="drawings-badge">
                        <span class="drawings-circle-icon" aria-hidden="true"></span>
                        <span class="drawings-text">Drawings</span>
                        <span class="drawings-amount" id="drawingsBadgeCount">0</span>
                    </div>
                </div>
            </section>

            <!-- Fondo ondulado tipo wave -->
            <div class="wave-bg" aria-hidden="true"></div>

            <!-- Contenedor de las filas de estilos de mascota -->
            <div class="pet-styles-list">

                <!-- Mascota 1: Dylan (Morado) -->
                <article class="pet-card-row pet-row-morado right-aligned" data-pet-id="1" data-price="0" data-theme="">
                    <div class="pet-card-bar">
                        <!-- Personaje centrado en el espacio de color -->
                        <div class="pet-character-container">
                            <img src="../assets/img/personajes/dylan.png" alt="Dylan" class="character-img character-dylan">
                        </div>

                        <!-- Pincel animado -->
                        <div class="pet-brush-wrapper">
                            <img src="../assets/img/otros/pincel-morada.png" alt="Pincel morado" class="brush-img brush-morado">
                        </div>
                    </div>

                    <!-- Fila con información de costo y botón de acción -->
                    <div class="pet-card-footer">
                        <div class="cost-pill">
                            <span class="cost-coin-icon">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2h2.4c3.1 0 5.6-2.5 5.6-5.6C23 5.4 18 2 12 2zM6.5 13a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3-4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3 4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                            </span>
                            <span class="cost-val">0</span>
                            <span class="cost-label">Draws</span>
                        </div>
                        <button type="button" class="btn-pet-action" data-pet-id="1">
                            Aplicar
                        </button>
                    </div>

                    <!-- Nombre vertical rotado -->
                    <div class="pet-vertical-title right-side">
                        <span class="title-solid">Mascota 1</span>
                        <span class="title-outline">Mascota 1</span>
                    </div>
                </article>

                <!-- Mascota 2: Tommy (Azul / Aqua) -->
                <article class="pet-card-row pet-row-aqua left-aligned" data-pet-id="2" data-price="50" data-theme="tema-aqua">
                    <div class="pet-card-bar">
                        <!-- Personaje centrado en el espacio de color -->
                        <div class="pet-character-container">
                            <img src="../assets/img/personajes/tommy.png" alt="Tommy" class="character-img character-tommy">
                        </div>

                        <!-- Pincel animado -->
                        <div class="pet-brush-wrapper">
                            <img src="../assets/img/otros/pincel-azul.png" alt="Pincel azul" class="brush-img brush-azul">
                        </div>
                    </div>

                    <!-- Fila con costo y botón -->
                    <div class="pet-card-footer">
                        <div class="cost-pill">
                            <span class="cost-coin-icon">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2h2.4c3.1 0 5.6-2.5 5.6-5.6C23 5.4 18 2 12 2zM6.5 13a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3-4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3 4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                            </span>
                            <span class="cost-val">50</span>
                            <span class="cost-label">Draws</span>
                        </div>
                        <button type="button" class="btn-pet-action" data-pet-id="2">
                            Aplicar
                        </button>
                    </div>

                    <!-- Nombre vertical rotado -->
                    <div class="pet-vertical-title left-side">
                        <span class="title-solid">Mascota 2</span>
                        <span class="title-outline">Mascota 2</span>
                    </div>
                </article>

                <!-- Mascota 3: Boomy (Verde) -->
                <article class="pet-card-row pet-row-verde right-aligned" data-pet-id="3" data-price="100" data-theme="tema-verde">
                    <div class="pet-card-bar">
                        <!-- Personaje centrado en el espacio de color -->
                        <div class="pet-character-container">
                            <img src="../assets/img/personajes/boomy.png" alt="Boomy" class="character-img character-boomy">
                        </div>

                        <!-- Pincel animado -->
                        <div class="pet-brush-wrapper">
                            <img src="../assets/img/otros/pincel-verde.png" alt="Pincel verde" class="brush-img brush-verde">
                        </div>
                    </div>

                    <!-- Fila con costo y botón -->
                    <div class="pet-card-footer">
                        <div class="cost-pill">
                            <span class="cost-coin-icon">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2h2.4c3.1 0 5.6-2.5 5.6-5.6C23 5.4 18 2 12 2zM6.5 13a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3-4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3 4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                            </span>
                            <span class="cost-val">100</span>
                            <span class="cost-label">Draws</span>
                        </div>
                        <button type="button" class="btn-pet-action" data-pet-id="3">
                            Aplicar
                        </button>
                    </div>

                    <!-- Nombre vertical rotado -->
                    <div class="pet-vertical-title right-side">
                        <span class="title-solid">Mascota 3</span>
                        <span class="title-outline">Mascota 3</span>
                    </div>
                </article>

            </div>

            <!-- Toast / Notificación feedback -->
            <div class="notification-toast" id="notificationToast" role="alert" aria-live="assertive"></div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/monedas_cambio.js"></script>
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
