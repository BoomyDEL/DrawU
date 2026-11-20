-- Para procedures

USE drawu;

DELIMITER $$

-- 01. sp_admin_cambiar_estado_usuario
DROP PROCEDURE IF EXISTS sp_admin_cambiar_estado_usuario$$
CREATE PROCEDURE sp_admin_cambiar_estado_usuario(
    IN p_admin INT UNSIGNED,
    IN p_usuario INT UNSIGNED,
    IN p_activo TINYINT
)
    MODIFIES SQL DATA
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM usuarios u
        WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Administrador no autorizado';
    END IF;

    IF p_activo IS NULL OR p_activo NOT IN (0, 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Estado de usuario invalido';
    END IF;

    IF p_usuario = p_admin THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puedes modificar tu propia cuenta';
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM usuarios u WHERE u.id_usuario = p_usuario
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El usuario no existe';
    END IF;

    IF EXISTS (
        SELECT 1 FROM usuarios u WHERE u.id_usuario = p_usuario AND u.es_admin = 1
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puedes desactivar administradores';
    END IF;

    UPDATE usuarios
    SET activo = p_activo
    WHERE id_usuario = p_usuario AND es_admin = 0;
END$$

-- 02. sp_admin_editar_comunidad
DROP PROCEDURE IF EXISTS sp_admin_editar_comunidad$$
CREATE PROCEDURE sp_admin_editar_comunidad(
    IN p_admin INT UNSIGNED, IN p_id INT UNSIGNED, IN p_titulo VARCHAR(150),
    IN p_descripcion TEXT, IN p_privacidad VARCHAR(10)
)
    MODIFIES SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    IF CHAR_LENGTH(TRIM(p_titulo)) < 3 OR p_privacidad NOT IN ('publica', 'privada') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Datos de comunidad invalidos';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM comunidades c WHERE c.id_comunidad = p_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Comunidad inexistente';
    END IF;
    UPDATE comunidades SET titulo = TRIM(p_titulo), descripcion = NULLIF(TRIM(p_descripcion), ''),
           privacidad = p_privacidad, fecha_edicion = NOW()
    WHERE id_comunidad = p_id;
END$$

-- 03. sp_admin_eliminar_comunidad
DROP PROCEDURE IF EXISTS sp_admin_eliminar_comunidad$$
CREATE PROCEDURE sp_admin_eliminar_comunidad(IN p_admin INT UNSIGNED, IN p_id INT UNSIGNED)
    MODIFIES SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM comunidades c WHERE c.id_comunidad = p_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Comunidad inexistente';
    END IF;
    -- Evita hacer públicas publicaciones de comunidades privadas al borrar la comunidad,
    -- ya que la FK publicaciones.id_comunidad utiliza ON DELETE SET NULL.
    IF EXISTS (SELECT 1 FROM publicaciones p WHERE p.id_comunidad = p_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puedes borrar comunidad con publicaciones';
    END IF;
    DELETE FROM comunidades WHERE id_comunidad = p_id;
END$$

-- 04. sp_admin_eliminar_publicacion
DROP PROCEDURE IF EXISTS sp_admin_eliminar_publicacion$$
CREATE PROCEDURE sp_admin_eliminar_publicacion(IN p_admin INT UNSIGNED, IN p_id INT UNSIGNED)
    MODIFIES SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM publicaciones p WHERE p.id_publicacion = p_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Publicacion inexistente';
    END IF;
    -- La clave foránea CASCADE borra likes, comentarios y video asociado.
    -- El registro de recompensa permanece para impedir ganar otra vez el premio.
    DELETE FROM publicaciones WHERE id_publicacion = p_id;
END$$

-- 05. sp_admin_estado_reporte
DROP PROCEDURE IF EXISTS sp_admin_estado_reporte$$
CREATE PROCEDURE sp_admin_estado_reporte(
    IN p_admin INT UNSIGNED, IN p_id INT UNSIGNED, IN p_estado VARCHAR(20)
)
    MODIFIES SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    IF p_estado NOT IN ('pendiente', 'en_revision', 'resuelto', 'descartado') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Estado de reporte no valido';
    END IF;
    UPDATE reportes SET estado = p_estado, fecha_revision = NOW()
    WHERE id_reporte = p_id;
    IF ROW_COUNT() = 0 AND NOT EXISTS (SELECT 1 FROM reportes WHERE id_reporte = p_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Reporte inexistente';
    END IF;
END$$

-- 06. sp_admin_estado_soporte
DROP PROCEDURE IF EXISTS sp_admin_estado_soporte$$
CREATE PROCEDURE sp_admin_estado_soporte(
    IN p_admin INT UNSIGNED, IN p_id INT UNSIGNED, IN p_estado VARCHAR(20)
)
    MODIFIES SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    IF p_estado NOT IN ('pendiente', 'en_revision', 'resuelto') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Estado de soporte no valido';
    END IF;
    UPDATE mensajes_soporte SET estado = p_estado, fecha_revision = NOW()
    WHERE id_mensaje = p_id;
    IF ROW_COUNT() = 0 AND NOT EXISTS (SELECT 1 FROM mensajes_soporte WHERE id_mensaje = p_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mensaje inexistente';
    END IF;
END$$

-- 07. sp_admin_identidad
DROP PROCEDURE IF EXISTS sp_admin_identidad$$
CREATE PROCEDURE sp_admin_identidad(IN p_id INT UNSIGNED)
    READS SQL DATA
BEGIN
    SELECT u.id_usuario, u.nombre_usuario, u.es_admin, u.activo
    FROM usuarios u
    WHERE u.id_usuario = p_id AND u.es_admin = 1 AND u.activo = 1
    LIMIT 1;
END$$

-- 08. sp_admin_listar_comunidades
DROP PROCEDURE IF EXISTS sp_admin_listar_comunidades$$
CREATE PROCEDURE sp_admin_listar_comunidades(IN p_admin INT UNSIGNED, IN p_buscar VARCHAR(80))
    READS SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    SELECT c.id_comunidad, c.slug, c.titulo, c.descripcion, c.privacidad,
           c.total_miembros, c.publicaciones_hoy
    FROM vista_comunidades_detalle c
    WHERE (p_buscar IS NULL OR c.titulo LIKE CONCAT('%', p_buscar, '%')
           OR c.slug LIKE CONCAT('%', p_buscar, '%'))
    ORDER BY c.total_miembros DESC, c.id_comunidad DESC
    LIMIT 150;
END$$

-- 09. sp_admin_listar_publicaciones
DROP PROCEDURE IF EXISTS sp_admin_listar_publicaciones$$
CREATE PROCEDURE sp_admin_listar_publicaciones(
    IN p_admin INT UNSIGNED, IN p_buscar VARCHAR(80), IN p_tipo VARCHAR(20),
    IN p_desde DATE, IN p_hasta DATE
)
    READS SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    SELECT v.id_publicacion, v.nombre_usuario, v.tipo, v.descripcion,
           v.titulo, v.comunidad_titulo, v.fecha_publicacion, v.total_likes,
           v.total_comentarios
    FROM vista_publicaciones_detalle v
    WHERE (p_tipo IS NULL OR v.tipo = p_tipo)
      AND (p_buscar IS NULL OR v.nombre_usuario LIKE CONCAT('%', p_buscar, '%')
           OR v.descripcion LIKE CONCAT('%', p_buscar, '%')
           OR v.titulo LIKE CONCAT('%', p_buscar, '%'))
      AND (p_desde IS NULL OR DATE(v.fecha_publicacion) >= p_desde)
      AND (p_hasta IS NULL OR DATE(v.fecha_publicacion) <= p_hasta)
    ORDER BY v.fecha_publicacion DESC, v.id_publicacion DESC
    LIMIT 150;
END$$

-- 10. sp_admin_listar_reportes
DROP PROCEDURE IF EXISTS sp_admin_listar_reportes$$
CREATE PROCEDURE sp_admin_listar_reportes(
    IN p_admin INT UNSIGNED,
    IN p_motivo VARCHAR(20),
    IN p_estado VARCHAR(20),
    IN p_buscar VARCHAR(80),
    IN p_desde DATE,
    IN p_hasta DATE
)
    READS SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    SELECT v.id_reporte, v.usuario_reportador, v.usuario_reportado,
           v.motivo, v.detalle, v.fecha_reporte, r.estado
    FROM vista_reportes_detalle v
    INNER JOIN reportes r ON r.id_reporte = v.id_reporte
    WHERE (p_motivo IS NULL OR v.motivo = p_motivo)
      AND (p_estado IS NULL OR r.estado = p_estado)
      AND (p_buscar IS NULL OR v.usuario_reportador LIKE CONCAT('%', p_buscar, '%')
           OR v.usuario_reportado LIKE CONCAT('%', p_buscar, '%'))
      AND (p_desde IS NULL OR DATE(v.fecha_reporte) >= p_desde)
      AND (p_hasta IS NULL OR DATE(v.fecha_reporte) <= p_hasta)
    ORDER BY v.fecha_reporte DESC, v.id_reporte DESC
    LIMIT 150;
END$$

-- 11. sp_admin_listar_soporte
DROP PROCEDURE IF EXISTS sp_admin_listar_soporte$$
CREATE PROCEDURE sp_admin_listar_soporte(
    IN p_admin INT UNSIGNED,
    IN p_estado VARCHAR(20),
    IN p_buscar VARCHAR(80),
    IN p_desde DATE,
    IN p_hasta DATE
)
    READS SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    SELECT v.id_mensaje, v.nombre_usuario, v.mensaje, v.fecha_envio, s.estado
    FROM vista_mensajes_soporte v
    INNER JOIN mensajes_soporte s ON s.id_mensaje = v.id_mensaje
    WHERE (p_estado IS NULL OR s.estado = p_estado)
      AND (p_buscar IS NULL OR v.nombre_usuario LIKE CONCAT('%', p_buscar, '%')
           OR v.mensaje LIKE CONCAT('%', p_buscar, '%'))
      AND (p_desde IS NULL OR DATE(v.fecha_envio) >= p_desde)
      AND (p_hasta IS NULL OR DATE(v.fecha_envio) <= p_hasta)
    ORDER BY v.fecha_envio DESC, v.id_mensaje DESC
    LIMIT 150;
END$$

-- 12. sp_admin_listar_usuarios
DROP PROCEDURE IF EXISTS sp_admin_listar_usuarios$$
CREATE PROCEDURE sp_admin_listar_usuarios(IN p_admin INT UNSIGNED, IN p_buscar VARCHAR(80))
    READS SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    SELECT u.id_usuario, u.nombre_usuario, u.correo, u.fecha_registro, u.es_admin, u.activo
    FROM usuarios u
    WHERE (p_buscar IS NULL OR u.nombre_usuario LIKE CONCAT('%', p_buscar, '%')
           OR u.correo LIKE CONCAT('%', p_buscar, '%'))
    ORDER BY u.fecha_registro DESC, u.id_usuario DESC
    LIMIT 150;
END$$

-- 13. sp_admin_resumen
DROP PROCEDURE IF EXISTS sp_admin_resumen$$
CREATE PROCEDURE sp_admin_resumen(IN p_admin INT UNSIGNED)
    READS SQL DATA
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_usuario = p_admin AND u.es_admin = 1 AND u.activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No autorizado';
    END IF;
    SELECT
       (SELECT COUNT(u.id_usuario) FROM usuarios u) AS total_usuarios,
       (SELECT COUNT(p.id_publicacion) FROM publicaciones p) AS total_publicaciones,
       (SELECT COUNT(c.id_comunidad) FROM comunidades c) AS total_comunidades,
       (SELECT COUNT(r.id_reporte) FROM reportes r) AS total_reportes,
       (SELECT COUNT(r2.id_reporte) FROM reportes r2 WHERE r2.estado = 'pendiente') AS reportes_pendientes,
       (SELECT COUNT(s.id_mensaje) FROM mensajes_soporte s WHERE s.estado = 'pendiente') AS soporte_pendiente;
END$$

-- 14. sp_drawu_autenticacion
DROP PROCEDURE IF EXISTS sp_drawu_autenticacion$$
CREATE PROCEDURE sp_drawu_autenticacion(IN p_identificador VARCHAR(150))
    READS SQL DATA
BEGIN
    SELECT
        u.id_usuario, u.contrasena_hash, u.id_mascota_activa,
        u.es_admin, u.activo
    FROM usuarios u
    WHERE u.correo = p_identificador OR u.nombre_usuario = p_identificador
    LIMIT 1;
END$$

-- 15. sp_drawu_obtener_reto_actual
DROP PROCEDURE IF EXISTS sp_drawu_obtener_reto_actual$$
CREATE PROCEDURE sp_drawu_obtener_reto_actual()
    READS SQL DATA
BEGIN
    SELECT
        id_reto, titulo, descripcion, ruta_imagen,
        fecha_creacion, fecha_inicio, fecha_fin
    FROM vista_reto_actual
    LIMIT 1;
END$$

-- 16. sp_drawu_obtener_usuario
DROP PROCEDURE IF EXISTS sp_drawu_obtener_usuario$$
CREATE PROCEDURE sp_drawu_obtener_usuario(IN p_id INT UNSIGNED)
    READS SQL DATA
BEGIN
    SELECT
        v.id_usuario, v.primer_nombre, v.primer_apellido, v.nombre_usuario,
        v.correo, v.imagen_perfil, v.descripcion_perfil, v.tema,
        v.drawings, v.id_mascota_activa,
        v.mascota_nombre, v.mascota_imagen, v.mascota_color,
        v.total_publicaciones, v.total_seguidores, v.total_seguidos,
        u.es_admin, u.activo
    FROM vista_usuario_completo v
    INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
    WHERE v.id_usuario = p_id AND u.activo = 1
    LIMIT 1;
END$$

DELIMITER ;
