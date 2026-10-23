// Carrusel de mascotas
const mascotas = [
    { 
        id: 1,
        nombre: 'Dylan', 
        estilo: 'ESTILO MORADO',   
        tema: '', 
        globalClass: '',
        img: '../assets/img/personajes/dylan.png' 
    },
    { 
        id: 2,
        nombre: 'Tommy', 
        estilo: 'ESTILO AQUA',     
        tema: 'tema-aqua', 
        globalClass: 'tema-pet-aqua',
        img: '../assets/img/personajes/tommy.png' 
    },
    { 
        id: 3,
        nombre: 'Boomy', 
        estilo: 'ESTILO VERDE',    
        tema: 'tema-verde', 
        globalClass: 'tema-pet-verde',
        img: '../assets/img/personajes/boomy.png' 
    }
];

let actual = 0;
const page = document.querySelector('.page');
const petImg = document.getElementById('petImg');

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
    document.getElementById('status').textContent = '';
}

document.getElementById('previous').addEventListener('click', () => mostrarMascota(actual - 1));
document.getElementById('next').addEventListener('click', () => mostrarMascota(actual + 1));
document.getElementById('applyButton').addEventListener('click', () => {
    const selected = mascotas[actual];
    // Guardar la mascota elegida en localStorage
    localStorage.setItem('drawu_active_pet', selected.id.toString());
    
    // Aplicar clase global
    document.body.classList.remove('tema-pet-aqua', 'tema-pet-verde');
    document.documentElement.classList.remove('tema-pet-aqua', 'tema-pet-verde');
    if (selected.globalClass) {
        document.body.classList.add(selected.globalClass);
        document.documentElement.classList.add(selected.globalClass);
    }
    
    // Ir a la página de inicio
    window.location.href = 'inicio.php';
});
