(() => {
  "use strict";

  const pagina = document.getElementById("paginaRetoDia");
  if (!pagina) return;

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

  // Ocultar imágenes que no existan
  $$("img[data-asset-opcional]", pagina).forEach((img) => {
    function ocultarImagen() {
      img.style.display = "none";
    }
    img.addEventListener("error", ocultarImagen);
    if (img.complete && img.naturalWidth === 0) {
      ocultarImagen();
    }
  });

  // Toast
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

  // Seguir usuario en BD
  pagina.addEventListener("click", async (evento) => {
    const boton = evento.target.closest(".reto-follow");
    if (!boton) return;

    const idUsuario = boton.dataset.userId;
    const usuario = boton.dataset.author || "usuario";
    if (!idUsuario) return;

    try {
      const res = await window.drawuApi("seguir.php", { id_usuario: idUsuario });
      $$(`.reto-follow[data-user-id="${idUsuario}"]`, pagina).forEach((b) => {
        b.textContent = res.siguiendo ? "Siguiendo" : "Seguir";
        b.classList.toggle("siguiendo", res.siguiendo);
        b.setAttribute("aria-pressed", String(res.siguiendo));
      });
      mostrarAviso(
        res.siguiendo ? `Ahora sigues a ${usuario}` : `Dejaste de seguir a ${usuario}`
      );
    } catch (err) {
      mostrarAviso(err.message);
    }
  });

  // Comentarios
  const fondo = $("#retoComentariosFondo");
  const lista = $("#retoListaComentarios");
  const formulario = $("#retoFormularioComentario");
  const input = $("#retoTextoComentario");
  const cerrar = $("#retoCerrarComentarios");

  let publicacionActual = null;

  function pintarComentarios(items) {
    lista.replaceChildren();
    if (!items || items.length === 0) {
      const vacio = document.createElement("p");
      vacio.className = "reto-comments-empty";
      vacio.textContent = "Todavía no hay comentarios.";
      lista.appendChild(vacio);
      return;
    }

    items.forEach((item) => {
      const p = document.createElement("p");
      const strong = document.createElement("strong");
      strong.textContent = item.usuario || "Usuario";
      p.append(strong, document.createTextNode(item.contenido || ""));
      lista.appendChild(p);
    });
  }

  async function abrirComentarios(idPost) {
    publicacionActual = idPost;
    fondo.hidden = false;
    document.body.style.overflow = "hidden";
    lista.replaceChildren();
    input.focus();

    try {
      const res = await window.drawuApi("comentarios.php", { id_publicacion: idPost }, "GET");
      if (publicacionActual === idPost) {
        pintarComentarios(res.comentarios);
      }
    } catch (err) {
      mostrarAviso(err.message);
    }
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

  formulario.addEventListener("submit", async (evento) => {
    evento.preventDefault();
    const texto = input.value.trim();
    if (!texto || !publicacionActual) return;

    try {
      const res = await window.drawuApi("comentarios.php", {
        id_publicacion: publicacionActual,
        contenido: texto,
      });
      input.value = "";
      pintarComentarios(res.comentarios);

      $$(`[data-action='comment'][data-post-id='${publicacionActual}']`, pagina).forEach((boton) => {
        const contador = $("span", boton);
        if (contador) contador.textContent = String(res.total);
      });
      mostrarAviso("Comentario publicado");
    } catch (err) {
      mostrarAviso(err.message);
    }
  });

  pagina.addEventListener("click", async (evento) => {
    const boton = evento.target.closest("[data-action]");
    if (!boton) return;

    const accion = boton.dataset.action;
    const postId = boton.dataset.postId;

    if (accion === "like") {
      try {
        const res = await window.drawuApi("like.php", { id_publicacion: postId });
        $$(`[data-action='like'][data-post-id='${postId}']`, pagina).forEach((b) => {
          b.classList.toggle("liked", res.liked);
          b.setAttribute("aria-pressed", String(res.liked));
          const span = $("span", b);
          if (span) span.textContent = String(res.total);
        });
        mostrarAviso(res.liked ? "Me gusta agregado" : "Me gusta retirado");
      } catch (err) {
        mostrarAviso(err.message);
      }
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
