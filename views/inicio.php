
<?php

$comunidadesInicio = [
    [
        'nombre' => 'Boom día, Boomy!',
        'descripcion' => 'Comparte tus dibujos, personajes e ideas con otros artistas.',
        'imagen' => '../assets/img/comunidades/boomy_lentes.png',
        'color' => 'boomy'
    ],
    [
        'nombre' => 'Rediseño de personajes',
        'descripcion' => 'Comparte rediseños de tus personajes favoritos.',
        'imagen' => '../assets/img/comunidades/LSS_Lilly.png',
        'color' => 'redisenos'
    ],
    [
        'nombre' => 'Arte y color',
        'descripcion' => 'Descubre nuevas técnicas y estilos artísticos.',
        'imagen' => '../assets/img/comunidades/hermanitos.png',
        'color' => 'arte'
    ],
    [
        'nombre' => 'Noobs del dibujo',
        'descripcion' => 'Aprende y mejora junto a otros artistas.',
        'imagen' => '../assets/img/comunidades/claro_oscuro.png',
        'color' => 'historias'
    ]
];

$publicacionesInicio = [
    [
        'id' => 1,
        'usuario' => 'Fulanito_detal',
        'imagen' => '../assets/img/publis/Luke.png',
        'descripcion' => ''
    ],
    [
        'id' => 2,
        'usuario' => 'Fulanito_detal',
        'imagen' => '../assets/img/publis/Luke.png',
        'descripcion' => ''
    ],
    [
        'id' => 3,
        'usuario' => 'Fulanito_detal',
        'imagen' => '../assets/img/publis/Luke.png',
        'descripcion' => 'Reviviendo los recuerdos de un lindo Beagle.'
    ],
    [
        'id' => 4,
        'usuario' => 'Fulanito_detal',
        'imagen' => '../assets/img/publis/Luke.png',
        'descripcion' => ''
    ]
];

