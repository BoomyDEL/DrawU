<?php
require_once __DIR__ . '/includes/app.php';

$usuario = requerirLogin();
$yo = (int) $usuario['id_usuario'];
$pdo = conexion();

$paramId = trim((string) ($_GET['id'] ?? ''));

$sqlCom = 'SELECT c.*,
                  (SELECT COUNT(*) FROM comunidades_usuarios cu WHERE cu.id_comunidad = c.id_comunidad) AS total_miembros,
                  EXISTS (SELECT 1 FROM comunidades_usuarios cu2 WHERE cu2.id_comunidad = c.id_comunidad AND cu2.id_usuario = :yo) AS es_miembro
           FROM comunidades c
           WHERE ';
if ($paramId === '') {
    $sqlCom .= '1=1 ORDER BY c.id_comunidad ASC LIMIT 1';
    $stmt = $pdo->prepare($sqlCom);
    $stmt->execute([':yo' => $yo]);
} elseif (ctype_digit($paramId)) {
    $sqlCom .= 'c.id_comunidad = :id LIMIT 1';
    $stmt = $pdo->prepare($sqlCom);
    $stmt->execute([':yo' => $yo, ':id' => (int) $paramId]);
} else {
    $sqlCom .= 'c.slug = :slug LIMIT 1';
    $stmt = $pdo->prepare($sqlCom);
    $stmt->execute([':yo' => $yo, ':slug' => $paramId]);
}

$comunidadActual = $stmt->fetch();
if (!$comunidadActual) {
    http_response_code(404);
    exit('Comunidad no encontrada. <a href="explorar_comunidades.php">Volver a comunidades</a>');
}

$idComunidadNum = (int) $comunidadActual['id_comunidad'];
$slugActual = $comunidadActual['slug'];
$esMiembro = !empty($comunidadActual['es_miembro']);
$portada = url($comunidadActual['imagen_banner'] ?: $comunidadActual['imagen_perfil'] ?: 'assets/img/comunidades/boomy_lentes.png');
$avatarCom = url($comunidadActual['imagen_perfil'] ?: $comunidadActual['imagen_banner'] ?: 'assets/img/comunidades/boomy_lentes.png');
$miembrosTexto = plural((int) $comunidadActual['total_miembros'], 'miembro', 'miembros');

$todasComunidades = obtenerComunidades(['limite' => 10]);
$publicacionesActuales = obtenerPublicaciones(['id_comunidad' => $idComunidadNum], 40);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($comunidadActual['titulo']) ?> | DrawU</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/comunidad_ind.css?v=<?= filemtime(__DIR__ . '/../assets/css/comunidad_ind.css') ?>">
</head>

