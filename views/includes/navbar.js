(() => {
    // --- Menús desplegables (comunidad / configuración) ---
    const botonesMenu = document.querySelectorAll('.sidebar [data-menu]');
    function cerrarMenus(excepto) {
        botonesMenu.forEach(b => {
            if (b === excepto) return;
            document.getElementById(b.dataset.menu).classList.remove('abierto');
            b.setAttribute('aria-expanded', 'false');
        });
    }
    botonesMenu.forEach(b => b.addEventListener('click', e => {
        e.stopPropagation();
        const menu = document.getElementById(b.dataset.menu);
        cerrarMenus(b);
        const abierto = menu.classList.toggle('abierto');
        b.setAttribute('aria-expanded', String(abierto));
    }));
    document.addEventListener('click', e => { if (!e.target.closest('.side-menu')) cerrarMenus(); });

    // --- Modales ---
    function abrirModal(id) { cerrarMenus(); const m = document.getElementById(id); m.classList.add('abierto'); m.setAttribute('aria-hidden', 'false'); }
    function cerrarModal(m) { m.classList.remove('abierto'); m.setAttribute('aria-hidden', 'true'); }
    document.querySelectorAll('.overlay').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m || e.target.closest('[data-cerrar]')) cerrarModal(m); });
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { document.querySelectorAll('.overlay.abierto').forEach(cerrarModal); cerrarMenus(); }
    });

    document.getElementById('abrirCrearPubli').addEventListener('click', () => abrirModal('modalCrearPubli'));
    document.getElementById('archivoPubli').addEventListener('change', e => {
        document.getElementById('nombreArchivo').textContent = e.target.files[0]?.name || '';
    });

    document.getElementById('abrirSoporte').addEventListener('click', () => abrirModal('modalSoporte'));
    document.getElementById('formSoporte').addEventListener('submit', e => {
        e.preventDefault();
        // TODO: enviar el mensaje al backend
        alert('¡Gracias! Tu mensaje fue enviado al equipo de soporte.');
        e.target.reset();
        cerrarModal(document.getElementById('modalSoporte'));
    });

    // --- Modo claro / oscuro (se guarda en localStorage) ---
    const btnTema = document.getElementById('toggleTema');
    function aplicarTema(oscuro) {
        document.body.classList.toggle('oscuro', oscuro);
        btnTema.textContent = oscuro ? 'Modo claro' : 'Modo oscuro';
    }
    aplicarTema(localStorage.getItem('tema') === 'oscuro');
    btnTema.addEventListener('click', () => {
        const oscuro = !document.body.classList.contains('oscuro');
        localStorage.setItem('tema', oscuro ? 'oscuro' : 'claro');
        aplicarTema(oscuro);
    });
})();