function escaparInicio($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio | DrawU</title>

    
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="drawu-page">

    
    <?php include __DIR__ . '/includes/header/header.php'; ?>

    
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    
    <nav class="drawu-nav-mobile" aria-label="Navegación móvil">
        <a href="inicio.php" aria-current="page">Inicio</a>
        <a href="videos.php">Videos</a>
        <a href="explorar_comunidades.php">Comunidades</a>
        <a href="crear_publi.php">Crear</a>
    </nav>

    <main class="drawu-home">

        <section class="drawu-hero" aria-labelledby="tituloReto">

            <div class="drawu-hero-art" aria-hidden="true"></div>

            <div class="drawu-hero-inner">

                <h1 id="tituloReto">
                    Reto del<br>día
                </h1>

                <a href="reto_dia.php" class="drawu-primary-link">
                    Más información
                </a>

            </div>

        </section>


        <div class="drawu-home-inner">


            <section
                class="drawu-community-section"
                aria-labelledby="tituloComunidades"
            >

                <div class="drawu-heading-row">

                    <h2 id="tituloComunidades">
                        Explora nuevas comunidades
                    </h2>

                    <a
                        href="explorar_comunidades.php"
                        class="drawu-see-all"
                    >
                        Ver todas
                    </a>

                </div>


                <div class="drawu-carousel-wrap">

                    <button
                        class="drawu-carousel-arrow"
                        id="comunidadesAnterior"
                        type="button"
                        aria-label="Comunidades anteriores"
                    >
                        &#10094;
                    </button>


                    <div
                        class="drawu-community-track"
                        id="pistaComunidades"
                        tabindex="0"
                    >

                        <?php foreach ($comunidadesInicio as $comunidad): ?>

                            <article
                                class="drawu-community-card drawu-community-<?=
                                    escaparInicio($comunidad['color'])
                                ?>"
                            >

                                <div class="drawu-community-art">

                                    <img
                                        src="<?= escaparInicio($comunidad['imagen']) ?>"
                                        alt=""
                                        loading="lazy"
                                        data-asset-opcional
                                    >

                                </div>

                                <div class="drawu-community-overlay">

                                    <div>

                                        <h3>
                                            <?= escaparInicio($comunidad['nombre']) ?>
                                        </h3>

                                        <p>
                                            <?= escaparInicio($comunidad['descripcion']) ?>
                                        </p>

                                    </div>

                                    <a href="comunidad_ind.php">
                                        Ver más
                                    </a>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>


                    
                    <button
                        class="drawu-carousel-arrow"
                        id="comunidadesSiguiente"
                        type="button"
                        aria-label="Comunidades siguientes"
                    >
                        &#10095;
                    </button>

                </div>

            </section>


            <section
                class="drawu-feed"
                aria-label="Publicaciones de la comunidad"
            >

                <?php foreach ($publicacionesInicio as $publicacion): ?>

                    <article
                        class="drawu-post"
                        data-publicacion="<?= (int)$publicacion['id'] ?>"
                    >

                        <header class="drawu-post-header">

                            <a
                                href="usuario_externo.php"
                                class="drawu-avatar"
                                aria-label="Ver perfil"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <circle
                                        cx="12" cy="8" r="3.2"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    />

                                    <path
                                        d="M5.5 20c0-3.6 2.6-5.8 6.5-5.8s6.5 2.2 6.5 5.8"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </a>

                            <a
                                href="usuario_externo.php"
                                class="drawu-post-user"
                            >
                                <?= escaparInicio($publicacion['usuario']) ?>
                            </a>

                            <button
                                type="button"
                                class="drawu-follow"
                                data-follow-user="<?= escaparInicio($publicacion['usuario']) ?>"
                                aria-pressed="false"
                            >
                                Seguir
                            </button>

                        </header>


                        <?php if ($publicacion['descripcion'] !== ''): ?>

                            <p class="drawu-post-caption">
                                <?= escaparInicio($publicacion['descripcion']) ?>
                            </p>

                        <?php endif; ?>


                        <div class="drawu-post-image">

                            <img
                                src="<?= escaparInicio($publicacion['imagen']) ?>"
                                alt="Ilustración de un perro Beagle"
                                loading="lazy"
                                data-asset-opcional
                            >

                        </div>


                        <footer class="drawu-post-actions">

                            <button
                                type="button"
                                class="drawu-action drawu-like"
                                aria-label="Dar me gusta"
                                aria-pressed="false"
                            >

                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M20.8 4.8a5.3 5.3 0 0 0-7.5 0L12 6l-1.3-1.2a5.3 5.3 0 0 0-7.5 7.5L12 21l8.8-8.7a5.3 5.3 0 0 0 0-7.5Z"
                                        stroke="currentColor"
                                        stroke-width="1.9"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                                <span class="drawu-like-count">0</span>

                            </button>


                            <button
                                type="button"
                                class="drawu-action drawu-comment-open"
                                aria-label="Abrir comentarios"
                            >

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M20 11.5a8 8 0 0 1-8 8 8.3 8.3 0 0 1-3.5-.8L4 20l1.3-4.5A8 8 0 1 1 20 11.5Z"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                            </button>


                            <button
                                type="button"
                                class="drawu-action drawu-share"
                                aria-label="Compartir publicación"
                            >

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="m22 2-7.5 20-4-8.5L2 9.5 22 2ZM10.5 13.5 22 2"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                            </button>

                        </footer>

                    </article>

                <?php endforeach; ?>

            </section>

        </div>

    </main>



    <div
        class="drawu-comments-backdrop"
        id="drawuComentariosFondo"
        hidden
    >

        <section
            class="drawu-comments-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="drawuTituloComentarios"
        >

            <div class="drawu-comments-titlebar">

                <h2 id="drawuTituloComentarios">
                    Comentarios
                </h2>

                <button
                    type="button"
                    id="drawuCerrarComentarios"
                    aria-label="Cerrar comentarios"
                >
                    &times;
                </button>

            </div>

            <div
                class="drawu-comments-list"
                id="drawuListaComentarios"
                aria-live="polite"
            ></div>

            <form
                class="drawu-comments-form"
                id="drawuFormularioComentario"
            >

                <label
                    class="visually-hidden"
                    for="drawuTextoComentario"
                >
                    Escribe un comentario
                </label>

                <input
                    type="text"
                    id="drawuTextoComentario"
                    maxlength="300"
                    placeholder="Escribe un comentario..."
                    required
                >

                <button type="submit">
                    Enviar
                </button>

            </form>

        </section>

    </div>


    <div
        class="drawu-toast"
        id="drawuAviso"
        role="status"
        aria-live="polite"
    ></div>


    <!-- Footer existente -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- Funciones de la página -->
    <script src="../assets/js/main.js" defer></script>

</body>
</html>
