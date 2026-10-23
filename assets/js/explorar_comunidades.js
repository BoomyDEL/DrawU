(() => {
  "use strict";

  const pagina = document.querySelector(".drawu-explorar");

  if (!pagina) return;

  const tarjetas = () => [...pagina.querySelectorAll(".drawu-explorar-card")];

  const aviso = document.getElementById("avisoComunidad");
  const mensajeVacio = document.getElementById("comunidadesVacias");

  let temporizadorAviso;

  function mostrarAviso(mensaje) {
    if (!aviso) return;

    aviso.textContent = mensaje;
    aviso.classList.add("visible");

    clearTimeout(temporizadorAviso);

    temporizadorAviso = setTimeout(() => {
      aviso.classList.remove("visible");
    }, 2800);
  }

  const clave = "drawu_demo_comunidades_unidas";

  function obtenerComunidadesUnidas() {
    try {
      const datos = localStorage.getItem(clave);

      return new Set(datos ? JSON.parse(datos) : []);
    } catch {
      return new Set();
    }
  }

  function guardarComunidadesUnidas() {
    try {
      localStorage.setItem(clave, JSON.stringify([...comunidadesUnidas]));
    } catch {
      mostrarAviso("No se pudo guardar la preferencia en este navegador.");
    }
  }

  const comunidadesUnidas = obtenerComunidadesUnidas();

  function actualizarBotones() {
    tarjetas().forEach((tarjeta) => {
      const id = tarjeta.dataset.comunidad;
      const boton = tarjeta.querySelector("[data-unirse]");

      if (!boton) return;

      const unido = comunidadesUnidas.has(id);

      boton.textContent = unido
        ? "Unido a la comunidad"
        : "Unirse a la comunidad";

      boton.classList.toggle("unido", unido);

      boton.setAttribute("aria-pressed", String(unido));
    });
  }

  pagina.addEventListener("click", (evento) => {
    const boton = evento.target.closest("[data-unirse]");

    if (!boton) return;

    const tarjeta = boton.closest(".drawu-explorar-card");

    if (!tarjeta) return;

    const id = tarjeta.dataset.comunidad;

    if (comunidadesUnidas.has(id)) {
      comunidadesUnidas.delete(id);

      mostrarAviso("Saliste de la comunidad (prueba)");
    } else {
      comunidadesUnidas.add(id);

      mostrarAviso("Te uniste a la comunidad (prueba)");
    }

    guardarComunidadesUnidas();
    actualizarBotones();
  });

  pagina.addEventListener("click", (evento) => {
    const boton = evento.target.closest(".drawu-explorar-cerrar");

    if (!boton) return;

    const tarjeta = boton.closest(".drawu-explorar-card");

    if (!tarjeta) return;

    tarjeta.remove();

    mostrarAviso("Tarjeta ocultada. Recarga la página para restaurarla.");

    if (tarjetas().length === 0 && mensajeVacio) {
      mensajeVacio.hidden = false;
    }
  });

  pagina.querySelectorAll("img[data-imagen-comunidad]").forEach((imagen) => {
    function ocultarImagen() {
      imagen.hidden = true;
    }

    imagen.addEventListener("error", ocultarImagen);

    if (imagen.complete && imagen.naturalWidth === 0) {
      ocultarImagen();
    }
  });

  actualizarBotones();
})();
