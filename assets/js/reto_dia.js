(() => {
  "use strict";

  const pagina = document.getElementById("paginaRetoDia");
  if (!pagina) return;

  const $ = (selector, root = document) => root.querySelector(selector);

  const $$ = (selector, root = document) => [
    ...root.querySelectorAll(selector),
  ];

  const idReto = pagina.dataset.retoId || "reto-del-dia";

  const claveLikes = `drawu_demo_${idReto}_likes`;
  const claveComentarios = `drawu_demo_${idReto}_comentarios`;
  const claveSeguidos = "drawu_demo_seguidos";

  function leerDatos(clave, porDefecto) {
    try {
      const valor = localStorage.getItem(clave);
      return valor === null ? porDefecto : JSON.parse(valor);
    } catch {
      return porDefecto;
    }
  }

  function guardarDatos(clave, valor) {
    try {
      localStorage.setItem(clave, JSON.stringify(valor));
    } catch {
      return false;
    }
    return true;
  }

  // ======================================
  // Ocultar imágenes que no existan
  // ======================================
  $$("img[data-asset-opcional]", pagina).forEach((img) => {
    function ocultarImagen() {
      img.style.display = "none";
    }

    img.addEventListener("error", ocultarImagen);

    if (img.complete && img.naturalWidth === 0) {
      ocultarImagen();
    }
  });

  // ======================================
  // Toast
  // ======================================
  const aviso = $("#retoAviso");
  let timerAviso;

  function mostrarAviso(mensaje) {
    if (!aviso) return;

    aviso.textContent = mensaje;
    aviso.classList.add("visible");

    clearTimeout(timerAviso);

    timerAviso = setTimeout(() => {
      aviso.classList.remove("visible");
    }, 2600);
  }

  const seguidos = new Set(leerDatos(claveSeguidos, []));

  function actualizarSeguimientos() {
    $$(".reto-follow", pagina).forEach((boton) => {
      const usuario = boton.dataset.author;
      const siguiendo = seguidos.has(usuario);

      boton.textContent = siguiendo ? "Siguiendo" : "Seguir";
      boton.classList.toggle("siguiendo", siguiendo);
      boton.setAttribute("aria-pressed", String(siguiendo));
    });
  }

  pagina.addEventListener("click", (evento) => {
    const boton = evento.target.closest(".reto-follow");
    if (!boton) return;

    const usuario = boton.dataset.author;

    if (seguidos.has(usuario)) {
      seguidos.delete(usuario);
    } else {
      seguidos.add(usuario);
    }

    guardarDatos(claveSeguidos, [...seguidos]);
    actualizarSeguimientos();

    mostrarAviso(
      seguidos.has(usuario)
        ? `Ahora sigues a ${usuario} (prueba)`
        : `Dejaste de seguir a ${usuario} (prueba)`,
    );
  });

  actualizarSeguimientos();

  const likes = new Set(leerDatos(claveLikes, []));

  function actualizarLikes() {
    $$("[data-action='like']", pagina).forEach((boton) => {
      const postId = boton.dataset.postId;
      const activo = likes.has(postId);
      const contador = $("span", boton);

      boton.classList.toggle("liked", activo);
      boton.setAttribute("aria-pressed", String(activo));

      if (contador) {
        contador.textContent = activo ? "1" : "0";
      }
    });
  }

  actualizarLikes();

  const comentarios = leerDatos(claveComentarios, {});
  const fondo = $("#retoComentariosFondo");
  const lista = $("#retoListaComentarios");
  const formulario = $("#retoFormularioComentario");
  const input = $("#retoTextoComentario");
  const cerrar = $("#retoCerrarComentarios");

  let publicacionActual = null;

  function actualizarContadoresComentarios() {
    $$("[data-action='comment']", pagina).forEach((boton) => {
      const postId = boton.dataset.postId;
      const contador = $("span", boton);
      const total = (comentarios[postId] || []).length;

      if (contador) contador.textContent = total;
    });
  }

  function pintarComentarios() {
    lista.replaceChildren();

    const items = comentarios[publicacionActual] || [];

    if (items.length === 0) {
      const vacio = document.createElement("p");
      vacio.className = "reto-comments-empty";
      vacio.textContent = "Todavía no hay comentarios.";
      lista.appendChild(vacio);
      return;
    }

    items.forEach((texto) => {
      const p = document.createElement("p");
      const strong = document.createElement("strong");

      strong.textContent = "Usuario";

      p.append(strong, document.createTextNode(texto));

      lista.appendChild(p);
    });
  }

  function abrirComentarios(idPost) {
    publicacionActual = idPost;
    fondo.hidden = false;
    document.body.style.overflow = "hidden";
    pintarComentarios();
    input.focus();
  }

  function cerrarComentarios() {
    fondo.hidden = true;
    document.body.style.overflow = "";
    publicacionActual = null;
    input.value = "";
  }

  cerrar.addEventListener("click", cerrarComentarios);

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

    const texto = input.value.trim();
    if (!texto || !publicacionActual) return;

    if (!comentarios[publicacionActual]) {
      comentarios[publicacionActual] = [];
    }

    comentarios[publicacionActual].push(texto);

    guardarDatos(claveComentarios, comentarios);
    input.value = "";

    pintarComentarios();
    actualizarContadoresComentarios();

    mostrarAviso("Comentario agregado (prueba)");
  });

  actualizarContadoresComentarios();

  pagina.addEventListener("click", async (evento) => {
    const boton = evento.target.closest("[data-action]");
    if (!boton) return;

    const accion = boton.dataset.action;
    const postId = boton.dataset.postId;

    if (accion === "like") {
      if (likes.has(postId)) {
        likes.delete(postId);
      } else {
        likes.add(postId);
      }

      guardarDatos(claveLikes, [...likes]);
      actualizarLikes();

      mostrarAviso(
        likes.has(postId)
          ? "Me gusta agregado (prueba)"
          : "Me gusta retirado (prueba)",
      );
    } else if (accion === "comment") {
      abrirComentarios(postId);
    } else if (accion === "share") {
      try {
        await navigator.clipboard.writeText(window.location.href);
        mostrarAviso("Enlace copiado al portapapeles");
      } catch {
        window.prompt("Copia el enlace:", window.location.href);
      }
    }
  });
})();
