<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/videos.css">
</head>
<body>

    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="wave" aria-hidden="true"></div>



    <main class="layout">
        <section class="info">
            <div class="usuario">
                <div class="avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <circle cx="12" cy="7.5" r="3.5"/>
                        <path d="M4.5 21v-2a7.5 7.5 0 0 1 15 0v2"/>
                    </svg>
                </div>
                <span>Fulanito_detal</span>
                <button class="seguir" onclick="location.href='usuario_externo.php'">Seguir</button>
            </div>
            <div class="descripcion">Animación 2D viento</div>
        </section>
        <section class="video-shell" id="videoShell">
            <video id="video" controls playsinline preload="metadata" aria-label="Video de la publicación"></video>
            <div class="placeholder">
                <div>
                    <strong>Vista previa del video</strong><span>Agrega la ruta de un archivo de video para mostrarlo aquí.</span>
                </div>
            </div><button class="sonido" id="sonido" aria-label="Silenciar o activar sonido">🔇</button>
        </section>
        <div class="lateral">
        <nav class="acciones" aria-label="Acciones del video">
        <button class="accion" id="like" aria-label="Me gusta" aria-pressed="false">
            <svg viewBox="0 0 24 24">
                <path class="contorno" d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" fill="none" stroke="currentColor" stroke-width="1.9"/><path class="relleno" d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" fill="currentColor"/></svg><span id="likes">0</span></button>
        <button class="accion" id="abrirComentarios" aria-label="Abrir comentarios" aria-expanded="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/></svg><span id="contadorComentarios">0</span></button>
        <button class="accion" id="compartir" aria-label="Compartir video"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></button></nav>
        <div class="cambiar-video" aria-label="Cambiar de video">
            <button class="flecha-video" id="videoAnterior" aria-label="Video anterior">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 15l8-8 8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button class="flecha-video" id="videoSiguiente" aria-label="Video siguiente">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 9l8 8 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
        </div>
    </main>

        <aside class="comentarios" id="panelComentarios" aria-hidden="true">
            <div class="cabecera">
                <span>Comentarios</span>
                <button class="cerrar" id="cerrarComentarios" aria-label="Cerrar comentarios">×</button>
            </div>
            <div class="lista" id="listaComentarios" aria-live="polite">
                <p class="vacio" id="sinComentarios">Todavía no hay comentarios. ¡Sé el primero!</p>
            </div>
            <form class="form-comentario" id="formComentario">
                <input id="textoComentario" maxlength="500" placeholder="Escribe un comentario..." aria-label="Escribe un comentario" required>
                <button type="submit">Publicar</button>
            </form>
        </aside>



    

    <script src="../assets/js/videos.js"></script>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>