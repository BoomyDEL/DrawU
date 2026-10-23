const inputF = document.getElementById('fotoComunidadPagInput');
        const inputB = document.getElementById('bannerComunidadPagInput');
        if (inputF) {
            inputF.addEventListener('change', e => {
                document.getElementById('nombreFotoComunidadPag').textContent = e.target.files[0]?.name || '';
            });
        }
        if (inputB) {
            inputB.addEventListener('change', e => {
                document.getElementById('nombreBannerComunidadPag').textContent = e.target.files[0]?.name || '';
            });
        }
        document.getElementById('formPaginaComunidad').addEventListener('submit', e => {
            e.preventDefault();
            const nom = document.getElementById('nombreComunidadPag').value.trim();
            const priv = document.getElementById('privacidadComunidadPag').value;
            alert(`¡Comunidad "${nom}" (${priv}) creada exitosamente!`);
            window.location.href = 'explorar_comunidades.php';
        });