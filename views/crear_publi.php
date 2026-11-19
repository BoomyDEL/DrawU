<?php
require_once __DIR__ . '/includes/app.php';

$usuario = requerirLogin();
$yo = (int) $usuario['id_usuario'];

// Comunidades a las que pertenece el usuario (para avisos o publicar en comunidad)
$misComunidades = obtenerComunidades(['id_miembro' => $yo]);
$retoActual = obtenerRetoActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Publicación | DrawU</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../views/includes/navbar.css?v=<?= filemtime(__DIR__ . '/includes/navbar.css') ?>">
    <link rel="stylesheet" href="../assets/css/crear_publi.css?v=<?= filemtime(__DIR__ . '/../assets/css/crear_publi.css') ?>">
</head>
<body>

    <?php include __DIR__ . '/includes/header/header.php'; ?>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="pagina-crear-publi">
        <div class="tarjeta-crear-publi">
            <div class="cabecera-publi">
                <span id="tituloSeccion">Crear una publicación</span>
            </div>

            <!-- Pestañas para elegir Publicación Normal o Aviso de Comunidad -->
            <div class="pestanas-publi" role="tablist">
                <button type="button" class="tab-btn activo" id="tabPubliNormal" role="tab" aria-selected="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>
                    </svg>
                    Publicación normal
                </button>
                <button type="button" class="tab-btn" id="tabAvisoComunidad" role="tab" aria-selected="false">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    Aviso de comunidad
                </button>
            </div>

            <!-- Formulario común -->
            <form class="contenido-publi-form" id="formCrearPubli">
                
                <!-- Panel 1: Publicación normal (requiere archivo de imagen/video + pie de foto opcional) -->
                <div class="panel-tipo" id="panelPubliNormal">
                    <label class="zona-drop-archivo" for="inputArchivoNormal">
                        <div class="preview-media-container" id="previewMedia"></div>
                        <svg class="icono-subir" id="iconoSubirNormal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="m21 15-5-5L5 21"/>
                        </svg>
                        <span class="nombre-archivo-seleccionado" id="nombreArchivoNormal"></span>
                        <span class="btn-seleccionar-pildora">Seleccionar imagen o video</span>
                        <input type="file" id="inputArchivoNormal" accept="image/*,video/*" hidden>
                    </label>

                    <textarea class="textarea-publi" id="descripcionNormal" placeholder="Escribe un pie de foto o descripción para tu publicación..."></textarea>

                    <?php if (!empty($retoActual)): ?>
                        <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;margin-top:4px;cursor:pointer;">
                            <input type="checkbox" id="checkParticiparReto" value="<?= (int) $retoActual['id_reto'] ?>" <?= !empty($_GET['reto']) ? 'checked' : '' ?>>
                            <span>Participar en el reto actual: <strong><?= e($retoActual['titulo']) ?></strong> (+20 Draws en tu primera entrega)</span>
                        </label>
                    <?php endif; ?>
                </div>

                <!-- Panel 2: Aviso de comunidad (selección de comunidad + título + mensaje + imagen opcional) -->
                <div class="panel-tipo oculto" id="panelAvisoComunidad">
                    <div class="select-wrapper">
                        <select id="selectComunidadAviso" class="input-redondeado-lila">
                            <option value="" disabled selected hidden>Selecciona una comunidad</option>
                            <?php foreach ($misComunidades as $com): ?>
                                <option value="<?= (int) $com['id_comunidad'] ?>" data-slug="<?= e($com['slug']) ?>"><?= e($com['titulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="text" id="tituloAviso" placeholder="Título o asunto del aviso..." class="input-redondeado-lila">

                    <textarea class="textarea-publi" id="mensajeAviso" rows="4" placeholder="Escribe el mensaje o anuncio para los miembros de la comunidad..."></textarea>

                    <label class="zona-drop-archivo" for="inputArchivoAviso" style="min-height: 110px;">
                        <span class="nombre-archivo-seleccionado" id="nombreArchivoAviso"></span>
                        <span class="btn-seleccionar-pildora">Adjuntar imagen (opcional)</span>
                        <input type="file" id="inputArchivoAviso" accept="image/*" hidden>
                    </label>
                </div>

                <div class="pie-formulario">
                    <a href="inicio.php" class="btn-cancelar">Cancelar</a>
                    <button type="submit" class="btn-publicar" id="btnSubmitPubli">Publicar</button>
                </div>
            </form>
        </div>
    </main>

    <script src="../assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>
    <script>
        const tabNormal = document.getElementById('tabPubliNormal');
        const tabAviso = document.getElementById('tabAvisoComunidad');
        const panelNormal = document.getElementById('panelPubliNormal');
        const panelAviso = document.getElementById('panelAvisoComunidad');
        const tituloSeccion = document.getElementById('tituloSeccion');
        const btnSubmit = document.getElementById('btnSubmitPubli');
        let modoActual = 'normal';

        tabNormal.addEventListener('click', () => {
            tabNormal.classList.add('activo');
            tabAviso.classList.remove('activo');
            panelNormal.classList.remove('oculto');
            panelAviso.classList.add('oculto');
            tabNormal.setAttribute('aria-selected', 'true');
            tabAviso.setAttribute('aria-selected', 'false');
            tituloSeccion.textContent = 'Crear una publicación';
            btnSubmit.textContent = 'Publicar';
            modoActual = 'normal';
        });

        tabAviso.addEventListener('click', () => {
            tabAviso.classList.add('activo');
            tabNormal.classList.remove('activo');
            panelAviso.classList.remove('oculto');
            panelNormal.classList.add('oculto');
            tabAviso.setAttribute('aria-selected', 'true');
            tabNormal.setAttribute('aria-selected', 'false');
            tituloSeccion.textContent = 'Publicar aviso de comunidad';
            btnSubmit.textContent = 'Enviar aviso';
            modoActual = 'aviso';
        });

        const inputNormal = document.getElementById('inputArchivoNormal');
        const txtNombreNormal = document.getElementById('nombreArchivoNormal');
        const previewContainer = document.getElementById('previewMedia');
        const iconoSubirNormal = document.getElementById('iconoSubirNormal');

        inputNormal.addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;
            txtNombreNormal.textContent = file.name;

            previewContainer.innerHTML = '';
            const url = URL.createObjectURL(file);
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = url;
                previewContainer.appendChild(img);
                previewContainer.style.display = 'flex';
                iconoSubirNormal.style.display = 'none';
            } else if (file.type.startsWith('video/')) {
                const vid = document.createElement('video');
                vid.src = url;
                vid.controls = true;
                previewContainer.appendChild(vid);
                previewContainer.style.display = 'flex';
                iconoSubirNormal.style.display = 'none';
            }
        });

        const inputAviso = document.getElementById('inputArchivoAviso');
        const txtNombreAviso = document.getElementById('nombreArchivoAviso');
        inputAviso.addEventListener('change', e => {
            const file = e.target.files[0];
            txtNombreAviso.textContent = file ? file.name : '';
        });

        document.getElementById('formCrearPubli').addEventListener('submit', async e => {
            e.preventDefault();
            const datos = new FormData();

            if (modoActual === 'normal') {
                const desc = document.getElementById('descripcionNormal').value.trim();
                const file = inputNormal.files[0];
                const checkReto = document.getElementById('checkParticiparReto');

                if (!file && !desc) {
                    alert('Por favor selecciona un archivo o escribe una descripción para la publicación.');
                    return;
                }

                datos.append('modo', 'normal');
                datos.append('descripcion', desc);
                if (file) datos.append('archivo', file);
                if (checkReto && checkReto.checked) {
                    datos.append('id_reto', checkReto.value);
                }

                btnSubmit.disabled = true;
                try {
                    const res = await window.drawuApi('publicacion.php', datos);
                    if (res.recompensa > 0) {
                        alert(`¡Publicación creada! Recibiste +${res.recompensa} Draws por participar en el reto del día.`);
                    }
                    window.location.href = res.tipo === 'video' ? 'videos.php' : 'usuario_propio.php';
                } catch (err) {
                    alert(err.message);
                    btnSubmit.disabled = false;
                }
            } else {
                const selectCom = document.getElementById('selectComunidadAviso');
                const idCom = selectCom.value;
                const slugCom = selectCom.selectedOptions[0]?.dataset.slug || '';
                const tit = document.getElementById('tituloAviso').value.trim();
                const msg = document.getElementById('mensajeAviso').value.trim();
                const file = inputAviso.files[0];

                if (!idCom) {
                    alert('Por favor selecciona una comunidad a la que pertenezcas.');
                    return;
                }
                if (!tit || !msg) {
                    alert('Por favor completa el título y el mensaje del aviso.');
                    return;
                }

                datos.append('modo', 'aviso');
                datos.append('id_comunidad', idCom);
                datos.append('titulo', tit);
                datos.append('mensaje', msg);
                if (file) datos.append('archivo', file);

                btnSubmit.disabled = true;
                try {
                    await window.drawuApi('publicacion.php', datos);
                    window.location.href = slugCom ? ('comunidad_ind.php?id=' + encodeURIComponent(slugCom)) : 'explorar_comunidades.php';
                } catch (err) {
                    alert(err.message);
                    btnSubmit.disabled = false;
                }
            }
        });
    </script>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
