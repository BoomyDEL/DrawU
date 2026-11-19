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

    const btnCrearComunidad = document.getElementById('btnAbrirCrearComunidad');
    if (btnCrearComunidad) {
        btnCrearComunidad.addEventListener('click', () => abrirModal('modalCrearComunidad'));
    }

    const inputFotoCom = document.getElementById('fotoComunidadInput');
    const inputBannerCom = document.getElementById('bannerComunidadInput');
    if (inputFotoCom) {
        inputFotoCom.addEventListener('change', e => {
            const nom = document.getElementById('nombreFotoComunidad');
            if (nom) nom.textContent = e.target.files[0]?.name || '';
        });
    }
    if (inputBannerCom) {
        inputBannerCom.addEventListener('change', e => {
            const nom = document.getElementById('nombreBannerComunidad');
            if (nom) nom.textContent = e.target.files[0]?.name || '';
        });
    }

    // --- Crear comunidad (se guarda en la base de datos) ---
    const formComunidad = document.getElementById('formCrearComunidad');
    if (formComunidad) {
        formComunidad.addEventListener('submit', async e => {
            e.preventDefault();
            const boton = formComunidad.querySelector('[type="submit"]');
            const datos = new FormData();
            datos.append('accion', 'crear');
            datos.append('nombre', document.getElementById('nombreComunidad')?.value.trim() || '');
            datos.append('privacidad', document.getElementById('privacidadComunidad')?.value || '');
            if (inputFotoCom?.files[0]) datos.append('foto', inputFotoCom.files[0]);
            if (inputBannerCom?.files[0]) datos.append('banner', inputBannerCom.files[0]);

            boton.disabled = true;
            try {
                const res = await window.drawuApi('comunidad.php', datos);
                window.location.href = (window.DRAWU?.base || '../') + 'views/' + res.url;
            } catch (error) {
                alert(error.message);
                boton.disabled = false;
            }
        });
    }

    const btnCrearPubli = document.getElementById('abrirCrearPubli');
    if (btnCrearPubli) {
        btnCrearPubli.addEventListener('click', () => abrirModal('modalCrearPubli'));
    }
    const inputArchPubli = document.getElementById('archivoPubli');
    if (inputArchPubli) {
        inputArchPubli.addEventListener('change', e => {
            const nomArch = document.getElementById('nombreArchivo');
            if (nomArch) nomArch.textContent = e.target.files[0]?.name || '';
        });
    }

    // --- Ayuda y soporte técnico (se guarda en la base de datos) ---
    document.getElementById('abrirSoporte').addEventListener('click', () => abrirModal('modalSoporte'));
    document.getElementById('formSoporte').addEventListener('submit', async e => {
        e.preventDefault();
        const mensaje = document.getElementById('mensajeSoporte').value.trim();
        try {
            await window.drawuApi('soporte.php', { mensaje });
            alert('¡Gracias! Tu mensaje fue enviado al equipo de soporte.');
            e.target.reset();
            cerrarModal(document.getElementById('modalSoporte'));
        } catch (error) {
            alert(error.message);
        }
    });

    // --- Modo claro / oscuro (se guarda en localStorage y en el perfil del usuario) ---
    const btnTema = document.getElementById('toggleTema');
    function aplicarTema(oscuro) {
        document.body.classList.toggle('oscuro', oscuro);
        btnTema.textContent = oscuro ? 'Modo claro' : 'Modo oscuro';
    }
    const temaInicialOscuro = document.body.classList.contains('oscuro') ||
        localStorage.getItem('tema') === 'oscuro' ||
        (window.DRAWU?.tema === 'oscuro');
    aplicarTema(temaInicialOscuro);

    btnTema.addEventListener('click', async () => {
        const oscuro = !document.body.classList.contains('oscuro');
        const nuevoTema = oscuro ? 'oscuro' : 'claro';
        localStorage.setItem('tema', nuevoTema);
        if (window.DRAWU) window.DRAWU.tema = nuevoTema;
        aplicarTema(oscuro);
        try {
            await window.drawuApi('perfil.php', { accion: 'tema', tema: nuevoTema });
        } catch {
            // El cambio visual y en localStorage se conserva
        }
    });
})();
