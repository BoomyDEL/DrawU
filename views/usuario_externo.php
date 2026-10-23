<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fulanito_detal | DrawU</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/usuario_propio.css">
    <link rel="stylesheet" href="../assets/css/publicaciones.css">
    <link rel="stylesheet" href="../assets/css/usuario_externo.css">
</head>
<body>

    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <!-- Banner superior -->
    <header class="banner">
        <h2>Banner del personaje elegido</h2>
    </header>

    <!-- Perfil del usuario externo -->
    <section class="perfil">

        <div class="contenedor-foto">
            <!-- Solo se puede visualizar, no cambiar -->
            <button class="foto-perfil" id="fotoPerfil" type="button" aria-label="Ver foto de perfil">
                <span id="textoFoto">Imagen</span>
            </button>
        </div>

        <div class="datos-perfil">

            <div class="nombre-con-menu">
                <h1 id="nombreUsuario">Fulanito_detal</h1>

                <button class="tres-puntos" id="opcionesUsuario" type="button" aria-label="Opciones del usuario" aria-expanded="false">
                    ⋮
                </button>

                <!-- Menú con el estilo del de comunidad -->
                <div class="menu-externo oculto" id="menuPuntos" role="menu">
                    <button type="button" role="menuitem" class="boton-seguir" data-usuario="Fulanito_detal">Seguir</button>
                    <button type="button" role="menuitem" id="bloquearUsuario">Bloquear usuario</button>
                    <button type="button" role="menuitem" id="reportarUsuario">Reportar usuario</button>
                </div>

                <button type="button" class="boton-seguir boton-seguir-perfil" data-usuario="Fulanito_detal">Seguir</button>
            </div>

            <div class="estadisticas">
                <span id="nombreHandle">@fulanito_detal</span>

                <div>
                    <span>2 Publicaciones</span>
                    <span id="contadorSeguidores">0 Seguidores</span>
                    <span>0 Seguidos</span>
                </div>
            </div>

            <p id="descripcionPerfil">
                Breve descripción del usuario
            </p>

        </div>
    </section>

    <!-- Aviso de usuario bloqueado -->
    <p class="aviso-bloqueo oculto" id="avisoBloqueo">
        Has bloqueado a este usuario. No verás sus publicaciones.
    </p>

    <div id="contenidoUsuario">
    <!-- Publicaciones -->
    <section class="publicaciones">

        <article class="publicacion">
            <header class="encabezado-publicacion">
                <span class="avatar-mini">♙</span>
                <a class="autor-publicacion" href="usuario_externo.php">Fulanito_detal</a>

                <button type="button" class="boton-seguir" data-usuario="Fulanito_detal">Seguir</button>
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
                <a class="autor-publicacion" href="usuario_externo.php">Fulanito_detal</a>

                <button type="button" class="boton-seguir" data-usuario="Fulanito_detal">Seguir</button>
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
    </div>

    <!-- Visor de foto de perfil -->
    <div class="overlay" id="modalFoto" aria-hidden="true">
        <div class="visor-foto" role="dialog" aria-modal="true" aria-label="Foto de perfil">
            <button class="modal-cerrar" data-cerrar aria-label="Cerrar">×</button>
            <div class="visor-contenido" id="visorContenido"></div>
        </div>
    </div>

    <!-- Modal: Reportar usuario -->
    <div class="overlay" id="modalReporte" aria-hidden="true">
        <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="tituloReporte">
            <div class="modal-head">
                <span id="tituloReporte">Reportar a Fulanito_detal</span>
                <button class="modal-cerrar" data-cerrar aria-label="Cerrar">×</button>
            </div>
            <form class="modal-body form-reporte" id="formReporte">
                <label><input type="radio" name="motivo" value="spam" required> Spam</label>
                <label><input type="radio" name="motivo" value="acoso"> Acoso o bullying</label>
                <label><input type="radio" name="motivo" value="inapropiado"> Contenido inapropiado</label>
                <label><input type="radio" name="motivo" value="plagio"> Plagio de arte</label>
                <label><input type="radio" name="motivo" value="otro"> Otro</label>
                <textarea id="detalleReporte" maxlength="500" placeholder="Detalles (opcional)"></textarea>
                <button type="submit" class="modal-btn">Enviar reporte</button>
            </form>
        </div>
    </div>

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
    <script src="../assets/js/publicaciones.js"></script>
    <script src="../assets/js/usuario_externo.js"></script>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
