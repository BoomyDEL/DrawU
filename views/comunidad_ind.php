

<?php
// Datos de demostración, sin conexión a base de datos.
$comunidades = [
    'boomy' => [
        'nombre' => 'Boom día, Boomy!',
        'descripcion' => 'Un espacio para compartir aventuras, personajes y dibujos del mundo de Boomy.',
        'portada' => '../assets/img/comunidades/boomy_lentes.png',
        'miembros' => '67 mil miembros'
    ],
    'redisenos' => [
        'nombre' => 'Rediseño de personajes',
        'descripcion' => 'Muestra tus rediseños, personajes originales y procesos creativos.',
        'portada' => '../assets/img/comunidades/LSS_Lilly.png',
        'miembros' => 'Comunidad creativa'
    ],
    'arte' => [
        'nombre' => 'Arte y color',
        'descripcion' => 'Ilustración, técnicas, colores e inspiración para artistas.',
        'portada' => '../assets/img/comunidades/hermanitos.png',
        'miembros' => 'Comunidad creativa'
    ],
    'noobs' => [
        'nombre' => 'Noobs del dibujo',
        'descripcion' => 'Aprendemos a dibujar y compartimos nuestros avances.',
        'portada' => '../assets/img/comunidades/claro_oscuro.png',
        'miembros' => 'Comunidad de aprendizaje'
    ],
    'lss' => [
        'nombre' => 'Little Silly Singer',
        'descripcion' => 'Dibujos y personajes inspirados en Little Silly Singer.',
        'portada' => '../assets/img/comunidades/LSS_Lilly.png',
        'miembros' => 'Comunidad creativa'
    ]
];

// Cada publicación pertenece a una comunidad específica.
$publicaciones = [
    [
        'id' => 'ejemplo-1',
        'comunidad' => 'boomy',
        'autor' => 'Fulanito_detal',
        'texto' => 'Mi ilustración de hoy. ¿Qué les parece?',
        'imagen' => '../assets/img/publis/Luke.png'
    ],
    [
        'id' => 'ejemplo-2',
        'comunidad' => 'boomy',
        'autor' => 'Usuario',
        'texto' => 'Comparto otro dibujo con la comunidad.',
        'imagen' => '../assets/img/publis/visemas_lilly.png'
    ],
    [
        'id' => 'ejemplo-3',
        'comunidad' => 'redisenos',
        'autor' => 'Usuario',
        'texto' => 'Probando un nuevo estilo de personaje.',
        'imagen' => '../assets/img/publis/visemas_lilly.png'
    ],
    [
        'id' => 'ejemplo-4',
        'comunidad' => 'arte',
        'autor' => 'Fulanito_detal',
        'texto' => 'Práctica de color e iluminación.',
        'imagen' => '../assets/img/publis/Luke.png'
    ],
    [
        'id' => 'ejemplo-5',
        'comunidad' => 'noobs',
        'autor' => 'Usuario',
        'texto' => 'Mi práctica de dibujo de hoy.',
        'imagen' => '../assets/img/publis/Luke.png'
    ],
    [
        'id' => 'ejemplo-6',
        'comunidad' => 'lss',
        'autor' => 'Usuario',
        'texto' => 'Compartiendo fanart de Little Silly Singer.',
        'imagen' => '../assets/img/publis/visemas_lilly.png'
    ]
];

// Obtener la comunidad seleccionada en la URL.
$idComunidad = (string) ($_GET['id'] ?? 'boomy');

if (!isset($comunidades[$idComunidad])) {
    http_response_code(404);

    exit(
        'Comunidad no encontrada. ' .
        '<a href="explorar_comunidades.php">Volver a comunidades</a>'
    );
}

$comunidadActual = $comunidades[$idComunidad];

// Mostrar solamente publicaciones de esta comunidad.
$publicacionesActuales = array_values(array_filter(
    $publicaciones,
    fn($post) => $post['comunidad'] === $idComunidad
));