<body class="comunidad-ind-page">

    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main
        class="com-page"
        id="paginaComunidad"
        data-community-id="<?= $idComunidadNum ?>"
        data-community-slug="<?= e($slugActual) ?>"
        data-is-member="<?= $esMiembro ? '1' : '0' ?>"
    >

        <!-- RUTA DE NAVEGACIÓN -->
        <div class="com-container">

            <nav class="com-breadcrumb" aria-label="Ruta de navegación">

                <a href="explorar_comunidades.php">
                    Comunidades
                </a>

                <span aria-hidden="true">/</span>

                <span>
                    <?= e($comunidadActual['titulo']) ?>
                </span>

            </nav>

        </div>

        <section class="com-banner" aria-label="Portada de la comunidad">

            <img
                src="<?= e($portada) ?>"
                alt=""
                onerror="this.hidden=true"
            >

            <div class="com-banner-overlay"></div>

        </section>

        <div class="com-container">

            <section class="com-identity">

                <div class="com-avatar">

                    <img
                        src="<?= e($avatarCom) ?>"
                        alt=""
                        onerror="this.hidden=true"
                    >

                    <span class="com-avatar-fallback">
                        DrawU
                    </span>

                </div>

                <div class="com-description">

                    <h1>
                        <?= e($comunidadActual['titulo']) ?>
                    </h1>

                    <p>
                        <?= e($comunidadActual['descripcion'] ?: 'Comunidad creativa de DrawU.') ?>
                    </p>

                    <small id="comunidadSubtituloMiembros">
                        <?= e($miembrosTexto) ?>
                        · Publicaciones de esta comunidad
                    </small>

                </div>

                <button
                    class="com-btn com-btn-join<?= $esMiembro ? ' unido' : '' ?>"
                    id="btnUnirse"
                    type="button"
                    aria-pressed="<?= $esMiembro ? 'true' : 'false' ?>"
                >
                    <?= $esMiembro ? 'Unido a la comunidad' : 'Unirse a la comunidad' ?>
                </button>

            </section>

            <nav class="com-others" aria-label="Cambiar de comunidad">

                <?php foreach ($todasComunidades as $datos): ?>

                    <a
                        href="comunidad_ind.php?id=<?= urlencode($datos['slug']) ?>"
                        <?= $datos['slug'] === $slugActual
                            ? 'aria-current="page"'
                            : '' ?>
                    >
                        <?= e($datos['titulo']) ?>
                    </a>

                <?php endforeach; ?>

            </nav>

            <section
                class="com-main-section"
                aria-labelledby="tituloPublicaciones"
            >

                <div class="com-title-row">

                    <h2 id="tituloPublicaciones">
                        Publicaciones
                    </h2>

                    <button
                        type="button"
                        class="com-btn"
                        id="btnAbrirPublicacion"
                    >
                        Crear publicación
                    </button>

                </div>

                <form
                    id="formPublicacionComunidad"
                    class="com-compose"
                    hidden
                >

                    <h3>
                        Nueva publicación en
                        <?= e($comunidadActual['titulo']) ?>
                    </h3>

                    <label for="textoPublicacion">
                        Descripción
                    </label>

                    <textarea
                        id="textoPublicacion"
                        rows="3"
                        maxlength="500"
                        placeholder="Comparte algo con esta comunidad..."
                    ></textarea>

                    <label for="imagenPublicacion">
                        Imagen opcional
                    </label>

                    <input
                        type="file"
                        id="imagenPublicacion"
                        accept="image/png,image/jpeg,image/webp,image/gif"
                    >

                    <p class="com-helper">
                        Tu publicación se guardará directamente en la comunidad.
                    </p>

                    <div class="com-compose-actions">

                        <button
                            type="button"
                            class="com-btn com-btn-muted"
                            id="btnCancelarPublicacion"
                        >
                            Cancelar
                        </button>

                        <button type="submit" class="com-btn">
                            Publicar
                        </button>

                    </div>

                </form>

                <svg
                    class="com-icons-sprite"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >

                    <!-- ICONO DE USUARIO -->
                    <symbol id="com-icon-user" viewBox="0 0 24 24">

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

                    <!-- ICONO DE CORAZÓN -->
                    <symbol id="com-icon-heart" viewBox="0 0 24 24">

                        <path
                            d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linejoin="round"
                        />

                    </symbol>

                    <!-- ICONO DE COMENTARIOS -->
                    <symbol id="com-icon-comment" viewBox="0 0 24 24">

                        <path
                            d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linejoin="round"
                        />

                    </symbol>

                    <!-- ICONO DE COMPARTIR -->
                    <symbol id="com-icon-share" viewBox="0 0 24 24">

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

                <!-- PUBLICACIONES DE LA COMUNIDAD -->
                <div class="com-post-grid" id="listaPublicaciones">

                    <?php foreach ($publicacionesActuales as $post):
                        $esMio = ((int) $post['id_usuario'] === $yo);
                        $urlPerfil = $esMio ? 'usuario_propio.php' : ('usuario_externo.php?id=' . (int) $post['id_usuario']);
                        $imgPost = url($post['ruta_imagen']);
                        $liked = !empty($post['liked']);
                        $siguiendo = !empty($post['siguiendo']);
                        $textoPost = trim((string) ($post['titulo'] ? ($post['titulo'] . ': ' . $post['descripcion']) : $post['descripcion']));
                    ?>

                        <article
                            class="com-post"
                            data-post-id="<?= (int) $post['id_publicacion'] ?>"
                        >

                            <!-- ENCABEZADO -->
                            <header class="com-post-header">

                                <!-- Avatar -->
                                <a
                                    href="<?= e($urlPerfil) ?>"
                                    class="com-post-avatar"
                                    aria-label="Ver perfil"
                                >
                                    <svg aria-hidden="true">
                                        <use href="#com-icon-user"></use>
                                    </svg>
                                </a>

                                <!-- Nombre -->
                                <a
                                    href="<?= e($urlPerfil) ?>"
                                    class="com-post-author"
                                >
                                    <?= e($post['nombre_usuario']) ?>
                                </a>

                                <?php if (!$esMio): ?>
                                    <!-- Seguir -->
                                    <button
                                        type="button"
                                        class="com-follow<?= $siguiendo ? ' siguiendo' : '' ?>"
                                        data-user-id="<?= (int) $post['id_usuario'] ?>"
                                        data-author="<?= e($post['nombre_usuario']) ?>"
                                        aria-pressed="<?= $siguiendo ? 'true' : 'false' ?>"
                                    >
                                        <?= $siguiendo ? 'Siguiendo' : 'Seguir' ?>
                                    </button>
                                <?php endif; ?>

                            </header>

                            <!-- DESCRIPCIÓN -->
                            <?php if ($textoPost !== ''): ?>
                                <p class="com-post-text">
                                    <?= e($textoPost) ?>
                                </p>
                            <?php endif; ?>

                            <!-- IMAGEN -->
                            <?php if ($imgPost !== ''): ?>
                                <div class="com-post-image">
                                    <img
                                        src="<?= e($imgPost) ?>"
                                        alt="Ilustración de la publicación"
                                        loading="lazy"
                                        onerror="this.hidden=true"
                                    >
                                </div>
                            <?php endif; ?>

                            <!-- ACCIONES -->
                            <footer class="com-post-actions">

                                <!-- ME GUSTA -->
                                <button
                                    type="button"
                                    class="com-like<?= $liked ? ' liked' : '' ?>"
                                    data-action="like"
                                    aria-label="Me gusta"
                                    aria-pressed="<?= $liked ? 'true' : 'false' ?>"
                                >
                                    <svg class="com-icon" aria-hidden="true">
                                        <use href="#com-icon-heart"></use>
                                    </svg>

                                    <span><?= (int) $post['total_likes'] ?></span>
                                </button>

                                <!-- COMENTAR -->
                                <button
                                    type="button"
                                    data-action="comment"
                                    aria-label="Abrir comentarios"
                                >
                                    <svg class="com-icon" aria-hidden="true">
                                        <use href="#com-icon-comment"></use>
                                    </svg>

                                    <span><?= (int) $post['total_comentarios'] ?></span>
                                </button>

                                <!-- COMPARTIR -->
                                <button
                                    type="button"
                                    data-action="share"
                                    aria-label="Compartir publicación"
                                >
                                    <svg class="com-icon" aria-hidden="true">
                                        <use href="#com-icon-share"></use>
                                    </svg>
                                </button>

                            </footer>

                        </article>

                    <?php endforeach; ?>

                </div>

                <p
                    class="com-empty"
                    id="comSinPublicaciones"
                    <?= count($publicacionesActuales) > 0 ? 'hidden' : '' ?>
                >
                    No hay publicaciones en esta comunidad.
                </p>

            </section>

        </div>

    </main>

    <div
        class="com-dialog-backdrop"
        id="fondoComentariosCom"
        hidden
    >

        <section
            class="com-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tituloComentariosCom"
        >

            <div class="com-dialog-head">

                <h2 id="tituloComentariosCom">
                    Comentarios
                </h2>

                <button
                    type="button"
                    id="cerrarComentariosCom"
                    aria-label="Cerrar comentarios"
                >
                    &times;
                </button>

            </div>

            <div
                class="com-comment-list"
                id="listaComentariosCom"
            ></div>

            <form
                class="com-comment-form"
                id="formComentariosCom"
            >

                <input
                    id="textoComentarioCom"
                    type="text"
                    maxlength="300"
                    placeholder="Escribe un comentario..."
                    aria-label="Comentario"
                    required
                >

                <button class="com-btn" type="submit">
                    Enviar
                </button>

            </form>

        </section>

    </div>

    <div
        class="com-toast"
        id="avisoComunidadInd"
        role="status"
        aria-live="polite"
    ></div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="../assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>" defer></script>
    <script
        src="../assets/js/comunidad_ind.js?v=<?= filemtime(__DIR__ . '/../assets/js/comunidad_ind.js') ?>"
        defer
    ></script>

</body>
</html>
