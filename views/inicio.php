<?php
require_once __DIR__ . '/includes/app.php';

$usuario = requerirLogin();
$yo = (int) $usuario['id_usuario'];

$comunidadesInicio = obtenerComunidades(['limite' => 8]);
$publicacionesInicio = obtenerPublicaciones(['tipos' => ['imagen', 'texto', 'aviso']], 25);

$coloresCiclo = ['boomy', 'redisenos', 'arte', 'historias'];
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

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
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

                        <?php foreach ($comunidadesInicio as $idx => $comunidad):
                            $colorCard = $coloresCiclo[$idx % count($coloresCiclo)];
                            $imgCom = url($comunidad['imagen_perfil'] ?: $comunidad['imagen_banner']);
                        ?>

                            <article
                                class="drawu-community-card drawu-community-<?= e($colorCard) ?>"
                            >

                                <div class="drawu-community-art">
                                    <?php if ($imgCom !== ''): ?>
                                        <img
                                            src="<?= e($imgCom) ?>"
                                            alt=""
                                            loading="lazy"
                                            data-asset-opcional
                                        >
                                    <?php endif; ?>
                                </div>

                                <div class="drawu-community-overlay">

                                    <div>

                                        <h3>
                                            <?= e($comunidad['titulo']) ?>
                                        </h3>

                                        <p>
                                            <?= e($comunidad['descripcion'] ?: 'Comunidad artística de DrawU.') ?>
                                        </p>

                                    </div>

                                    <a href="comunidad_ind.php?id=<?= urlencode($comunidad['slug']) ?>">
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

                <?php foreach ($publicacionesInicio as $publicacion):
                    $esMio = ((int) $publicacion['id_usuario'] === $yo);
                    $urlPerfil = $esMio ? 'usuario_propio.php' : ('usuario_externo.php?id=' . (int) $publicacion['id_usuario']);
                    $imgPost = url($publicacion['ruta_imagen']);
                    $liked = !empty($publicacion['liked']);
                    $siguiendo = !empty($publicacion['siguiendo']);
                    $textoPost = trim((string) ($publicacion['titulo'] ? ($publicacion['titulo'] . ': ' . $publicacion['descripcion']) : $publicacion['descripcion']));
                ?>

                    <article
                        class="drawu-post"
                        data-publicacion="<?= (int) $publicacion['id_publicacion'] ?>"
                    >

                        <header class="drawu-post-header">

                            <a
                                href="<?= e($urlPerfil) ?>"
                                class="drawu-avatar"
                                aria-label="Ver perfil"
                            >
                                <?php if (!empty($publicacion['imagen_perfil'])): ?>
                                    <img src="<?= e(url($publicacion['imagen_perfil'])) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                <?php else: ?>
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
                                <?php endif; ?>
                            </a>

                            <a
                                href="<?= e($urlPerfil) ?>"
                                class="drawu-post-user"
                            >
                                <?= e($publicacion['nombre_usuario']) ?>
                            </a>

                            <?php if (!$esMio): ?>
                                <button
                                    type="button"
                                    class="drawu-follow<?= $siguiendo ? ' siguiendo' : '' ?>"
                                    data-user-id="<?= (int) $publicacion['id_usuario'] ?>"
                                    data-follow-user="<?= e($publicacion['nombre_usuario']) ?>"
                                    aria-pressed="<?= $siguiendo ? 'true' : 'false' ?>"
                                >
                                    <?= $siguiendo ? 'Siguiendo' : 'Seguir' ?>
                                </button>
                            <?php endif; ?>

                        </header>

                        <?php if ($textoPost !== ''): ?>
                            <p class="drawu-post-caption">
                                <?= e($textoPost) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($imgPost !== ''): ?>
                            <div class="drawu-post-image">
                                <img
                                    src="<?= e($imgPost) ?>"
                                    alt="Ilustración de <?= e($publicacion['nombre_usuario']) ?>"
                                    loading="lazy"
                                    data-asset-opcional
                                >
                            </div>
                        <?php endif; ?>

                        <footer class="drawu-post-actions">

                            <button
                                type="button"
                                class="drawu-action drawu-like<?= $liked ? ' liked' : '' ?>"
                                aria-label="Dar me gusta"
                                aria-pressed="<?= $liked ? 'true' : 'false' ?>"
                            >

                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M20.8 4.8a5.3 5.3 0 0 0-7.5 0L12 6l-1.3-1.2a5.3 5.3 0 0 0-7.5 7.5L12 21l8.8-8.7a5.3 5.3 0 0 0 0-7.5Z"
                                        stroke="currentColor"
                                        stroke-width="1.9"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                                <span class="drawu-like-count"><?= (int) $publicacion['total_likes'] ?></span>

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
    <script src="../assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>" defer></script>

</body>
</html>