// Evitar que los textos se interpreten como HTML.
function esc($dato) {
    return htmlspecialchars((string) $dato, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= esc($comunidadActual['nombre']) ?> | DrawU</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css">


    <link rel="stylesheet" href="../assets/css/comunidad_ind.css">
</head>

<body class="comunidad-ind-page">

    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main
        class="com-page"
        id="paginaComunidad"
        data-community-id="<?= esc($idComunidad) ?>"
    >

        <!-- RUTA DE NAVEGACIÓN -->
        <div class="com-container">

            <nav class="com-breadcrumb" aria-label="Ruta de navegación">

                <a href="explorar_comunidades.php">
                    Comunidades
                </a>

                <span aria-hidden="true">/</span>

                <span>
                    <?= esc($comunidadActual['nombre']) ?>
                </span>

            </nav>

        </div>


        <section class="com-banner" aria-label="Portada de la comunidad">


            <img
                src="<?= esc($comunidadActual['portada']) ?>"
                alt=""
                onerror="this.hidden=true"
            >

            <div class="com-banner-overlay"></div>

        </section>


        <div class="com-container">


            <section class="com-identity">

                <div class="com-avatar">

                    <img
                        src="<?= esc($comunidadActual['portada']) ?>"
                        alt=""
                        onerror="this.hidden=true"
                    >

                    <span class="com-avatar-fallback">
                        DrawU
                    </span>

                </div>


                <div class="com-description">

                    <h1>
                        <?= esc($comunidadActual['nombre']) ?>
                    </h1>

                    <p>
                        <?= esc($comunidadActual['descripcion']) ?>
                    </p>

                    <small>
                        <?= esc($comunidadActual['miembros']) ?>
                        · Publicaciones de esta comunidad
                    </small>

                </div>


                <button
                    class="com-btn com-btn-join"
                    id="btnUnirse"
                    type="button"
                    aria-pressed="false"
                >
                    Unirse a la comunidad
                </button>

            </section>


            <nav class="com-others" aria-label="Cambiar de comunidad">

                <?php foreach ($comunidades as $slug => $datos): ?>

                    <a
                        href="comunidad_ind.php?id=<?= urlencode($slug) ?>"
                        <?= $slug === $idComunidad
                            ? 'aria-current="page"'
                            : '' ?>
                    >
                        <?= esc($datos['nombre']) ?>
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
                        <?= esc($comunidadActual['nombre']) ?>
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
                        Imagen opcional (máximo 250 KB)
                    </label>

                    <input
                        type="file"
                        id="imagenPublicacion"
                        accept="image/png,image/jpeg,image/webp"
                    >

                    <p class="com-helper">
                        Sin SQL: esta publicación de prueba se guarda
                        en tu navegador.
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


                <!-- =====================================
                     PUBLICACIONES DE LA COMUNIDAD
                ====================================== -->

                <div class="com-post-grid" id="listaPublicaciones">

                    <?php foreach ($publicacionesActuales as $post): ?>

                        <article
                            class="com-post"
                            data-post-id="<?= esc($post['id']) ?>"
                        >

                            <!-- ENCABEZADO -->
                            <header class="com-post-header">

                                <!-- Avatar -->
                                <a
                                    href="usuario_externo.php"
                                    class="com-post-avatar"
                                    aria-label="Ver perfil"
                                >
                                    <svg aria-hidden="true">
                                        <use href="#com-icon-user"></use>
                                    </svg>
                                </a>


                                <!-- Nombre -->
                                <a
                                    href="usuario_externo.php"
                                    class="com-post-author"
                                >
                                    <?= esc($post['autor']) ?>
                                </a>


                                <!-- Seguir -->
                                <button
                                    type="button"
                                    class="com-follow"
                                    data-author="<?= esc($post['autor']) ?>"
                                    aria-pressed="false"
                                >
                                    Seguir
                                </button>

                            </header>


                            <!-- DESCRIPCIÓN -->
                            <?php if (trim($post['texto']) !== ''): ?>

                                <p class="com-post-text">
                                    <?= esc($post['texto']) ?>
                                </p>

                            <?php endif; ?>


                            <!-- IMAGEN -->
                            <?php if ($post['imagen'] !== ''): ?>

                                <div class="com-post-image">

                                    <!--
                                        TODO: Cambiar la ruta en el arreglo
                                        PHP si falta la imagen real.
                                    -->

                                    <img
                                        src="<?= esc($post['imagen']) ?>"
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
                                    class="com-like"
                                    data-action="like"
                                    aria-label="Me gusta"
                                    aria-pressed="false"
                                >
                                    <svg class="com-icon" aria-hidden="true">
                                        <use href="#com-icon-heart"></use>
                                    </svg>

                                    <span>0</span>
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

                                    <span>0</span>
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
                    hidden
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

    <script
        src="../assets/js/comunidad_ind.js"
        defer
    ></script>

</body>
</html>
