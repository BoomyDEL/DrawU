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

document.getElementById('formPaginaComunidad').addEventListener('submit', async e => {
    e.preventDefault();
    const boton = e.target.querySelector('[type="submit"]');
    const datos = new FormData();
    datos.append('accion', 'crear');
    datos.append('nombre', document.getElementById('nombreComunidadPag').value.trim());
    datos.append('privacidad', document.getElementById('privacidadComunidadPag').value);
    if (inputF?.files[0]) datos.append('foto', inputF.files[0]);
    if (inputB?.files[0]) datos.append('banner', inputB.files[0]);

    boton.disabled = true;
    try {
        const res = await window.drawuApi('comunidad.php', datos);
        window.location.href = res.url || 'explorar_comunidades.php';
    } catch (err) {
        alert(err.message);
        boton.disabled = false;
    }
});
