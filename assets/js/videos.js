(() => {
    const listaVideos = Array.isArray(window.DRAWU_VIDEOS) ? window.DRAWU_VIDEOS : [];
    let indiceVideo = 0;

    const like = document.getElementById('like');
    const likesEl = document.getElementById('likes');
    const panel = document.getElementById('panelComentarios');
    const abrir = document.getElementById('abrirComentarios');
    const input = document.getElementById('textoComentario');
    const listaComentarios = document.getElementById('listaComentarios');
    const contadorComentarios = document.getElementById('contadorComentarios');
    const video = document.getElementById('video');
    const shell = document.getElementById('videoShell');
    const sound = document.getElementById('sonido');
    const btnAnterior = document.getElementById('videoAnterior');
    const btnSiguiente = document.getElementById('videoSiguiente');
    const autorLink = document.getElementById('videoAutorLink');
    const btnSeguir = document.getElementById('videoBtnSeguir');
    const descEl = document.getElementById('videoDescripcion');

    function videoActual() {
        return listaVideos[indiceVideo] || null;
    }

    function renderComentarios(comentarios) {
        listaComentarios.innerHTML = '';
        if (!comentarios || !comentarios.length) {
            listaComentarios.innerHTML = '<p class="vacio" id="sinComentarios">Todavía no hay comentarios. ¡Sé el primero!</p>';
            return;
        }
        comentarios.forEach(item => {
            const d = document.createElement('div');
            d.className = 'comentario';
            const b = document.createElement('strong');
            b.textContent = item.usuario || 'Usuario';
            const s = document.createElement('span');
            s.textContent = item.contenido || '';
            d.append(b, s);
            listaComentarios.append(d);
        });
    }

    async function cargarComentariosActual() {
        const actual = videoActual();
        if (!actual || !actual.id_publicacion) {
            renderComentarios([]);
            return;
        }
        try {
            const res = await window.drawuApi('comentarios.php', { id_publicacion: actual.id_publicacion }, 'GET');
            renderComentarios(res.comentarios);
            actual.comentarios = res.total;
            contadorComentarios.textContent = String(res.total);
        } catch {
            renderComentarios([]);
        }
    }

    function toggleComentarios(show) {
        panel.classList.toggle('abierto', show);
        panel.setAttribute('aria-hidden', String(!show));
        abrir.setAttribute('aria-expanded', String(show));
        if (show) {
            cargarComentariosActual();
            input.focus();
        }
    }

    abrir.addEventListener('click', () => toggleComentarios(!panel.classList.contains('abierto')));
    document.getElementById('cerrarComentarios').addEventListener('click', () => toggleComentarios(false));

    like.addEventListener('click', async () => {
        const actual = videoActual();
        if (!actual || !actual.id_publicacion) return;
        try {
            const res = await window.drawuApi('like.php', { id_publicacion: actual.id_publicacion });
            actual.liked = res.liked;
            actual.likes = res.total;
            like.classList.toggle('liked', res.liked);
            like.setAttribute('aria-pressed', String(res.liked));
            likesEl.textContent = String(res.total);
        } catch (err) {
            alert(err.message);
        }
    });

    if (btnSeguir) {
        btnSeguir.addEventListener('click', async () => {
            const actual = videoActual();
            if (!actual || !actual.id_usuario || actual.es_mio) return;
            try {
                const res = await window.drawuApi('seguir.php', { id_usuario: actual.id_usuario });
                listaVideos.forEach(v => {
                    if (v.id_usuario === actual.id_usuario) v.siguiendo = res.siguiendo;
                });
                btnSeguir.classList.toggle('siguiendo', res.siguiendo);
                btnSeguir.textContent = res.siguiendo ? 'Siguiendo' : 'Seguir';
            } catch (err) {
                alert(err.message);
            }
        });
    }

    document.getElementById('formComentario').addEventListener('submit', async e => {
        e.preventDefault();
        const t = input.value.trim();
        const actual = videoActual();
        if (!t || !actual || !actual.id_publicacion) return;

        try {
            const res = await window.drawuApi('comentarios.php', {
                id_publicacion: actual.id_publicacion,
                contenido: t
            });
            actual.comentarios = res.total;
            contadorComentarios.textContent = String(res.total);
            renderComentarios(res.comentarios);
            input.value = '';
        } catch (err) {
            alert(err.message);
        }
    });

    sound.addEventListener('click', () => {
        video.muted = !video.muted;
        sound.textContent = video.muted ? '🔇' : '🔊';
    });

    document.getElementById('compartir').addEventListener('click', async () => {
        try {
            if (navigator.share) await navigator.share({ title: 'Video', url: location.href });
            else if (navigator.clipboard) { await navigator.clipboard.writeText(location.href); alert('Enlace copiado.'); }
            else alert('Copia el enlace desde la barra de direcciones.');
        } catch (e) {
            if (e.name !== 'AbortError') alert('No se pudo compartir el enlace.');
        }
    });

    function mostrarVideo(i) {
        if (!listaVideos.length) {
            shell.classList.remove('has-video');
            btnAnterior.disabled = btnSiguiente.disabled = true;
            return;
        }
        indiceVideo = Math.max(0, Math.min(i, listaVideos.length - 1));
        const actual = listaVideos[indiceVideo];

        if (actual.src) {
            video.src = actual.src;
            shell.classList.add('has-video');
        } else {
            video.removeAttribute('src');
            shell.classList.remove('has-video');
        }

        if (autorLink) {
            autorLink.textContent = actual.usuario;
            autorLink.href = actual.url_perfil;
        }
        if (descEl) {
            descEl.textContent = actual.descripcion;
        }
        if (btnSeguir) {
            btnSeguir.style.display = actual.es_mio ? 'none' : '';
            btnSeguir.dataset.userId = String(actual.id_usuario);
            btnSeguir.dataset.usuario = actual.usuario;
            btnSeguir.classList.toggle('siguiendo', Boolean(actual.siguiendo));
            btnSeguir.textContent = actual.siguiendo ? 'Siguiendo' : 'Seguir';
        }

        like.classList.toggle('liked', Boolean(actual.liked));
        like.setAttribute('aria-pressed', String(Boolean(actual.liked)));
        likesEl.textContent = String(actual.likes || 0);
        contadorComentarios.textContent = String(actual.comentarios || 0);

        if (panel.classList.contains('abierto')) {
            cargarComentariosActual();
        }

        btnAnterior.disabled = indiceVideo === 0;
        btnSiguiente.disabled = indiceVideo === listaVideos.length - 1;
    }

    btnAnterior.addEventListener('click', () => mostrarVideo(indiceVideo - 1));
    btnSiguiente.addEventListener('click', () => mostrarVideo(indiceVideo + 1));
    mostrarVideo(0);
})();
