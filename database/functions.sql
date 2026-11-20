CREATE FUNCTION fn_usuario_dio_like(
    p_id_publicacion INT,
    p_id_usuario INT
)
RETURNS TINYINT(1)
READS SQL DATA

RETURN (
    SELECT COUNT(*) > 0
    FROM likes_publicaciones
    WHERE id_publicacion = p_id_publicacion
      AND id_usuario = p_id_usuario
);


CREATE FUNCTION fn_publicacion_existe(
    p_id_publicacion INT
)
RETURNS TINYINT(1)
READS SQL DATA

RETURN (
    SELECT COUNT(*) > 0
    FROM publicaciones
    WHERE id_publicacion = p_id_publicacion
);


CREATE FUNCTION fn_usuario_bloqueado(
    p_id_bloqueador INT,
    p_id_bloqueado INT
)
RETURNS TINYINT(1)
READS SQL DATA

RETURN (
    SELECT COUNT(*) > 0
    FROM bloqueos
    WHERE id_bloqueador = p_id_bloqueador
      AND id_bloqueado = p_id_bloqueado
);

CREATE FUNCTION fn_es_miembro_comunidad(
    p_id_comunidad INT,
    p_id_usuario INT
)
RETURNS TINYINT(1)
READS SQL DATA

RETURN (
    SELECT COUNT(*) > 0
    FROM vista_miembros_comunidad
    WHERE id_comunidad = p_id_comunidad
      AND id_usuario = p_id_usuario
);


CREATE FUNCTION fn_usuario_tiene_mascota(
    p_id_usuario INT,
    p_id_mascota INT
)
RETURNS TINYINT(1)
READS SQL DATA

RETURN (
    SELECT COUNT(*) > 0
    FROM usuarios_mascotas
    WHERE id_usuario = p_id_usuario
      AND id_mascota = p_id_mascota
);


CREATE FUNCTION fn_nombre_disponible(
    p_nombre VARCHAR(50),
    p_id_usuario INT
)
RETURNS TINYINT(1)
READS SQL DATA

RETURN (
    SELECT COUNT(*) = 0
    FROM usuarios
    WHERE nombre_usuario = p_nombre
      AND id_usuario <> p_id_usuario
);


CREATE FUNCTION fn_usuario_existe(
    p_id_usuario INT UNSIGNED
)
RETURNS TINYINT(1)
READS SQL DATA

RETURN (
    SELECT COUNT(*) > 0
    FROM usuarios
    WHERE id_usuario = p_id_usuario
);


CREATE FUNCTION fn_total_mensajes_soporte(
    p_id_usuario INT UNSIGNED
)
RETURNS INT
READS SQL DATA

RETURN (
    SELECT COUNT(*)
    FROM mensajes_soporte
    WHERE id_usuario = p_id_usuario
);








