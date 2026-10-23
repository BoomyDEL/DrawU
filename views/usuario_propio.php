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
    <link rel="stylesheet" href="../assets/css/usuario_propio.css">
    <link rel="stylesheet" href="../assets/css/publicaciones.css">
</head>
<body>

    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    

    <!-- Banner superior -->
    <header class="banner">
        <h2>Banner del personaje elegido</h2>

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

                <span id="textoFoto">Imagen</span>
            </button>

            <input
                type="file"
                id="archivoFoto"
                accept="image/*"
                hidden>
        </div>

        <div class="datos-perfil">

            <div class="nombre-con-menu">
                <h1 id="nombreUsuario">Usuario</h1>

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
                <span id="nombreHandle">@usuario</span>

                <div>
                    <span>0 Publicaciones</span>
                    <span>0 Seguidores</span>
                    <span>0 Seguidos</span>
                </div>
            </div>

            <p id="descripcionPerfil">
                Breve descripción del usuario
            </p>

        </div>
    </section>

    <!-- Publicaciones -->
    <section class="publicaciones">

        <article class="publicacion">
            <header class="encabezado-publicacion">
                <span class="avatar-mini">♙</span>
                <a class="autor-publicacion" href="usuario_propio.php">Usuario</a>

                <button type="button" class="boton-seguir">Seguir</button>
            </header>

            <div class="imagen-publicacion">
                <img
                    src="../assets/img/publis/Luke.png"
                    alt="Perro ilust Luke">
            </div>

            <footer class="acciones-publicacion">
                <button class="accion accion-like" type="button" aria-label="Me gusta" aria-pressed="false"><svg viewBox="0 0 24 24"><path class="contorno" d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" fill="none" stroke="currentColor" stroke-width="1.9"/><path class="relleno" d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" fill="currentColor"/></svg><span class="contador">0</span></button>
                <button class="accion accion-comentar" type="button" aria-label="Comentarios"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/></svg><span class="contador">0</span></button>
                <button class="accion accion-compartir" type="button" aria-label="Compartir"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></button>
            </footer>
        </article>

        <article class="publicacion">
            <header class="encabezado-publicacion">
                <span class="avatar-mini">♙</span>
                <a class="autor-publicacion" href="usuario_propio.php">Usuario</a>

                <button type="button" class="boton-seguir">Seguir</button>
            </header>

            <div class="imagen-publicacion">
                <img
                    src="../assets/img/publis/visemas_lilly.png"
                    alt="visemas lilly">
            </div>

            <footer class="acciones-publicacion">
                <button class="accion accion-like" type="button" aria-label="Me gusta" aria-pressed="false"><svg viewBox="0 0 24 24"><path class="contorno" d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" fill="none" stroke="currentColor" stroke-width="1.9"/><path class="relleno" d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" fill="currentColor"/></svg><span class="contador">0</span></button>
                <button class="accion accion-comentar" type="button" aria-label="Comentarios"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/></svg><span class="contador">0</span></button>
                <button class="accion accion-compartir" type="button" aria-label="Compartir"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></button>
            </footer>
        </article>

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

                    <!-- Comunidad 1 -->
                    <article class="comunidad" tabindex="0">

                        <img
                            src="../assets/img/comunidades/boomy_lentes.png"
                            alt="Comunidad Boom día, Boomy!"
                            onerror="this.style.display='none';">

                        <div class="portada-fallback boomy">
                            <span>Boom día, Boomy!</span>
                        </div>

                        <div class="info-comunidad">
                            <div>
                                <h3 style="color:white">Boom día, Boomy!</h3>

                                <p>
                                    Comparte aventuras, personajes y
                                    momentos del mundo de Boomy.
                                </p>
                            </div>

                            <a href="comunidad_ind.php">
                                Ver más
                            </a>
                        </div>

                    </article>

                    <!-- Comunidad 2 -->
                    <article class="comunidad" tabindex="0">

                        <img
                            src="../assets/img/comunidades/LSS_Lilly.png"
                            alt="Comunidad de rediseño de personajes"
                            onerror="this.style.display='none';">

                        <div class="portada-fallback redisenos">
                            <span>Rediseño ✦</span>
                        </div>

                        <div class="info-comunidad">
                            <div>
                                <h3 style="color:white">Rediseño de personajes</h3>

                                <p>
                                    Compartimos diseños de
                                    nuestros OC's y retros.
                                </p>
                            </div>

                            <a href="comunidad_ind.php">
                                Ver más
                            </a>
                        </div>

                    </article>

                    <!-- Comunidad 3 -->
                    <article class="comunidad" tabindex="0">

                        <img
                            src="../assets/img/comunidades/hermanitos.png"
                            alt="Comunidad de arte y creatividad"
                            onerror="this.style.display='none';">

                        <div class="portada-fallback arte">
                            <span>Arte y color ♡</span>
                        </div>

                        <div class="info-comunidad">
                            <div>
                                <h3 style="color:white">Arte y color</h3>

                                <p>
                                    Comparte dibujos, ilustraciones
                                    y proyectos creativos.
                                </p>
                            </div>

                            <a href="comunidad_ind.php">
                                Ver más
                            </a>
                        </div>

                    </article>

                    <!-- Comunidad 4 -->
                    <article class="comunidad" tabindex="0">

                        <img
                            src="../assets/img/comunidades/claro_oscuro.png"
                            alt="Comunidad de arte y creatividad"
                            onerror="this.style.display='none';">

                        <div class="portada-fallback historias">
                            <span>Noobs del dibujo</span>
                        </div>

                        <div class="info-comunidad">
                            <div>
                                <h3 style="color:white">Noobs del dibujo</h3>

                                <p>
                                    Somos un grupo de noobs intentando
                                    dibujar sin morir en el intento.
                                </p>
                            </div>

                            <a href="comunidad_ind.php">
                                Ver más
                            </a>
                        </div>

                    </article>

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
    <script src="../assets/js/publicaciones.js"></script>    <script src="../assets/js/usuario_propio.js"></script>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>