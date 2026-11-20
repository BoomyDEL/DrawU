<?php
require_once __DIR__ . '/includes/app.php';

$usuario = requerirLogin();
$yo = (int) $usuario['id_usuario'];

$misPublicaciones = obtenerPublicaciones(['id_usuario' => $yo], 40);
$misComunidades = obtenerComunidades(['id_miembro' => $yo]);
if (empty($misComunidades)) {
    $misComunidades = obtenerComunidades(['limite' => 6]);
}

$coloresFallback = ['boomy', 'redisenos', 'arte', 'historias'];
$imgPerfilUrl = url($usuario['imagen_perfil']);
$colorMascotaPropia = $usuario['mascota_color'] ?: match ((int) ($usuario['id_mascota_activa'] ?? 1)) {
    2 => 'aqua',
    3 => 'verde',
    default => 'morado',
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil | DrawU</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/usuario_propio.css?v=<?= filemtime(__DIR__ . '/../assets/css/usuario_propio.css') ?>">
    <link rel="stylesheet" href="../assets/css/publicaciones.css?v=<?= filemtime(__DIR__ . '/../assets/css/publicaciones.css') ?>">
</head>
<body>

    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <!-- Banner superior -->
    <header class="banner banner-propio banner-mascota-<?= e($colorMascotaPropia) ?>">
       

        <!-- Opciones de la foto de perfil -->
        <div class="menu-foto oculto" id="menuFoto">
            <button type="button" id="verFoto">
                Ver foto del perfil
            </button>

            <button type="button" id="elegirFoto">
                Elegir foto de perfil
            </button>
        </div>

        <!-- Opciones de edición -->
        <div class="menu-edicion oculto" id="menuEdicion">
            <button type="button" id="editarNombre">
                Editar nombre de usuario
            </button>

            <button type="button" id="editarDescripcion">
                Editar descripción
            </button>
        </div>
    </header>

    <!-- Perfil del usuario -->
    <section class="perfil">

        <div class="contenedor-foto">
            <button
                class="foto-perfil"
                id="fotoPerfil"
                type="button"
                aria-label="Opciones de foto de perfil"
                aria-expanded="false">

                <?php if ($imgPerfilUrl !== ''): ?>
                    <img src="<?= e($imgPerfilUrl) ?>" alt="Foto de perfil">
                <?php else: ?>
                    <span id="textoFoto">Imagen</span>
                <?php endif; ?>
            </button>

            <input
                type="file"
                id="archivoFoto"
                accept="image/*"
                hidden>
        </div>

        <div class="datos-perfil">

            <div class="nombre-con-menu">
                <h1 id="nombreUsuario"><?= e($usuario['nombre_usuario']) ?></h1>

                <button
                    class="tres-puntos"
                    id="opcionesUsuario"
                    type="button"
                    aria-label="Opciones del perfil"
                    aria-expanded="false">
                    ⋮
                </button>

                <div
                    class="menu-puntos oculto"
                    id="menuPuntos">

                    <button type="button" id="puntosEditarNombre">
                        Editar nombre de usuario
                    </button>

                    <button type="button" id="puntosEditarDescripcion">
                        Editar descripción
                    </button>
                </div>
            </div>

            <div class="estadisticas">
                <span id="nombreHandle">@<?= e(strtolower($usuario['nombre_usuario'])) ?></span>

                <div>
                    <span><?= (int) $usuario['total_publicaciones'] ?> Publicaciones</span>
                    <span><?= (int) $usuario['total_seguidores'] ?> Seguidores</span>
                    <span><?= (int) $usuario['total_seguidos'] ?> Seguidos</span>
                </div>
            </div>

            <p id="descripcionPerfil">
                <?= e($usuario['descripcion_perfil'] ?: 'Sin descripción') ?>
            </p>

        </div>
    </section>

    <!-- Publicaciones -->
    <section class="publicaciones">

        <?php foreach ($misPublicaciones as $pub):
            $imgPub = url($pub['ruta_imagen']);
            $liked = !empty($pub['liked']);
            $textoPub = trim((string) ($pub['titulo'] ? ($pub['titulo'] . ': ' . $pub['descripcion']) : $pub['descripcion']));
        ?>
            <article class="publicacion" data-publicacion-id="<?= (int) $pub['id_publicacion'] ?>">
                <header class="encabezado-publicacion">
                    <span class="avatar-mini">♙</span>
                    <a class="autor-publicacion" href="usuario_propio.php"><?= e($pub['nombre_usuario']) ?></a>
                </header>

                <?php if ($textoPub !== '' && $imgPub === ''): ?>
                    <p style="padding:12px 16px;margin:0;"><?= e($textoPub) ?></p>
                <?php endif; ?>

                <?php if ($imgPub !== ''): ?>
                    <div class="imagen-publicacion">
                        <img
                            src="<?= e($imgPub) ?>"
                            alt="<?= e($textoPub ?: 'Publicación de ' . $pub['nombre_usuario']) ?>">
                    </div>
                <?php endif; ?>

                <footer class="acciones-publicacion">
                    <button class="accion accion-like<?= $liked ? ' liked' : '' ?>" type="button" aria-label="Me gusta" aria-pressed="<?= $liked ? 'true' : 'false' ?>"><svg viewBox="0 0 24 24"><path class="contorno" d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" fill="none" stroke="currentColor" stroke-width="1.9"/><path class="relleno" d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" fill="currentColor"/></svg><span class="contador"><?= (int) $pub['total_likes'] ?></span></button>
                    <button class="accion accion-comentar" type="button" aria-label="Comentarios"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/></svg><span class="contador"><?= (int) $pub['total_comentarios'] ?></span></button>
                    <button class="accion accion-compartir" type="button" aria-label="Compartir"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></button>
                </footer>
            </article>
        <?php endforeach; ?>

    </section>

    <!-- Carrusel de comunidades -->
    <section class="seccion-comunidades">

        <h2>Comunidades</h2>

        <div class="carrusel">

            <button
                class="flecha"
                id="anterior"
                type="button"
                aria-label="Comunidades anteriores">
                ‹
            </button>

            <div class="ventana-carrusel" id="ventanaCarrusel">

                <div class="pista-carrusel" id="pistaCarrusel">

                    <?php foreach ($misComunidades as $idx => $com):
                        $colorFb = $coloresFallback[$idx % count($coloresFallback)];
                        $imgCom = url($com['imagen_perfil'] ?: $com['imagen_banner']);
                    ?>
                        <article class="comunidad" tabindex="0">

                            <?php if ($imgCom !== ''): ?>
                                <img
                                    src="<?= e($imgCom) ?>"
                                    alt="Comunidad <?= e($com['titulo']) ?>"
                                    onerror="this.style.display='none';">
                            <?php endif; ?>

                            <div class="portada-fallback <?= e($colorFb) ?>">
                                <span><?= e($com['titulo']) ?></span>
                            </div>

                            <div class="info-comunidad">
                                <div>
                                    <h3 style="color:white"><?= e($com['titulo']) ?></h3>

                                    <p>
                                        <?= e($com['descripcion'] ?: 'Comunidad artística de DrawU.') ?>
                                    </p>
                                </div>

                                <a href="comunidad_ind.php?id=<?= urlencode($com['slug']) ?>">
                                    Ver más
                                </a>
                            </div>

                        </article>
                    <?php endforeach; ?>

                </div>
            </div>

            <button class="flecha" id="siguiente" type="button" aria-label="Siguientes comunidades">›
            </button>

        </div>
    </section>

    <!-- Panel de comentarios -->
    <aside class="comentarios" id="panelComentarios" aria-hidden="true">
        <div class="cabecera">
            <span>Comentarios</span>
            <button class="cerrar" id="cerrarComentarios" aria-label="Cerrar comentarios">×</button>
        </div>
        <div class="lista" id="listaComentarios" aria-live="polite"></div>
        <form class="form-comentario" id="formComentario">
            <input id="textoComentario" maxlength="500" placeholder="Escribe un comentario..." aria-label="Escribe un comentario" required>
            <button type="submit">Publicar</button>
        </form>
    </aside>

    <script src="../assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>
    <script src="../assets/js/publicaciones.js?v=<?= filemtime(__DIR__ . '/../assets/js/publicaciones.js') ?>"></script>
    <script src="../assets/js/usuario_propio.js?v=<?= filemtime(__DIR__ . '/../assets/js/usuario_propio.js') ?>"></script>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
