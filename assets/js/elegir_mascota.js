// Carrusel de mascotas conectado a la base de datos
const mascotas = (window.DRAWU && Array.isArray(window.DRAWU.mascotasIniciales) && window.DRAWU.mascotasIniciales.length)
    ? window.DRAWU.mascotasIniciales
    : [
        { id: 1, nombre: 'Dylan', estilo: 'ESTILO MORADO', tema: '', globalClass: '', img: '../assets/img/personajes/dylan.png' },
        { id: 2, nombre: 'Tommy', estilo: 'ESTILO AQUA', tema: 'tema-aqua', globalClass: 'tema-pet-aqua', img: '../assets/img/personajes/tommy.png' },
        { id: 3, nombre: 'Boomy', estilo: 'ESTILO VERDE', tema: 'tema-verde', globalClass: 'tema-pet-verde', img: '../assets/img/personajes/boomy.png' }
    ];

let actual = 0;
const page = document.querySelector('.page');
const petImg = document.getElementById('petImg');
const statusEl = document.getElementById('status');
const applyBtn = document.getElementById('applyButton');

function mostrarMascota(i) {
    actual = (i + mascotas.length) % mascotas.length;
    const m = mascotas[actual];
    document.getElementById('petName').textContent = m.nombre;
    document.getElementById('petNameOutline').textContent = m.nombre;

    if (petImg) {
        petImg.src = m.img;
        petImg.alt = m.nombre;
    }

    document.getElementById('petDescription').innerHTML = '<span>' + m.estilo + '</span>';
    page.classList.remove('tema-aqua', 'tema-verde');
    if (m.tema) page.classList.add(m.tema);
    if (statusEl) statusEl.textContent = '';
}

document.getElementById('previous').addEventListener('click', () => mostrarMascota(actual - 1));
document.getElementById('next').addEventListener('click', () => mostrarMascota(actual + 1));

applyBtn.addEventListener('click', async () => {
    const selected = mascotas[actual];
    applyBtn.disabled = true;

    try {
        await window.drawuApi('mascota.php', {
            accion: 'elegir',
            id_mascota: selected.id
        });
        window.location.href = 'inicio.php';
    } catch (err) {
        if (statusEl) statusEl.textContent = err.message;
        else alert(err.message);
        applyBtn.disabled = false;
    }
});
