/**
 * api.js - Helper para comunicarse con el backend (carpeta /api).
 * window.DRAWU se define en views/includes/header/header.php.
 *
 * Uso:
 *   const datos = await drawuApi('like.php', { id_publicacion: 3 });
 *   const datos = await drawuApi('comentarios.php', { id_publicacion: 3 }, 'GET');
 *   const datos = await drawuApi('publicacion.php', formData);   // con archivos
 */
(() => {
  "use strict";

  const base = (window.DRAWU && window.DRAWU.api) || "../api/";

  async function drawuApi(endpoint, datos = {}, metodo = "POST") {
    let url = base + endpoint;
    const opciones = { method: metodo, credentials: "same-origin", headers: { Accept: "application/json" } };

    if (metodo === "GET") {
      const params = new URLSearchParams(datos).toString();
      if (params) url += (url.includes("?") ? "&" : "?") + params;
    } else if (datos instanceof FormData) {
      opciones.body = datos;
    } else {
      const form = new FormData();
      Object.entries(datos).forEach(([clave, valor]) => {
        if (valor !== undefined && valor !== null) form.append(clave, valor);
      });
      opciones.body = form;
    }

    let respuesta;
    try {
      respuesta = await fetch(url, opciones);
    } catch {
      throw new Error("No se pudo conectar con el servidor.");
    }

    let json = null;
    try {
      json = await respuesta.json();
    } catch {
      /* respuesta no JSON */
    }

    if (respuesta.status === 401) {
      window.location.href = "pagina_login.php";
      throw new Error((json && json.error) || "Sesión expirada.");
    }
    if (!respuesta.ok || !json || json.ok === false) {
      throw new Error((json && json.error) || "Ocurrió un error inesperado.");
    }
    return json;
  }

  window.drawuApi = drawuApi;
})();
