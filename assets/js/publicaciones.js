/**
 * Funcionalidad compartida de publicaciones conectada a la BD:
 * like, comentarios, compartir y seguir/dejar de seguir.
 * Usado en usuario_propio.php y usuario_externo.php.
 */
(() => {
    // --- Like ---
    document.querySelectorAll('.accion-like').forEach(btn => {
        btn.addEventListener('click', async () => {
            const publi = btn.closest('.publicacion');
            const idPubli = publi?.dataset.publicacionId;
            if (!idPubli) return;

            try {
                const res = await window.drawuApi('like.php', { id_publicacion: idPubli });
                btn.classList.toggle('liked', res.liked);
                btn.setAttribute('aria-pressed', String(res.liked));
                const contador = btn.querySelector('.contador');
                if (contador) contador.textContent = String(res.total);
            } catch (err) {
                alert(err.message);
            }
        });
    });

    // --- Comentarios ---
    const panel = document.getElementById('panelComentarios');
    const lista = document.getElementById('listaComentarios');
    const input = document.getElementById('textoComentario');
    let publiActual = null;

    function renderComentarios(comentarios) {
        lista.innerHTML = '';
        if (!comentarios || !comentarios.length) {
            lista.innerHTML = '<p class="vacio">Todavía no hay comentarios. ¡Sé el primero!</p>';
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
            lista.append(d);
        });
    }

    function togglePanel(show) {
        if (!panel) return;
        panel.classList.toggle('abierto', show);
        panel.setAttribute('aria-hidden', String(!show));
        if (show && input) input.focus();
    }

    document.querySelectorAll('.accion-comentar').forEach(btn => {
        btn.addEventListener('click', async () => {
            const publi = btn.closest('.publicacion');
            if (!publi) return;
            if (panel.classList.contains('abierto') && publiActual === publi) {
                return togglePanel(false);
            }
            publiActual = publi;
            const idPubli = publi.dataset.publicacionId;
            lista.innerHTML = '';
            togglePanel(true);

            if (!idPubli) return;
            try {
                const res = await window.drawuApi('comentarios.php', { id_publicacion: idPubli }, 'GET');
                if (publiActual === publi) {
                    renderComentarios(res.comentarios);
                }
            } catch (err) {
                alert(err.message);
            }
        });
    });

    const btnCerrar = document.getElementById('cerrarComentarios');
    if (btnCerrar) {
        btnCerrar.addEventListener('click', () => togglePanel(false));
    }

    const formCom = document.getElementById('formComentario');
    if (formCom) {
        formCom.addEventListener('submit', async e => {
            e.preventDefault();
            const t = input.value.trim();
            const idPubli = publiActual?.dataset.publicacionId;
            if (!t || !idPubli) return;

            try {
                const res = await window.drawuApi('comentarios.php', {
                    id_publicacion: idPubli,
                    contenido: t
                });
                const contador = publiActual.querySelector('.accion-comentar .contador');
                if (contador) contador.textContent = String(res.total);
                input.value = '';
                renderComentarios(res.comentarios);
            } catch (err) {
                alert(err.message);
            }
        });
    }

    // --- Compartir ---
    document.querySelectorAll('.accion-compartir').forEach(btn => {
        btn.addEventListener('click', async () => {
            try {
                if (navigator.share) await navigator.share({ title: 'Publicación', url: location.href });
                else if (navigator.clipboard) { await navigator.clipboard.writeText(location.href); alert('Enlace copiado.'); }
                else alert('Copia el enlace desde la barra de direcciones.');
            } catch (e) {
                if (e.name !== 'AbortError') alert('No se pudo compartir el enlace.');
            }
        });
    });

    // --- Seguir / Dejar de seguir en BD ---
    function aplicarSeguir(idUsuario, usuario, siguiendo, seguidores) {
        document.querySelectorAll(`.boton-seguir[data-user-id="${idUsuario}"]`).forEach(b => {
            b.classList.toggle('siguiendo', siguiendo);
            b.textContent = siguiendo ? 'Dejar de seguir' : 'Seguir';
            b.setAttribute('aria-pressed', String(siguiendo));
        });
        document.dispatchEvent(new CustomEvent('seguir-cambio', {
            detail: { idUsuario, usuario, siguiendo, seguidores }
        }));
    }

    window.toggleSeguir = async (idUsuario, usuario = '') => {
        if (!idUsuario) return;
        try {
            const res = await window.drawuApi('seguir.php', { id_usuario: idUsuario });
            aplicarSeguir(idUsuario, usuario, res.siguiendo, res.seguidores);
        } catch (err) {
            alert(err.message);
        }
    };

    document.querySelectorAll('.boton-seguir[data-user-id]').forEach(b => {
        b.addEventListener('click', () => window.toggleSeguir(b.dataset.userId, b.dataset.usuario || ''));
    });
})();
