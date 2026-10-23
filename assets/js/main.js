/**
 * main.js - Sincronización global de monedas y temas en todas las vistas de DrawU
 */
(() => {
  "use strict";

  // Sincronizar estilo/tema de mascota activo en todo el sitio
  function syncGlobalPetTheme() {
    const activePet = localStorage.getItem('drawu_active_pet') || '1';
    document.body.classList.remove('tema-pet-aqua', 'tema-pet-verde');
    document.documentElement.classList.remove('tema-pet-aqua', 'tema-pet-verde');

    if (activePet === '2') {
      document.body.classList.add('tema-pet-aqua');
      document.documentElement.classList.add('tema-pet-aqua');
    } else if (activePet === '3') {
      document.body.classList.add('tema-pet-verde');
      document.documentElement.classList.add('tema-pet-verde');
    }
  }

  // Sincronizar contador de monedas en el header
  function syncGlobalCoins() {
    const coinsCountEl = document.querySelector('.header-coins-count');
    if (coinsCountEl) {
      const storedCoins = localStorage.getItem('drawu_user_coins');
      if (storedCoins !== null) {
        coinsCountEl.textContent = storedCoins;
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      syncGlobalPetTheme();
      syncGlobalCoins();
    });
  } else {
    syncGlobalPetTheme();
    syncGlobalCoins();
  }

  // Escuchar cambios de storage entre pestañas
  window.addEventListener('storage', (e) => {
    if (e.key === 'drawu_active_pet') {
      syncGlobalPetTheme();
    }
    if (e.key === 'drawu_user_coins') {
      syncGlobalCoins();
    }
  });
})();


(() => {
  "use strict";
  const inicio = document.querySelector(".drawu-home");

  if (!inicio) return;

  const $ = (selector, root = document) => root.querySelector(selector);

  const $$ = (selector, root = document) => [
    ...root.querySelectorAll(selector),
  ];

  function leerDatos(clave, predeterminado) {
    try {
      const valor = localStorage.getItem(clave);

      return valor === null ? predeterminado : JSON.parse(valor);
    } catch {
      return predeterminado;
    }
  }

  function guardarDatos(clave, valor) {
    try {
      localStorage.setItem(clave, JSON.stringify(valor));
    } catch {}
  }

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
      pista.scrollBy({
        left: -anchoTarjeta(),
        behavior: "smooth",
      });
    });

    siguiente.addEventListener("click", () => {
      pista.scrollBy({
        left: anchoTarjeta(),
        behavior: "smooth",
      });
    });

    pista.addEventListener("scroll", actualizarFlechas, { passive: true });

    window.addEventListener("resize", actualizarFlechas);

    actualizarFlechas();
  }

  const seguidos = new Set(leerDatos("drawu_demo_seguidos", []));

  function actualizarSeguimientos() {
    $$(".drawu-follow", inicio).forEach((boton) => {
      const usuario = boton.dataset.followUser;
      const siguiendo = seguidos.has(usuario);

      boton.textContent = siguiendo ? "Siguiendo" : "Seguir";

      boton.classList.toggle("siguiendo", siguiendo);

      boton.setAttribute("aria-pressed", String(siguiendo));
    });
  }

  $$(".drawu-follow", inicio).forEach((boton) => {
    boton.addEventListener("click", () => {
      const usuario = boton.dataset.followUser;

      if (seguidos.has(usuario)) {
        seguidos.delete(usuario);
      } else {
        seguidos.add(usuario);
      }

      guardarDatos("drawu_demo_seguidos", [...seguidos]);

      actualizarSeguimientos();

      mostrarAviso(
        seguidos.has(usuario)
          ? `Ahora sigues a ${usuario} (prueba)`
          : `Dejaste de seguir a ${usuario} (prueba)`,
      );
    });
  });

  actualizarSeguimientos();

  const likes = new Set(leerDatos("drawu_demo_likes_inicio", []));

  $$(".drawu-post", inicio).forEach((publicacion) => {
    const id = publicacion.dataset.publicacion;
    const boton = $(".drawu-like", publicacion);
    const contador = $(".drawu-like-count", publicacion);

    function actualizarLike() {
      const activo = likes.has(id);

      boton.classList.toggle("liked", activo);

      boton.setAttribute("aria-pressed", String(activo));

      contador.textContent = activo ? "1" : "0";
    }

    boton.addEventListener("click", () => {
      if (likes.has(id)) {
        likes.delete(id);
      } else {
        likes.add(id);
      }

      guardarDatos("drawu_demo_likes_inicio", [...likes]);

      actualizarLike();

      mostrarAviso(
        likes.has(id)
          ? "Me gusta agregado (prueba)"
          : "Me gusta retirado (prueba)",
      );
    });

    actualizarLike();

    const compartir = $(".drawu-share", publicacion);

    compartir.addEventListener("click", async () => {
      const enlace = window.location.href.split("#")[0];

      try {
        await navigator.clipboard.writeText(enlace);

        mostrarAviso("Enlace copiado al portapapeles");
      } catch {
        window.prompt("Copia el enlace de la p├ígina:", enlace);
      }
    });
  });

  const fondo = $("#drawuComentariosFondo");
  const lista = $("#drawuListaComentarios");
  const formulario = $("#drawuFormularioComentario");
  const entrada = $("#drawuTextoComentario");
  const cerrarBoton = $("#drawuCerrarComentarios");

  let publicacionActual = null;

  const comentarios = leerDatos("drawu_demo_comentarios_inicio", {});

  function mostrarComentarios() {
    lista.replaceChildren();

    const elementos = comentarios[publicacionActual] || [];

    if (elementos.length === 0) {
      const vacio = document.createElement("p");

      vacio.className = "drawu-comments-empty";
      vacio.textContent = "A├║n no hay comentarios. Escribe el primero.";

      lista.appendChild(vacio);
      return;
    }

    elementos.forEach((texto) => {
      const parrafo = document.createElement("p");
      const usuario = document.createElement("strong");

      parrafo.className = "drawu-comments-item";

      usuario.textContent = "Usuario";

      parrafo.append(usuario, document.createTextNode(texto));

      lista.appendChild(parrafo);
    });
  }

  function abrirComentarios(id) {
    publicacionActual = id;

    fondo.hidden = false;
    document.body.style.overflow = "hidden";

    mostrarComentarios();
    entrada.focus();
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

  formulario.addEventListener("submit", (evento) => {
    evento.preventDefault();

    const texto = entrada.value.trim();

    if (!texto || !publicacionActual) return;

    if (!comentarios[publicacionActual]) {
      comentarios[publicacionActual] = [];
    }

    comentarios[publicacionActual].push(texto);

    guardarDatos("drawu_demo_comentarios_inicio", comentarios);

    entrada.value = "";

    mostrarComentarios();

    mostrarAviso("Comentario agregado (prueba local)");
  });
})();
