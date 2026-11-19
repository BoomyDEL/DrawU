(() => {
  "use strict";

  const pagina = document.getElementById("paginaComunidad");
  if (!pagina) return;

  const idComunidad = pagina.dataset.communityId;
  let esMiembro = pagina.dataset.isMember === "1";

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

  const feed = $("#listaPublicaciones");
  const aviso = $("#avisoComunidadInd");
  let timer;

  function notificar(texto) {
    if (!aviso) return;
    aviso.textContent = texto;
    aviso.classList.add("visible");
    clearTimeout(timer);
    timer = setTimeout(() => {
      aviso.classList.remove("visible");
    }, 2700);
  }

  // --- Unirse / Salir de la comunidad ---
  const btnUnirse = $("#btnUnirse");
  const subtituloMiembros = $("#comunidadSubtituloMiembros");

  btnUnirse.addEventListener("click", async () => {
    btnUnirse.disabled = true;
    try {
      const res = await window.drawuApi("comunidad.php", {
        accion: "unirse",
        id_comunidad: idComunidad,
      });
      esMiembro = Boolean(res.es_miembro);
      pagina.dataset.isMember = esMiembro ? "1" : "0";
      btnUnirse.textContent = esMiembro ? "Unido a la comunidad" : "Unirse a la comunidad";
      btnUnirse.classList.toggle("unido", esMiembro);
      btnUnirse.setAttribute("aria-pressed", String(esMiembro));

      if (subtituloMiembros && typeof res.miembros === "number") {
        const txt = `${res.miembros} ${res.miembros === 1 ? "miembro" : "miembros"} · Publicaciones de esta comunidad`;
        subtituloMiembros.textContent = txt;
      }

      notificar(esMiembro ? "Te uniste a la comunidad." : "Saliste de la comunidad.");
    } catch (err) {
      notificar(err.message);
    } finally {
      btnUnirse.disabled = false;
    }
  });

  // --- Seguir usuario ---
  feed.addEventListener("click", async (e) => {
    const boton = e.target.closest(".com-follow");
    if (!boton) return;

    const idUsuario = boton.dataset.userId;
    const nombreUsuario = boton.dataset.author || "usuario";
    if (!idUsuario) return;

    try {
      const res = await window.drawuApi("seguir.php", { id_usuario: idUsuario });
      $$(`.com-follow[data-user-id="${idUsuario}"]`, feed).forEach((b) => {
        b.textContent = res.siguiendo ? "Siguiendo" : "Seguir";
        b.classList.toggle("siguiendo", res.siguiendo);
        b.setAttribute("aria-pressed", String(res.siguiendo));
      });
      notificar(
        res.siguiendo
          ? `Ahora sigues a ${nombreUsuario}.`
          : `Dejaste de seguir a ${nombreUsuario}.`
      );
    } catch (err) {
      notificar(err.message);
    }
  });

  // --- Crear publicación en esta comunidad ---
  const form = $("#formPublicacionComunidad");
  const texto = $("#textoPublicacion");
  const archivo = $("#imagenPublicacion");

  $("#btnAbrirPublicacion").addEventListener("click", () => {
    if (!esMiembro) {
      notificar("Primero únete a la comunidad para publicar.");
      return;
    }
    form.hidden = false;
    texto.focus();
  });

  $("#btnCancelarPublicacion").addEventListener("click", () => {
    form.hidden = true;
    form.reset();
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const contenido = texto.value.trim();
    const file = archivo.files[0];

    if (!contenido && !file) {
      return notificar("Escribe un texto o selecciona una imagen.");
    }

    const datos = new FormData();
    datos.append("modo", "normal");
    datos.append("id_comunidad", idComunidad);
    datos.append("descripcion", contenido);
    if (file) {
      datos.append("archivo", file);
    }

    const btnSubmit = form.querySelector('[type="submit"]');
    btnSubmit.disabled = true;

    try {
      await window.drawuApi("publicacion.php", datos);
      window.location.reload();
    } catch (err) {
      notificar(err.message);
      btnSubmit.disabled = false;
    }
  });

  // --- Comentarios ---
  const fondo = $("#fondoComentariosCom");
  const listaComentarios = $("#listaComentariosCom");
  const inputComentario = $("#textoComentarioCom");
  let postActivo = null;

  function renderComentarios(elementos) {
    listaComentarios.replaceChildren();
    if (!elementos || !elementos.length) {
      const p = document.createElement("p");
      p.textContent = "Todavía no hay comentarios.";
      listaComentarios.appendChild(p);
      return;
    }

    elementos.forEach((item) => {
      const p = document.createElement("p");
      const autor = document.createElement("strong");
      autor.textContent = item.usuario || "Usuario";
      p.append(autor, document.createTextNode(item.contenido || ""));
      listaComentarios.appendChild(p);
    });
  }

  function cerrarComentarios() {
    fondo.hidden = true;
    postActivo = null;
    inputComentario.value = "";
  }

  $("#cerrarComentariosCom").addEventListener("click", cerrarComentarios);

  fondo.addEventListener("click", (e) => {
    if (e.target === fondo) {
      cerrarComentarios();
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !fondo.hidden) {
      cerrarComentarios();
    }
  });

  feed.addEventListener("click", async (e) => {
    const boton = e.target.closest("[data-action]");
    if (!boton) return;

    const post = boton.closest(".com-post");
    const idPost = post.dataset.postId;

    if (boton.dataset.action === "like") {
      try {
        const res = await window.drawuApi("like.php", { id_publicacion: idPost });
        boton.classList.toggle("liked", res.liked);
        boton.setAttribute("aria-pressed", String(res.liked));
        const span = $("span", boton);
        if (span) span.textContent = String(res.total);
      } catch (err) {
        notificar(err.message);
      }
    } else if (boton.dataset.action === "comment") {
      postActivo = idPost;
      listaComentarios.replaceChildren();
      fondo.hidden = false;
      inputComentario.focus();

      try {
        const res = await window.drawuApi("comentarios.php", { id_publicacion: idPost }, "GET");
        if (postActivo === idPost) {
          renderComentarios(res.comentarios);
        }
      } catch (err) {
        notificar(err.message);
      }
    } else if (boton.dataset.action === "share") {
      try {
        await navigator.clipboard.writeText(window.location.href);
        notificar("Enlace de comunidad copiado.");
      } catch {
        notificar("Copia la dirección de la comunidad desde el navegador.");
      }
    }
  });

  $("#formComentariosCom").addEventListener("submit", async (e) => {
    e.preventDefault();
    const valor = inputComentario.value.trim();
    if (!valor || !postActivo) return;

    try {
      const res = await window.drawuApi("comentarios.php", {
        id_publicacion: postActivo,
        contenido: valor,
      });
      inputComentario.value = "";
      renderComentarios(res.comentarios);

      const postEl = $(`.com-post[data-post-id="${postActivo}"]`, feed);
      if (postEl) {
        const span = $('[data-action="comment"] span', postEl);
        if (span) span.textContent = String(res.total);
      }
      notificar("Comentario publicado.");
    } catch (err) {
      notificar(err.message);
    }
  });
})();
