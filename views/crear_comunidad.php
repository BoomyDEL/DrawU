<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Comunidad | DrawU</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/crear_comunidad.css">
   
</head>
<body>

    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="pagina-crear-comunidad">
        <div class="tarjeta-pagina-comunidad">
            <div class="cabecera-tarjeta">
                Crear nueva comunidad
            </div>
            <form class="modal-body form-crear-comunidad" id="formPaginaComunidad">
                <div class="comunidad-inputs">
                    <input type="text" id="nombreComunidadPag" placeholder="Nombre de la comunidad" class="input-redondeado-lila" required>
                    <div class="select-wrapper">
                        <select id="privacidadComunidadPag" class="input-redondeado-lila" required>
                            <option value="" disabled selected hidden>Elegir privacidad</option>
                            <option value="publica">Pública</option>
                            <option value="privada">Privada</option>
                        </select>
                    </div>
                </div>

                <div class="comunidad-archivos-grid">
                    <div class="caja-subir-archivo">
                        <span class="label-archivo">Foto de comunidad</span>
                        <label class="tarjeta-archivo" for="fotoComunidadPagInput">
                            <svg class="icono-archivo-placeholder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <path d="m21 15-5-5L5 21"/>
                            </svg>
                            <span class="preview-nombre" id="nombreFotoComunidadPag"></span>
                            <span class="btn-subir-pildora">Seleccionar archivo</span>
                            <input type="file" id="fotoComunidadPagInput" accept="image/*" hidden>
                        </label>
                    </div>

                    <div class="caja-subir-archivo">
                        <span class="label-archivo">Banner de comunidad</span>
                        <label class="tarjeta-archivo" for="bannerComunidadPagInput">
                            <svg class="icono-archivo-placeholder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <path d="m21 15-5-5L5 21"/>
                            </svg>
                            <span class="preview-nombre" id="nombreBannerComunidadPag"></span>
                            <span class="btn-subir-pildora">Seleccionar archivo</span>
                            <input type="file" id="bannerComunidadPagInput" accept="image/*" hidden>
                        </label>
                    </div>
                </div>

                <button type="submit" class="modal-btn-accion">Crear comunidad</button>
            </form>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/crear_comunidad.js"></script>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
