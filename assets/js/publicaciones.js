/**
 * Funcionalidad compartida de publicaciones:
 * like, comentarios, compartir y seguir/dejar de seguir.
 * Usado en usuario_propio.php y usuario_externo.php.
 */
(() => {
    // --- Like ---
    document.querySelectorAll('.accion-like').forEach(btn => {
        btn.addEventListener('click', () => {
            const liked = btn.classList.toggle('liked');
            const contador = btn.querySelector('.contador');
            contador.textContent = Math.max(0, Number(contador.textContent) + (liked ? 1 : -1));
            btn.setAttribute('aria-pressed', String(liked));
        });
    });

    // --- Comentarios (un panel compartido, comentarios guardados por publicación) ---
    const panel = document.getElementById('panelComentarios');
    const lista = document.getElementById('listaComentarios');
    const input = document.getElementById('textoComentario');
    const comentariosPorPubli = new Map();
    let publiActual = null;

    function renderComentarios() {
        lista.innerHTML = '';
        const comentarios = comentariosPorPubli.get(publiActual) || [];
        if (!comentarios.length) {
            lista.innerHTML = '<p class="vacio">Todavía no hay comentarios. ¡Sé el primero!</p>';
            return;
        }
        comentarios.forEach(t => {
            const d = document.createElement('div');
            d.className = 'comentario';
            const b = document.createElement('strong');
            b.textContent = 'Tú';
            const s = document.createElement('span');
            s.textContent = t;
            d.append(b, s);
            lista.append(d);
        });
    }

    function togglePanel(show) {
        panel.classList.toggle('abierto', show);
        panel.setAttribute('aria-hidden', String(!show));
        if (show) input.focus();
    }

    document.querySelectorAll('.accion-comentar').forEach(btn => {
        btn.addEventListener('click', () => {
            const publi = btn.closest('.publicacion');
            if (panel.classList.contains('abierto') && publiActual === publi) return togglePanel(false);
            publiActual = publi;
            renderComentarios();
            togglePanel(true);
        });
    });
    document.getElementById('cerrarComentarios').addEventListener('click', () => togglePanel(false));

    document.getElementById('formComentario').addEventListener('submit', e => {
        e.preventDefault();
        const t = input.value.trim();
        if (!t || !publiActual) return;
        const arr = comentariosPorPubli.get(publiActual) || [];
        arr.push(t);
        comentariosPorPubli.set(publiActual, arr);
        publiActual.querySelector('.accion-comentar .contador').textContent = arr.length;
        input.value = '';
        renderComentarios();
    });

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

    // --- Seguir / Dejar de seguir (sincroniza todos los botones del mismo usuario) ---
    function aplicarSeguir(usuario, siguiendo) {
        document.querySelectorAll(`.boton-seguir[data-usuario="${usuario}"]`).forEach(b => {
            b.classList.toggle('siguiendo', siguiendo);
            b.textContent = siguiendo ? 'Dejar de seguir' : 'Seguir';
            b.setAttribute('aria-pressed', String(siguiendo));
        });
        document.dispatchEvent(new CustomEvent('seguir-cambio', { detail: { usuario, siguiendo } }));
    }
    window.toggleSeguir = usuario => {
        const clave = 'siguiendo_' + usuario;
        const siguiendo = localStorage.getItem(clave) !== '1';
        localStorage.setItem(clave, siguiendo ? '1' : '0');
        aplicarSeguir(usuario, siguiendo);
    };
    const usuarios = new Set([...document.querySelectorAll('.boton-seguir[data-usuario]')].map(b => b.dataset.usuario));
    usuarios.forEach(u => aplicarSeguir(u, localStorage.getItem('siguiendo_' + u) === '1'));
    document.querySelectorAll('.boton-seguir[data-usuario]').forEach(b =>
        b.addEventListener('click', () => window.toggleSeguir(b.dataset.usuario)));
})();
