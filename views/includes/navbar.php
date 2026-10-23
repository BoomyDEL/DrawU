<?php
/**
 * Componente Navbar lateral
 * Uso: <?php include __DIR__ . '/includes/navbar.php'; ?>
 * Incluye: inicio, videos, comunidades (menú), crear publicación (modal) y configuración (menú).
 */
$navBase = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'views') ? '' : 'views/';
?>
<link rel="stylesheet" href="<?= $navBase ?>includes/navbar.css">

<nav class="sidebar" aria-label="Navegación principal">
    <a class="side-btn" href="<?= $navBase ?>inicio.php" aria-label="Inicio" title="Inicio">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/></svg>
    </a>
    <a class="side-btn" href="<?= $navBase ?>videos.php" aria-label="Videos" title="Videos">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M7 4.5v15l12-7.5z"/></svg>
    </a>

   <div class="side-item">
        <button class="side-btn" data-menu="menuComunidad" aria-label="Comunidades" title="Comunidades" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M8.5 14.5a4.5 4.5 0 0 0 7 0"/><path d="M9 9.5h.01M15 9.5h.01" stroke-width="3"/></svg>
        </button>
        <div class="side-menu" id="menuComunidad" role="menu">
            <button role="menuitem" type="button" id="btnAbrirCrearComunidad">Crear comunidad</button>
            <a role="menuitem" href="<?= $navBase ?>explorar_comunidades.php">Explorar comunidades</a>
        </div>
    </div>

    <a class="side-btn" href="<?= $navBase ?>crear_publi.php" aria-label="Crear publicación" title="Crear publicación">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-6"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4z"/></svg>
    </a>

    <div class="side-item">
        <button class="side-btn" data-menu="menuConfig" aria-label="Configuración" title="Configuración" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
        </button>
        <div class="side-menu" id="menuConfig" role="menu">
            <a role="menuitem" href="<?= $navBase ?>monedas_cambio.php">Estilos de Mascota</a>
            <button role="menuitem" id="toggleTema">Modo oscuro</button>
            <button role="menuitem" id="abrirSoporte">Ayuda y soporte técnico</button>
            <a role="menuitem" href="<?= $navBase ?>pagina_login.php">Cerrar sesión</a>
        </div>
    </div>
</nav>

<!-- Modal: Crear nueva comunidad -->
<div class="overlay" id="modalCrearComunidad" aria-hidden="true">
    <div class="modal-box modal-crear-comunidad" role="dialog" aria-modal="true" aria-labelledby="tituloCrearComunidad">
        <div class="modal-head">
            <span id="tituloCrearComunidad">Crear nueva comunidad</span>
            <button class="modal-cerrar" data-cerrar aria-label="Cerrar">×</button>
        </div>
        <form class="modal-body form-crear-comunidad" id="formCrearComunidad">
            <div class="comunidad-inputs">
                <input type="text" id="nombreComunidad" placeholder="Nombre de la comunidad" class="input-redondeado-lila" required>
                <div class="select-wrapper">
                    <select id="privacidadComunidad" class="input-redondeado-lila" required>
                        <option value="" disabled selected hidden>Elegir privacidad</option>
                        <option value="publica">Pública</option>
                        <option value="privada">Privada</option>
                    </select>
                </div>
            </div>

            <div class="comunidad-archivos-grid">
                <div class="caja-subir-archivo">
                    <span class="label-archivo">Foto de comunidad</span>
                    <label class="tarjeta-archivo" for="fotoComunidadInput">
                        <svg class="icono-archivo-placeholder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="m21 15-5-5L5 21"/>
                        </svg>
                        <span class="preview-nombre" id="nombreFotoComunidad"></span>
                        <span class="btn-subir-pildora">Seleccionar archivo</span>
                        <input type="file" id="fotoComunidadInput" accept="image/*" hidden>
                    </label>
                </div>

                <div class="caja-subir-archivo">
                    <span class="label-archivo">Banner de comunidad</span>
                    <label class="tarjeta-archivo" for="bannerComunidadInput">
                        <svg class="icono-archivo-placeholder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="m21 15-5-5L5 21"/>
                        </svg>
                        <span class="preview-nombre" id="nombreBannerComunidad"></span>
                        <span class="btn-subir-pildora">Seleccionar archivo</span>
                        <input type="file" id="bannerComunidadInput" accept="image/*" hidden>
                    </label>
                </div>
            </div>

            <button type="submit" class="modal-btn-accion">Crear comunidad</button>
        </form>
    </div>
</div>

<!-- Modal: Ayuda y soporte técnico -->
<div class="overlay" id="modalSoporte" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="tituloSoporte">
        <div class="modal-head">
            <span id="tituloSoporte">Ayuda y soporte técnico</span>
            <button class="modal-cerrar" data-cerrar aria-label="Cerrar">×</button>
        </div>
        <form class="modal-body" id="formSoporte">
            <textarea id="mensajeSoporte" maxlength="1000" placeholder="Describe tu problema o duda..." required></textarea>
            <button type="submit" class="modal-btn">Enviar mensaje</button>
        </form>
    </div>
</div>

<script src="<?= $navBase ?>includes/navbar.js" defer></script>
