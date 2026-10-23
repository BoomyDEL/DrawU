
const menuFoto = document.getElementById("menuFoto");
const menuEdicion = document.getElementById("menuEdicion");
const menuPuntos = document.getElementById("menuPuntos");

const fotoPerfil = document.getElementById("fotoPerfil");
const opcionesUsuario = document.getElementById("opcionesUsuario");
const archivoFoto = document.getElementById("archivoFoto");

// Mostrar y ocultar los menús
function mostrarMenu(menu, mostrar) {
    menu.classList.toggle("oculto", !mostrar);
}

// Menú de la foto de perfil
fotoPerfil.addEventListener("click", () => {
    const mostrar = menuFoto.classList.contains("oculto");

    mostrarMenu(menuFoto, mostrar);
    mostrarMenu(menuEdicion, false);
    mostrarMenu(menuPuntos, false);

    fotoPerfil.setAttribute("aria-expanded", String(mostrar));
    opcionesUsuario.setAttribute("aria-expanded", "false");
});

// Menú de tres puntos
opcionesUsuario.addEventListener("click", () => {
    const mostrar = menuPuntos.classList.contains("oculto");

    mostrarMenu(menuPuntos, mostrar);
    mostrarMenu(menuFoto, false);
    mostrarMenu(menuEdicion, false);

    opcionesUsuario.setAttribute("aria-expanded", String(mostrar));
    fotoPerfil.setAttribute("aria-expanded", "false");
});

// Ver foto del perfil
document.getElementById("verFoto").addEventListener("click", () => {
    const imagen = fotoPerfil.querySelector("img");

    if (imagen) {
        window.open(imagen.src, "_blank");
    } else {
        alert("Todavía no has elegido una foto de perfil.");
    }

    mostrarMenu(menuFoto, false);
    fotoPerfil.setAttribute("aria-expanded", "false");
});

// Elegir una nueva foto
document.getElementById("elegirFoto").addEventListener("click", () => {
    archivoFoto.click();
});

archivoFoto.addEventListener("change", (evento) => {
    const archivo = evento.target.files[0];

    if (!archivo) return;

    if (!archivo.type.startsWith("image/")) {
        alert("Selecciona un archivo de imagen válido.");
        return;
    }

    const url = URL.createObjectURL(archivo);

    fotoPerfil.innerHTML = "";

    const imagen = document.createElement("img");
    imagen.src = url;
    imagen.alt = "Foto de perfil";

    fotoPerfil.appendChild(imagen);

    mostrarMenu(menuFoto, false);
    fotoPerfil.setAttribute("aria-expanded", "false");
});

// Editar el nombre
function editarNombre() {
    const nombreActual =
        document.getElementById("nombreUsuario").textContent;

    const nuevoNombre = prompt(
        "Escribe el nombre de usuario:",
        nombreActual
    );

    if (nuevoNombre && nuevoNombre.trim()) {
        const nombre = nuevoNombre.trim();

        document.getElementById("nombreUsuario").textContent = nombre;

        document.getElementById("nombreHandle").textContent =
            "@" + nombre.replace(/\s+/g, "").toLowerCase();
    }
}

// Editar la descripción
function editarDescripcion() {
    const descripcion = document.getElementById("descripcionPerfil");

    const nuevaDescripcion = prompt(
        "Escribe una breve descripción:",
        descripcion.textContent
    );

    if (nuevaDescripcion !== null) {
        descripcion.textContent =
            nuevaDescripcion.trim() || "Sin descripción";
    }
}

document.getElementById("editarNombre")
    .addEventListener("click", editarNombre);

document.getElementById("puntosEditarNombre")
    .addEventListener("click", editarNombre);

document.getElementById("editarDescripcion")
    .addEventListener("click", editarDescripcion);

document.getElementById("puntosEditarDescripcion")
    .addEventListener("click", editarDescripcion);

// Cerrar los menús al hacer clic fuera de ellos
document.addEventListener("click", (evento) => {
    if (
        !evento.target.closest(".menu-foto") &&
        !evento.target.closest(".contenedor-foto")
    ) {
        mostrarMenu(menuFoto, false);
        fotoPerfil.setAttribute("aria-expanded", "false");
    }

    if (!evento.target.closest(".nombre-con-menu")) {
        mostrarMenu(menuPuntos, false);
        opcionesUsuario.setAttribute("aria-expanded", "false");
    }
});

// Carrusel de comunidades
const pista = document.getElementById("pistaCarrusel");
let indice = 0;

function comunidadesVisibles() {
    if (window.innerWidth <= 390) return 1;
    if (window.innerWidth <= 650) return 2;
    return 3;
}

function indiceMaximo() {
    return Math.max(
        0,
        pista.children.length - comunidadesVisibles()
    );
}

function moverCarrusel() {
    const primera = pista.querySelector(".comunidad");

    if (!primera) return;

    const espacio =
        parseFloat(getComputedStyle(pista).gap) || 0;

    const ancho = primera.getBoundingClientRect().width;

    indice = Math.max(0, Math.min(indice, indiceMaximo()));

    pista.style.transform =
        `translateX(-${indice * (ancho + espacio)}px)`;
}

document.getElementById("anterior").addEventListener("click", () => {
    indice = indice <= 0 ? indiceMaximo() : indice - 1;
    moverCarrusel();
});

document.getElementById("siguiente").addEventListener("click", () => {
    indice = indice >= indiceMaximo() ? 0 : indice + 1;
    moverCarrusel();
});

window.addEventListener("resize", moverCarrusel);

// Los enlaces de ejemplo deben apuntar a las páginas reales
// de las comunidades de tu proyecto.

moverCarrusel();
