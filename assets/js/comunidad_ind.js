(() => {
  "use strict";

  const pagina = document.getElementById("paginaComunidad");

  if (!pagina) return;

  const idComunidad = pagina.dataset.communityId;

  const $ = (selector, root = document) => root.querySelector(selector);

  const $$ = (selector, root = document) => [
    ...root.querySelectorAll(selector),
  ];

  const feed = $("#listaPublicaciones");

  const clavePosts = `drawu_com_${idComunidad}_posts`;

  const claveLikes = `drawu_com_${idComunidad}_likes`;

  const claveComentarios = `drawu_com_${idComunidad}_comentarios`;

  const claveMiembros = "drawu_demo_comunidades_unidas";

  const claveSeguidos = "drawu_demo_seguidos";

  function leer(clave, inicial) {
    try {
      const dato = JSON.parse(localStorage.getItem(clave));
      return dato ?? inicial;
    } catch {
      return inicial;
    }
  }

  function guardar(clave, dato) {
    try {
      localStorage.setItem(clave, JSON.stringify(dato));

      return true;
    } catch {
      return false;
    }
  }

  const miembros = new Set(leer(claveMiembros, []));
  const seguidos = new Set(leer(claveSeguidos, []));
  const likes = new Set(leer(claveLikes, []));
  const comentarios = leer(claveComentarios, {});
  const publicacionesLocales = leer(clavePosts, []);

  const aviso = $("#avisoComunidadInd");

  let timer;

  function notificar(texto) {
    aviso.textContent = texto;
    aviso.classList.add("visible");

    clearTimeout(timer);

    timer = setTimeout(() => {
      aviso.classList.remove("visible");
    }, 2700);
  }

  const btnUnirse = $("#btnUnirse");

  function actualizarMiembro() {
    const unido = miembros.has(idComunidad);

    btnUnirse.textContent = unido
      ? "Unido a la comunidad"
      : "Unirse a la comunidad";

    btnUnirse.classList.toggle("unido", unido);

    btnUnirse.setAttribute("aria-pressed", String(unido));
  }

  btnUnirse.addEventListener("click", () => {
    if (miembros.has(idComunidad)) {
      miembros.delete(idComunidad);
    } else {
      miembros.add(idComunidad);
    }

    guardar(claveMiembros, [...miembros]);

    actualizarMiembro();

    notificar(
      miembros.has(idComunidad)
        ? "Te uniste a la comunidad (prueba)."
        : "Saliste de la comunidad (prueba).",
    );
  });

  actualizarMiembro();

  function actualizarSeguimientos() {
    $$(".com-follow", feed).forEach((boton) => {
      const activo = seguidos.has(boton.dataset.author);

      boton.textContent = activo ? "Siguiendo" : "Seguir";

      boton.classList.toggle("siguiendo", activo);

      boton.setAttribute("aria-pressed", String(activo));
    });
  }

  feed.addEventListener("click", (e) => {
    const boton = e.target.closest(".com-follow");

    if (!boton) return;

    const usuario = boton.dataset.author;

    if (seguidos.has(usuario)) {
      seguidos.delete(usuario);
    } else {
      seguidos.add(usuario);
    }

    guardar(claveSeguidos, [...seguidos]);

    actualizarSeguimientos();

    notificar(
      seguidos.has(usuario)
        ? `Ahora sigues a ${usuario}.`
        : `Dejaste de seguir a ${usuario}.`,
    );
  });

  const form = $("#formPublicacionComunidad");
  const texto = $("#textoPublicacion");
  const archivo = $("#imagenPublicacion");

  $("#btnAbrirPublicacion").addEventListener("click", () => {
    if (!miembros.has(idComunidad)) {
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

  function tarjetaDePublicacion(post) {
    const art = document.createElement("article");

    art.className = "com-post";
    art.dataset.postId = post.id;

    art.innerHTML = `

    <!-- ENCABEZADO -->
    <header class="com-post-header">

        <a
            href="usuario_propio.php"
            class="com-post-avatar"
            aria-label="Ver perfil"
        >
            <svg aria-hidden="true">
                <use href="#com-icon-user"></use>
            </svg>
        </a>

        <a
            href="usuario_propio.php"
            class="com-post-author"
        ></a>

        <button
            type="button"
            class="com-follow"
            aria-pressed="false"
        >
            Seguir
        </button>

    </header>


    <!-- DESCRIPCIÓN -->
    <p class="com-post-text"></p>


    <!-- ACCIONES -->
    <footer class="com-post-actions">

        <!-- ME GUSTA -->
        <button
            type="button"
            class="com-like"
            data-action="like"
            aria-label="Me gusta"
            aria-pressed="false"
        >
            <svg class="com-icon" aria-hidden="true">
                <use href="#com-icon-heart"></use>
            </svg>

            <span>0</span>
        </button>


        <!-- COMENTARIOS -->
        <button
            type="button"
            data-action="comment"
            aria-label="Abrir comentarios"
        >
            <svg class="com-icon" aria-hidden="true">
                <use href="#com-icon-comment"></use>
            </svg>

            <span>0</span>
        </button>


        <!-- COMPARTIR -->
        <button
            type="button"
            data-action="share"
            aria-label="Compartir publicación"
        >
            <svg class="com-icon" aria-hidden="true">
                <use href="#com-icon-share"></use>
            </svg>
        </button>

    </footer>
`;

    const nombreAutor = art.querySelector(".com-post-author");

    const botonSeguir = art.querySelector(".com-follow");

    nombreAutor.textContent = post.autor;

    const rutaPerfil =
      post.autor === "Tú" ? "usuario_propio.php" : "usuario_externo.php";

    nombreAutor.href = rutaPerfil;

    art.querySelector(".com-post-avatar").href = rutaPerfil;

    botonSeguir.dataset.author = post.autor;

    const descripcion = art.querySelector(".com-post-text");

    if (post.texto && post.texto.trim()) {
      descripcion.textContent = post.texto;
    } else {
      // No mostrar espacio si no hay descripción
      descripcion.remove();
    }

    if (post.imagen) {
      const contenedorImagen = document.createElement("div");

      contenedorImagen.className = "com-post-image";

      const imagen = document.createElement("img");

      imagen.src = post.imagen;
      imagen.alt = "Imagen de la publicación";
      imagen.loading = "lazy";

      imagen.addEventListener("error", () => {
        imagen.hidden = true;
      });

      contenedorImagen.appendChild(imagen);
      const acciones = art.querySelector(".com-post-actions");

      art.insertBefore(contenedorImagen, acciones);
    }

    return art;
  }

  publicacionesLocales
    .slice()
    .reverse()
    .forEach((post) => {
      feed.prepend(tarjetaDePublicacion(post));
    });

  function actualizarAcciones() {
    $$(".com-post", feed).forEach((post) => {
      const clave = post.dataset.postId;

      const like = $(".com-like", post);

      const liked = likes.has(clave);

      like.classList.toggle("liked", liked);

      like.setAttribute("aria-pressed", String(liked));

      $("span", like).textContent = liked ? "1" : "0";

      $('[data-action="comment"] span', post).textContent = (
        comentarios[clave] || []
      ).length;
    });

    $("#comSinPublicaciones").hidden = $$(".com-post", feed).length !== 0;

    actualizarSeguimientos();
  }

  function leerImagen(file) {
    return new Promise((resolver, rechazar) => {
      const lector = new FileReader();

      lector.onload = () => {
        resolver(lector.result);
      };

      lector.onerror = () => {
        rechazar(new Error("No se pudo leer la imagen"));
      };

      lector.readAsDataURL(file);
    });
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const contenido = texto.value.trim();
    const file = archivo.files[0];

    if (!contenido && !file) {
      return notificar("Escribe un texto o selecciona una imagen.");
    }

    if (
      file &&
      (!["image/png", "image/jpeg", "image/webp"].includes(file.type) ||
        file.size > 250 * 1024)
    ) {
      return notificar("Usa JPG, PNG o WebP de máximo 250 KB.");
    }

    let imagen = "";

    try {
      if (file) {
        imagen = await leerImagen(file);
      }
    } catch {
      return notificar("No se pudo leer la imagen.");
    }

    const post = {
      id: `local-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,

      autor: "Tú",

      texto: contenido,

      imagen,
    };

    publicacionesLocales.unshift(post);

    feed.prepend(tarjetaDePublicacion(post));

    if (!guardar(clavePosts, publicacionesLocales)) {
      notificar(
        "Publicación visible, pero no se pudo guardar en este navegador.",
      );
    } else {
      notificar("Publicación creada en esta comunidad (prueba).");
    }

    form.reset();
    form.hidden = true;

    actualizarAcciones();
  });

  const fondo = $("#fondoComentariosCom");

  const listaComentarios = $("#listaComentariosCom");

  const inputComentario = $("#textoComentarioCom");

  let postActivo = null;

  function pintarComentarios() {
    listaComentarios.replaceChildren();

    const elementos = comentarios[postActivo] || [];

    if (!elementos.length) {
      const p = document.createElement("p");

      p.textContent = "Todavía no hay comentarios.";

      listaComentarios.appendChild(p);
    }

    elementos.forEach((mensaje) => {
      const p = document.createElement("p");

      const autor = document.createElement("strong");

      autor.textContent = "Tú";

      p.append(autor, document.createTextNode(mensaje));

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

    const clave = post.dataset.postId;

    // Me gusta
    if (boton.dataset.action === "like") {
      if (likes.has(clave)) {
        likes.delete(clave);
      } else {
        likes.add(clave);
      }

      guardar(claveLikes, [...likes]);

      actualizarAcciones();
    }

    // Comentarios
    else if (boton.dataset.action === "comment") {
      postActivo = clave;

      pintarComentarios();

      fondo.hidden = false;

      inputComentario.focus();
    }

    // Compartir
    else if (boton.dataset.action === "share") {
      try {
        await navigator.clipboard.writeText(window.location.href);

        notificar("Enlace de comunidad copiado.");
      } catch {
        notificar("Copia la dirección de la comunidad desde el navegador.");
      }
    }
  });

  $("#formComentariosCom").addEventListener("submit", (e) => {
    e.preventDefault();

    const valor = inputComentario.value.trim();

    if (!valor || !postActivo) return;

    if (!comentarios[postActivo]) {
      comentarios[postActivo] = [];
    }

    comentarios[postActivo].push(valor);

    guardar(claveComentarios, comentarios);

    inputComentario.value = "";

    pintarComentarios();

    actualizarAcciones();

    notificar("Comentario agregado (prueba).");
  });

  actualizarAcciones();
})();
