CREATE OR REPLACE VIEW vista_usuario_completo AS

SELECT
    u.id_usuario,
    u.primer_nombre,
    u.primer_apellido,
    u.nombre_usuario,
    u.correo,
    u.imagen_perfil,
    u.descripcion_perfil,
    u.tema,
    u.drawings,
    u.id_mascota_activa,

    m.nombre AS mascota_nombre,
    m.ruta_imagen AS mascota_imagen,
    m.color_tema AS mascota_color,

    (SELECT COUNT(*)
     FROM publicaciones p
     WHERE p.id_usuario = u.id_usuario
    ) AS total_publicaciones,

    (SELECT COUNT(*)
     FROM seguimientos s
     WHERE s.id_seguido = u.id_usuario
    ) AS total_seguidores,

    (SELECT COUNT(*)
     FROM seguimientos s2
     WHERE s2.id_seguidor = u.id_usuario
    ) AS total_seguidos

FROM usuarios u

LEFT JOIN mascotas m
    ON m.id_mascota = u.id_mascota_activa;

-- Filtrar publicaciones
CREATE OR REPLACE VIEW vista_publicaciones_detalle AS

SELECT
    p.id_publicacion,
    p.id_usuario,
    p.id_comunidad,
    p.id_reto,
    p.tipo,
    p.titulo,
    p.descripcion,
    p.contenido,
    p.ruta_imagen,
    p.fecha_publicacion,

    u.nombre_usuario,
    u.imagen_perfil,

    co.titulo AS comunidad_titulo,
    co.slug AS comunidad_slug,
    co.privacidad AS comunidad_privacidad,

    v.ruta_video,

    (SELECT COUNT(*)
     FROM likes_publicaciones l
     WHERE l.id_publicacion = p.id_publicacion
    ) AS total_likes,

    (SELECT COUNT(*)
     FROM comentarios c
     WHERE c.id_publicacion = p.id_publicacion
    ) AS total_comentarios

FROM publicaciones p

JOIN usuarios u
    ON u.id_usuario = p.id_usuario

LEFT JOIN comunidades co
    ON co.id_comunidad = p.id_comunidad

LEFT JOIN videos v
    ON v.id_publicacion = p.id_publicacion;

-- Obtener Comunidades

CREATE OR REPLACE VIEW vista_comunidades_detalle AS

SELECT
    c.id_comunidad,
    c.id_creador,
    c.slug,
    c.titulo,
    c.descripcion,
    c.privacidad,
    c.imagen_perfil,
    c.imagen_banner,
    c.fecha_creacion,

    (SELECT COUNT(*)
     FROM comunidades_usuarios cu
     WHERE cu.id_comunidad = c.id_comunidad
    ) AS total_miembros,

    (SELECT COUNT(*)
     FROM publicaciones p
     WHERE p.id_comunidad = c.id_comunidad
       AND p.fecha_publicacion >= CURDATE()
    ) AS publicaciones_hoy

FROM comunidades c;

-- Reto del día
CREATE OR REPLACE VIEW vista_reto_actual AS

SELECT *
FROM retos_del_dia

ORDER BY
    (CURDATE() BETWEEN fecha_inicio AND fecha_fin) DESC,
    fecha_inicio DESC,
    id_reto DESC

LIMIT 1;

-- Contar likes por publicación

CREATE OR REPLACE VIEW vista_total_likes AS

SELECT
    p.id_publicacion,
    COUNT(l.id_publicacion) AS total_likes

FROM publicaciones p

LEFT JOIN likes_publicaciones l
    ON l.id_publicacion = p.id_publicacion

GROUP BY p.id_publicacion;

-- Comentarios

CREATE OR REPLACE VIEW vista_comentarios_detalle AS

SELECT
    c.id_comentario AS id,
    c.id_publicacion,
    c.id_usuario,
    u.nombre_usuario AS usuario,
    c.contenido,
    c.fecha_comentario AS fecha

FROM comentarios c

INNER JOIN usuarios u
    ON u.id_usuario = c.id_usuario;

-- Seguidores

CREATE OR REPLACE VIEW vista_total_seguidores AS

SELECT
    u.id_usuario,
    COUNT(s.id_seguidor) AS total_seguidores

FROM usuarios u

LEFT JOIN seguimientos s
    ON s.id_seguido = u.id_usuario

GROUP BY u.id_usuario;

-- Miembros - Comunidad
CREATE OR REPLACE VIEW vista_miembros_comunidad AS
SELECT
    cu.id_comunidad,
    cu.id_usuario,
    u.nombre_usuario,
    u.imagen_perfil,
    (cu.id_usuario = c.id_creador) AS es_creador
FROM comunidades_usuarios cu
JOIN usuarios u
    ON u.id_usuario = cu.id_usuario
JOIN comunidades c
    ON c.id_comunidad = cu.id_comunidad;

-- Mascotas
    
CREATE OR REPLACE VIEW vista_catalogo_mascotas AS
SELECT
    m.id_mascota,
    m.nombre,
    m.es_inicial,
    a.id_articulo,
    a.precio_drawings

FROM mascotas m

LEFT JOIN articulos a
    ON a.id_articulo = (
        SELECT MIN(a2.id_articulo)
        FROM articulos a2
        WHERE a2.tipo = 'mascota'
          AND a2.id_mascota = m.id_mascota
          AND a2.activo = TRUE
    );

-- retos
CREATE OR REPLACE VIEW vista_retos_vigentes AS

SELECT
    id_reto,
    fecha_inicio,
    fecha_fin

FROM retos_del_dia

WHERE CURDATE() BETWEEN fecha_inicio AND fecha_fin;

-- reportes

CREATE OR REPLACE VIEW vista_reportes_detalle AS

SELECT
    r.id_reporte,
    r.id_reportador,
    u1.nombre_usuario AS usuario_reportador,
    r.id_reportado,
    u2.nombre_usuario AS usuario_reportado,
    r.motivo,
    r.detalle,
    r.fecha_reporte

FROM reportes r

JOIN usuarios u1
    ON u1.id_usuario = r.id_reportador

JOIN usuarios u2
    ON u2.id_usuario = r.id_reportado;

-- Mensajes soporte
CREATE OR REPLACE VIEW vista_mensajes_soporte AS

SELECT
    m.id_mensaje,
    m.id_usuario,
    u.nombre_usuario,
    m.mensaje,
    m.fecha_envio

FROM mensajes_soporte m

INNER JOIN usuarios u
    ON u.id_usuario = m.id_usuario;






