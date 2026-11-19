/**
 * Perfil de otro usuario (usuario_externo.php) conectado a la base de datos.
 */
(() => {
    const ID_USUARIO = document.body.dataset.externalUserId;
    const USUARIO = document.body.dataset.externalUsername || 'Usuario';
    let bloqueadoActual = document.body.dataset.isBlocked === '1';

    const menuPuntos = document.getElementById('menuPuntos');
    const opciones = document.getElementById('opcionesUsuario');

    function abrirModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.add('abierto');
        m.setAttribute('aria-hidden', 'false');
    }
    function cerrarModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.remove('abierto');
        m.setAttribute('aria-hidden', 'true');
    }
    function mostrarMenu(mostrar) {
        menuPuntos.classList.toggle('oculto', !mostrar);
        opciones.setAttribute('aria-expanded', String(mostrar));
    }

    // --- Menú de tres puntos ---
    opciones.addEventListener('click', e => {
        e.stopPropagation();
        mostrarMenu(menuPuntos.classList.contains('oculto'));
    });
    document.addEventListener('click', e => {
        if (!e.target.closest('.nombre-con-menu') || e.target.closest('#menuPuntos button')) {
            mostrarMenu(false);
        }
    });

    // --- Contador de seguidores ---
    function actualizarTextoSeguidores(n) {
        const el = document.getElementById('contadorSeguidores');
        if (el && typeof n === 'number') {
            el.textContent = `${n} Seguidor${n === 1 ? '' : 'es'}`;
        }
    }

    document.addEventListener('seguir-cambio', e => {
        if (String(e.detail.idUsuario) !== String(ID_USUARIO)) return;
        actualizarTextoSeguidores(e.detail.seguidores);
    });

    // --- Bloquear / desbloquear en BD ---
    const btnBloquear = document.getElementById('bloquearUsuario');
    function aplicarBloqueo(bloqueado) {
        bloqueadoActual = bloqueado;
        document.body.dataset.isBlocked = bloqueado ? '1' : '0';
        document.getElementById('contenidoUsuario').classList.toggle('oculto', bloqueado);
        document.getElementById('avisoBloqueo').classList.toggle('oculto', !bloqueado);
        document.querySelectorAll(`.boton-seguir[data-user-id="${ID_USUARIO}"]`).forEach(b => {
            b.classList.toggle('oculto', bloqueado);
            if (bloqueado) {
                b.classList.remove('siguiendo');
                b.textContent = 'Seguir';
                b.setAttribute('aria-pressed', 'false');
            }
        });
        btnBloquear.textContent = bloqueado ? 'Desbloquear usuario' : 'Bloquear usuario';
        btnBloquear.classList.toggle('peligro', !bloqueado);
    }

    btnBloquear.addEventListener('click', async () => {
        if (!bloqueadoActual) {
            if (!confirm(`¿Seguro que quieres bloquear a ${USUARIO}? Dejarás de seguirlo y no verás sus publicaciones.`)) return;
        }
        try {
            const res = await window.drawuApi('bloqueo.php', { id_usuario: ID_USUARIO });
            aplicarBloqueo(Boolean(res.bloqueado));
            actualizarTextoSeguidores(res.seguidores);
            if (!res.bloqueado) {
                window.location.reload();
            }
        } catch (err) {
            alert(err.message);
        }
    });

    // --- Reportar en BD ---
    document.getElementById('reportarUsuario').addEventListener('click', () => abrirModal('modalReporte'));
    document.getElementById('formReporte').addEventListener('submit', async e => {
        e.preventDefault();
        const motivoEl = e.target.querySelector('input[name="motivo"]:checked');
        const detalle = document.getElementById('detalleReporte').value.trim();
        if (!motivoEl) return;

        try {
            await window.drawuApi('reporte.php', {
                id_usuario: ID_USUARIO,
                motivo: motivoEl.value,
                detalle
            });
            alert('Gracias. Revisaremos tu reporte.');
            e.target.reset();
            cerrarModal('modalReporte');
        } catch (err) {
            alert(err.message);
        }
    });

    // --- Ver foto de perfil ---
    const fotoPerfil = document.getElementById('fotoPerfil');
    fotoPerfil.addEventListener('click', () => {
        const visor = document.getElementById('visorContenido');
        visor.innerHTML = '';
        const img = fotoPerfil.querySelector('img');
        if (img) visor.appendChild(img.cloneNode());
        else visor.textContent = 'Sin foto de perfil';
        abrirModal('modalFoto');
    });

    // --- Carrusel de comunidades ---
    const pista = document.getElementById('pistaCarrusel');
    if (pista) {
        let indice = 0;
        const visibles = () => window.innerWidth <= 390 ? 1 : window.innerWidth <= 650 ? 2 : 3;
        const maximo = () => Math.max(0, pista.children.length - visibles());
        function mover() {
            const primera = pista.querySelector('.comunidad');
            if (!primera) return;
            const espacio = parseFloat(getComputedStyle(pista).gap) || 0;
            indice = Math.max(0, Math.min(indice, maximo()));
            pista.style.transform = `translateX(-${indice * (primera.getBoundingClientRect().width + espacio)}px)`;
        }
        document.getElementById('anterior').addEventListener('click', () => { indice = indice <= 0 ? maximo() : indice - 1; mover(); });
        document.getElementById('siguiente').addEventListener('click', () => { indice = indice >= maximo() ? 0 : indice + 1; mover(); });
        window.addEventListener('resize', mover);
        mover();
    }
})();
