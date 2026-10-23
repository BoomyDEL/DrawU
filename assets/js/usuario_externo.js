/**
 * Perfil de otro usuario (usuario_externo.php)
 * - Ver foto de perfil (sin poder cambiarla)
 * - Menú de tres puntos: seguir, bloquear, reportar
 * - Carrusel de comunidades
 * Like / comentarios / compartir / seguir están en publicaciones.js
 */
(() => {
    const USUARIO = 'Fulanito_detal';
    const menuPuntos = document.getElementById('menuPuntos');
    const opciones = document.getElementById('opcionesUsuario');

    function abrirModal(id) {
        const m = document.getElementById(id);
        m.classList.add('abierto');
        m.setAttribute('aria-hidden', 'false');
    }
    function cerrarModal(id) {
        const m = document.getElementById(id);
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
        if (!e.target.closest('.nombre-con-menu') || e.target.closest('#menuPuntos button')) mostrarMenu(false);
    });

    // --- Contador de seguidores ---
    document.addEventListener('seguir-cambio', e => {
        if (e.detail.usuario !== USUARIO) return;
        const n = e.detail.siguiendo ? 1 : 0;
        document.getElementById('contadorSeguidores').textContent = `${n} Seguidor${n === 1 ? '' : 'es'}`;
    });
    if (localStorage.getItem('siguiendo_' + USUARIO) === '1') {
        document.getElementById('contadorSeguidores').textContent = '1 Seguidor';
    }

    // --- Bloquear / desbloquear ---
    const btnBloquear = document.getElementById('bloquearUsuario');
    const claveBloqueo = 'bloqueado_' + USUARIO;
    function aplicarBloqueo(bloqueado) {
        document.getElementById('contenidoUsuario').classList.toggle('oculto', bloqueado);
        document.getElementById('avisoBloqueo').classList.toggle('oculto', !bloqueado);
        document.querySelectorAll('.boton-seguir[data-usuario="' + USUARIO + '"]')
            .forEach(b => b.classList.toggle('oculto', bloqueado));
        btnBloquear.textContent = bloqueado ? 'Desbloquear usuario' : 'Bloquear usuario';
        btnBloquear.classList.toggle('peligro', !bloqueado);
    }
    btnBloquear.addEventListener('click', () => {
        const bloqueado = localStorage.getItem(claveBloqueo) === '1';
        if (!bloqueado) {
            if (!confirm(`¿Seguro que quieres bloquear a ${USUARIO}? Dejarás de seguirlo y no verás sus publicaciones.`)) return;
            if (localStorage.getItem('siguiendo_' + USUARIO) === '1') window.toggleSeguir(USUARIO);
        }
        localStorage.setItem(claveBloqueo, bloqueado ? '0' : '1');
        aplicarBloqueo(!bloqueado);
    });
    aplicarBloqueo(localStorage.getItem(claveBloqueo) === '1');

    // --- Reportar ---
    document.getElementById('reportarUsuario').addEventListener('click', () => abrirModal('modalReporte'));
    document.getElementById('formReporte').addEventListener('submit', e => {
        e.preventDefault();
        // TODO: enviar el reporte al backend
        alert('Gracias. Revisaremos tu reporte.');
        e.target.reset();
        cerrarModal('modalReporte');
    });

    // --- Ver foto de perfil (solo visualizar) ---
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
