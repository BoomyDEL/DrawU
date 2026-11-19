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

  pagina.addEventListener("click", async (evento) => {
    const boton = evento.target.closest("[data-unirse]");
    if (!boton) return;

    const tarjeta = boton.closest(".drawu-explorar-card");
    if (!tarjeta) return;

    const idComunidad = tarjeta.dataset.comunidadId;
    if (!idComunidad) return;

    boton.disabled = true;
    try {
      const res = await window.drawuApi("comunidad.php", {
        accion: "unirse",
        id_comunidad: idComunidad,
      });
      boton.textContent = res.es_miembro ? "Unido a la comunidad" : "Unirse a la comunidad";
      boton.classList.toggle("unido", res.es_miembro);
      boton.setAttribute("aria-pressed", String(res.es_miembro));

      const contador = tarjeta.querySelector("[data-miembros-count]");
      if (contador && typeof res.miembros === "number") {
        contador.textContent = `${res.miembros} ${res.miembros === 1 ? "miembro" : "miembros"}`;
      }

      mostrarAviso(res.es_miembro ? "Te uniste a la comunidad" : "Saliste de la comunidad");
    } catch (err) {
      mostrarAviso(err.message);
    } finally {
      boton.disabled = false;
    }
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
})();
