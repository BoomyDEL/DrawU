<?php
require_once __DIR__ . '/includes/app.php';

$usuario = requerirLogin();
$yo = (int) $usuario['id_usuario'];

$retoDb = obtenerRetoActual();
$idRetoActual = $retoDb ? (int) $retoDb['id_reto'] : 0;

$retoDelDia = [
    'id' => $idRetoActual ?: 'reto-kirby-paisaje',
    'titulo' => $retoDb['titulo'] ?? 'Reto: Kirby paisaje',
    'descripcion' => $retoDb['descripcion'] ?? 'Consigue más Drawings haciendo retos de dibujo diarios.',
    'imagen_reto' => url($retoDb['ruta_imagen'] ?? ''),
    'imagen_personaje' => url($usuario['mascota_imagen'] ?? ''),
];

// Destacados de la semana: publicaciones asociadas al reto ordenadas por likes
$destacados = $idRetoActual > 0
    ? obtenerPublicaciones(['id_reto' => $idRetoActual, 'orden' => 'likes'], 6)
    : [];
if (empty($destacados)) {
    $destacados = obtenerPublicaciones(['solo_con_imagen' => true, 'orden' => 'likes'], 3);
}

$postPrincipal = $destacados[0] ?? null;
$idPostPrincipal = $postPrincipal ? (int) $postPrincipal['id_publicacion'] : 0;
$likesPrincipal = $postPrincipal ? (int) $postPrincipal['total_likes'] : 0;
$comentariosPrincipal = $postPrincipal ? (int) $postPrincipal['total_comentarios'] : 0;
$likedPrincipal = !empty($postPrincipal['liked']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto del día | DrawU</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/reto_dia.css?v=<?= filemtime(__DIR__ . '/../assets/css/reto_dia.css') ?>">
</head>

<body class="reto-page">

    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main
        class="reto-main"
        id="paginaRetoDia"
        data-reto-id="<?= e($retoDelDia['id']) ?>"
    >
        <svg
            class="reto-icons-sprite"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <!-- Usuario -->
            <symbol id="reto-icon-user" viewBox="0 0 24 24">
                <circle
                    cx="12"
                    cy="8"
                    r="3.3"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                />
                <path
                    d="M5 20c0-4 2.8-6 7-6s7 2 7 6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                />
            </symbol>

            <!-- Corazón -->
            <symbol id="reto-icon-heart" viewBox="0 0 24 24">
                <path
                    d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.9"
                    stroke-linejoin="round"
                />
            </symbol>

            <!-- Comentarios -->
            <symbol id="reto-icon-comment" viewBox="0 0 24 24">
                <path
                    d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                />
            </symbol>

            <!-- Compartir -->
            <symbol id="reto-icon-share" viewBox="0 0 24 24">
                <path
                    d="m22 2-7 20-4-9-9-4Z"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                />
                <path
                    d="M22 2 11 13"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                />
            </symbol>
        </svg>

        <section class="reto-hero">

            <div class="reto-hero-top">

                <div class="reto-hero-copy">
                    <h1 style="color: white">Reto del día</h1>
                    <p>
                        Consigue más <em>Drawings</em>
                        haciendo retos de dibujo diarios
                    </p>
                </div>

                <div class="reto-hero-character">

                    <?php if ($retoDelDia['imagen_personaje'] !== ''): ?>
                        <img
                            src="<?= e($retoDelDia['imagen_personaje']) ?>"
                            alt="Personaje elegido del reto"
                            loading="lazy"
                            data-asset-opcional
                        >
                    <?php else: ?>
                        <span>Imagen del personaje elegido</span>
                    <?php endif; ?>

                </div>

            </div>

            <div class="reto-hero-wave" aria-hidden="true"></div>

        </section>

        <section class="reto-card-wrap">

            <article class="reto-card">

                <h2 style="color:white">
                    <?= e($retoDelDia['titulo']) ?>
                </h2>

                <div class="reto-card-media">

                    <?php if ($retoDelDia['imagen_reto'] !== ''): ?>
                        <img
                            src="<?= e($retoDelDia['imagen_reto']) ?>"
                            alt="<?= e($retoDelDia['titulo']) ?>"
                            loading="lazy"
                            data-asset-opcional
                        >
                    <?php else: ?>
                        <div class="reto-card-placeholder">
                            <?= e($retoDelDia['descripcion']) ?>
                        </div>
                    <?php endif; ?>

                </div>

                <footer class="reto-card-actions">

                    <?php if ($idPostPrincipal > 0): ?>
                        <button
                            type="button"
                            class="reto-action reto-like<?= $likedPrincipal ? ' liked' : '' ?>"
                            data-action="like"
                            data-post-id="<?= $idPostPrincipal ?>"
                            aria-label="Me gusta"
                            aria-pressed="<?= $likedPrincipal ? 'true' : 'false' ?>"
                        >
                            <svg class="reto-icon" aria-hidden="true">
                                <use href="#reto-icon-heart"></use>
                            </svg>
                            <span><?= $likesPrincipal ?></span>
                        </button>

                        <button
                            type="button"
                            class="reto-action"
                            data-action="comment"
                            data-post-id="<?= $idPostPrincipal ?>"
                            aria-label="Abrir comentarios"
                        >
                            <svg class="reto-icon" aria-hidden="true">
                                <use href="#reto-icon-comment"></use>
                            </svg>
                            <span><?= $comentariosPrincipal ?></span>
                        </button>
                    <?php endif; ?>

                    <button
                        type="button"
                        class="reto-action"
                        data-action="share"
                        data-post-id="<?= $idPostPrincipal ?: 'reto' ?>"
                        aria-label="Compartir reto"
                    >
                        <svg class="reto-icon" aria-hidden="true">
                            <use href="#reto-icon-share"></use>
                        </svg>
                    </button>

                    <a
                        href="crear_publi.php?reto=1"
                        class="reto-action"
                        style="margin-left:auto;text-decoration:none;font-weight:600;padding:4px 12px;"
                    >
                        Participar (+20 Draws)
                    </a>

                </footer>

            </article>

        </section>

        <section class="reto-featured">

            <div class="reto-featured-head">
                <h2 style="color:white">Destacados<br>de la semana</h2>
                <p>
                    Dibujos con más likes publicados en los retos diarios
                </p>
            </div>

            <div class="reto-featured-list">

                <?php foreach ($destacados as $indice => $post):
                    $idPost = (int) $post['id_publicacion'];
                    $esMio = ((int) $post['id_usuario'] === $yo);
                    $urlPerfil = $esMio ? 'usuario_propio.php' : ('usuario_externo.php?id=' . (int) $post['id_usuario']);
                    $imgPost = url($post['ruta_imagen']);
                    $liked = !empty($post['liked']);
                    $siguiendo = !empty($post['siguiendo']);
                    $textoPost = trim((string) $post['descripcion']);
                ?>
                    <div class="reto-featured-item">

                        <div class="reto-rank-pill">
                            <?= $indice + 1 ?>
                        </div>

                        <article
                            class="reto-post"
                            data-post-id="<?= $idPost ?>"
                        >

                            <header class="reto-post-header">

                                <a
                                    href="<?= e($urlPerfil) ?>"
                                    class="reto-post-avatar"
                                    aria-label="Ver perfil"
                                >
                                    <svg aria-hidden="true">
                                        <use href="#reto-icon-user"></use>
                                    </svg>
                                </a>

                                <a
                                    href="<?= e($urlPerfil) ?>"
                                    class="reto-post-author"
                                >
                                    <?= e($post['nombre_usuario']) ?>
                                </a>

                                <?php if (!$esMio): ?>
                                    <button
                                        type="button"
                                        class="reto-follow<?= $siguiendo ? ' siguiendo' : '' ?>"
                                        data-user-id="<?= (int) $post['id_usuario'] ?>"
                                        data-author="<?= e($post['nombre_usuario']) ?>"
                                        aria-pressed="<?= $siguiendo ? 'true' : 'false' ?>"
                                    >
                                        <?= $siguiendo ? 'Siguiendo' : 'Seguir' ?>
                                    </button>
                                <?php endif; ?>

                            </header>

                            <?php if ($textoPost !== ''): ?>
                                <p class="reto-post-text">
                                    <?= e($textoPost) ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($imgPost !== ''): ?>
                                <div class="reto-post-image">
                                    <img
                                        src="<?= e($imgPost) ?>"
                                        alt="Ilustración destacada del reto"
                                        loading="lazy"
                                        data-asset-opcional
                                    >
                                </div>
                            <?php endif; ?>

                            <footer class="reto-post-actions">

                                <button
                                    type="button"
                                    class="reto-action reto-like<?= $liked ? ' liked' : '' ?>"
                                    data-action="like"
                                    data-post-id="<?= $idPost ?>"
                                    aria-label="Me gusta"
                                    aria-pressed="<?= $liked ? 'true' : 'false' ?>"
                                >
                                    <svg class="reto-icon" aria-hidden="true">
                                        <use href="#reto-icon-heart"></use>
                                    </svg>
                                    <span><?= (int) $post['total_likes'] ?></span>
                                </button>

                                <button
                                    type="button"
                                    class="reto-action"
                                    data-action="comment"
                                    data-post-id="<?= $idPost ?>"
                                    aria-label="Abrir comentarios"
                                >
                                    <svg class="reto-icon" aria-hidden="true">
                                        <use href="#reto-icon-comment"></use>
                                    </svg>
                                    <span><?= (int) $post['total_comentarios'] ?></span>
                                </button>

                                <button
                                    type="button"
                                    class="reto-action"
                                    data-action="share"
                                    data-post-id="<?= $idPost ?>"
                                    aria-label="Compartir publicación"
                                >
                                    <svg class="reto-icon" aria-hidden="true">
                                        <use href="#reto-icon-share"></use>
                                    </svg>
                                </button>

                            </footer>

                        </article>

                    </div>
                <?php endforeach; ?>

            </div>

        </section>

    </main>

    <div
        class="reto-comments-backdrop"
        id="retoComentariosFondo"
        hidden
    >
        <section
            class="reto-comments-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="retoTituloComentarios"
        >

            <div class="reto-comments-head">
                <h2 id="retoTituloComentarios">Comentarios</h2>
                <button
                    type="button"
                    id="retoCerrarComentarios"
                    aria-label="Cerrar comentarios"
                >
                    &times;
                </button>
            </div>

            <div
                class="reto-comments-list"
                id="retoListaComentarios"
            ></div>

            <form
                class="reto-comments-form"
                id="retoFormularioComentario"
            >
                <input
                    type="text"
                    id="retoTextoComentario"
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

    <!-- Mensajes -->
    <div
        class="reto-toast"
        id="retoAviso"
        role="status"
        aria-live="polite"
    ></div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="../assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>" defer></script>
    <script src="../assets/js/reto_dia.js?v=<?= filemtime(__DIR__ . '/../assets/js/reto_dia.js') ?>" defer></script>

</body>
</html>
