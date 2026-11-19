/**
 * main.js - Sincronización global de monedas y temas desde la BD + lógica de inicio.php
 */
(() => {
  "use strict";

  function syncGlobalPetTheme() {
    const temaMascota = window.DRAWU?.temaMascota || "";
    document.body.classList.remove("tema-pet-aqua", "tema-pet-verde");
    document.documentElement.classList.remove("tema-pet-aqua", "tema-pet-verde");

    if (temaMascota) {
      document.body.classList.add(temaMascota);
      document.documentElement.classList.add(temaMascota);
    }
  }

  function syncGlobalCoins() {
    const coinsCountEl = document.querySelector(".header-coins-count");
    if (coinsCountEl && window.DRAWU && typeof window.DRAWU.drawings === "number") {
      coinsCountEl.textContent = String(window.DRAWU.drawings);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      syncGlobalPetTheme();
      syncGlobalCoins();
    });
  } else {
    syncGlobalPetTheme();
    syncGlobalCoins();
  }
})();

(() => {
  "use strict";
  const inicio = document.querySelector(".drawu-home");
  if (!inicio) return;

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

  $$("img[data-asset-opcional]", inicio).forEach((img) => {
    function ocultarImagen() {
      img.style.display = "none";
    }
    img.addEventListener("error", ocultarImagen);
    if (img.complete && img.naturalWidth === 0) {
      ocultarImagen();
    }
  });

  const aviso = $("#drawuAviso");
  let temporizadorAviso;

  function mostrarAviso(mensaje) {
    if (!aviso) return;
    aviso.textContent = mensaje;
    aviso.classList.add("visible");
    clearTimeout(temporizadorAviso);
    temporizadorAviso = setTimeout(() => {
      aviso.classList.remove("visible");
    }, 2600);
  }

  const pista = $("#pistaComunidades");
  const anterior = $("#comunidadesAnterior");
  const siguiente = $("#comunidadesSiguiente");

  if (pista && anterior && siguiente) {
    function anchoTarjeta() {
      const tarjeta = pista.querySelector(".drawu-community-card");
      if (!tarjeta) return 220;
      const estilos = getComputedStyle(pista);
      const espacio = parseFloat(estilos.gap) || 0;
      return tarjeta.getBoundingClientRect().width + espacio;
    }

    function actualizarFlechas() {
      const maximo = pista.scrollWidth - pista.clientWidth;
      anterior.disabled = pista.scrollLeft <= 5;
      siguiente.disabled = maximo <= 5 || pista.scrollLeft >= maximo - 5;
    }

    anterior.addEventListener("click", () => {
      pista.scrollBy({ left: -anchoTarjeta(), behavior: "smooth" });
    });

    siguiente.addEventListener("click", () => {
      pista.scrollBy({ left: anchoTarjeta(), behavior: "smooth" });
    });

    pista.addEventListener("scroll", actualizarFlechas, { passive: true });
    window.addEventListener("resize", actualizarFlechas);
    actualizarFlechas();
  }

  // --- Seguir usuario en BD ---
  $$(".drawu-follow", inicio).forEach((boton) => {
    boton.addEventListener("click", async () => {
      const idUsuario = boton.dataset.userId;
      const nombreUsuario = boton.dataset.followUser || "usuario";
      if (!idUsuario) return;

      try {
        const res = await window.drawuApi("seguir.php", { id_usuario: idUsuario });
        $$(`.drawu-follow[data-user-id="${idUsuario}"]`, inicio).forEach((b) => {
          b.textContent = res.siguiendo ? "Siguiendo" : "Seguir";
          b.classList.toggle("siguiendo", res.siguiendo);
          b.setAttribute("aria-pressed", String(res.siguiendo));
        });
        mostrarAviso(
          res.siguiendo
            ? `Ahora sigues a ${nombreUsuario}`
            : `Dejaste de seguir a ${nombreUsuario}`
        );
      } catch (err) {
        mostrarAviso(err.message);
      }
    });
  });

  // --- Likes y compartir ---
  $$(".drawu-post", inicio).forEach((publicacion) => {
    const id = publicacion.dataset.publicacion;
    const boton = $(".drawu-like", publicacion);
    const contador = $(".drawu-like-count", publicacion);

    if (boton) {
      boton.addEventListener("click", async () => {
        try {
          const res = await window.drawuApi("like.php", { id_publicacion: id });
          boton.classList.toggle("liked", res.liked);
          boton.setAttribute("aria-pressed", String(res.liked));
          if (contador) contador.textContent = String(res.total);
          mostrarAviso(res.liked ? "Me gusta agregado" : "Me gusta retirado");
        } catch (err) {
          mostrarAviso(err.message);
        }
      });
    }

    const compartir = $(".drawu-share", publicacion);
    if (compartir) {
      compartir.addEventListener("click", async () => {
        const enlace = window.location.href.split("#")[0];
        try {
          await navigator.clipboard.writeText(enlace);
          mostrarAviso("Enlace copiado al portapapeles");
        } catch {
          window.prompt("Copia el enlace de la página:", enlace);
        }
      });
    }
  });

  // --- Comentarios desde BD ---
  const fondo = $("#drawuComentariosFondo");
  const lista = $("#drawuListaComentarios");
  const formulario = $("#drawuFormularioComentario");
  const entrada = $("#drawuTextoComentario");
  const cerrarBoton = $("#drawuCerrarComentarios");

  let publicacionActual = null;

  function renderListaComentarios(elementos) {
    lista.replaceChildren();
    if (!elementos || elementos.length === 0) {
      const vacio = document.createElement("p");
      vacio.className = "drawu-comments-empty";
      vacio.textContent = "Aún no hay comentarios. Escribe el primero.";
      lista.appendChild(vacio);
      return;
    }

    elementos.forEach((item) => {
      const parrafo = document.createElement("p");
      const usuario = document.createElement("strong");
      parrafo.className = "drawu-comments-item";
      usuario.textContent = item.usuario || "Usuario";
      parrafo.append(usuario, document.createTextNode(item.contenido || ""));
      lista.appendChild(parrafo);
    });
  }

  async function abrirComentarios(id) {
    publicacionActual = id;
    fondo.hidden = false;
    document.body.style.overflow = "hidden";
    lista.replaceChildren();
    entrada.focus();

    try {
      const res = await window.drawuApi("comentarios.php", { id_publicacion: id }, "GET");
      if (publicacionActual === id) {
        renderListaComentarios(res.comentarios);
      }
    } catch (err) {
      mostrarAviso(err.message);
    }
  }

  function cerrarComentarios() {
    fondo.hidden = true;
    document.body.style.overflow = "";
    publicacionActual = null;
  }

  $$(".drawu-comment-open", inicio).forEach((boton) => {
    boton.addEventListener("click", () => {
      const publicacion = boton.closest(".drawu-post");
      abrirComentarios(publicacion.dataset.publicacion);
    });
  });

  cerrarBoton.addEventListener("click", cerrarComentarios);

  fondo.addEventListener("click", (evento) => {
    if (evento.target === fondo) {
      cerrarComentarios();
    }
  });

  document.addEventListener("keydown", (evento) => {
    if (evento.key === "Escape" && !fondo.hidden) {
      cerrarComentarios();
    }
  });

  formulario.addEventListener("submit", async (evento) => {
    evento.preventDefault();
    const texto = entrada.value.trim();
    if (!texto || !publicacionActual) return;

    try {
      const res = await window.drawuApi("comentarios.php", {
        id_publicacion: publicacionActual,
        contenido: texto,
      });
      entrada.value = "";
      renderListaComentarios(res.comentarios);
      mostrarAviso("Comentario publicado");
    } catch (err) {
      mostrarAviso(err.message);
    }
  });
})();
