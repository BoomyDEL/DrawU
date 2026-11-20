
CREATE TRIGGER trg_like_agregado

AFTER INSERT ON likes_publicaciones

FOR EACH ROW

INSERT INTO historial_likes (
    id_publicacion,
    id_usuario,
    accion
)
VALUES (
    NEW.id_publicacion,
    NEW.id_usuario,
    'agregado'
);


CREATE TRIGGER trg_like_quitado

AFTER DELETE ON likes_publicaciones

FOR EACH ROW

INSERT INTO historial_likes (
    id_publicacion,
    id_usuario,
    accion
)
VALUES (
    OLD.id_publicacion,
    OLD.id_usuario,
    'quitado'
);


CREATE TRIGGER trg_bloqueo_deja_de_seguir

AFTER INSERT ON bloqueos

FOR EACH ROW

DELETE FROM seguimientos
WHERE id_seguidor = NEW.id_bloqueador
  AND id_seguido = NEW.id_bloqueado;


CREATE TRIGGER trg_creador_miembro
AFTER INSERT ON comunidades
FOR EACH ROW

INSERT INTO comunidades_usuarios (
    id_comunidad,
    id_usuario
)
VALUES (
    NEW.id_comunidad,
    NEW.id_creador
);


CREATE TRIGGER trg_compra_desbloquea_mascota

AFTER INSERT ON compras

FOR EACH ROW

INSERT INTO usuarios_mascotas (
    id_usuario,
    id_mascota
)

SELECT
    NEW.id_usuario,
    a.id_mascota

FROM articulos a

WHERE a.id_articulo = NEW.id_articulo
  AND a.tipo = 'mascota'
  AND a.id_mascota IS NOT NULL;
  

DELIMITER $$

CREATE TRIGGER trg_validar_perfil
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN

    -- Validar el nombre cuando cambie
    IF NOT (OLD.nombre_usuario <=> NEW.nombre_usuario)
       AND (
           NEW.nombre_usuario IS NULL
           OR NEW.nombre_usuario NOT REGEXP '^[A-Za-z0-9_.]{3,50}$'
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Nombre de usuario no valido';
    END IF;

    -- Validar el tema cuando cambie
    IF NOT (OLD.tema <=> NEW.tema)
       AND (
           NEW.tema IS NULL
           OR NEW.tema NOT IN ('claro', 'oscuro')
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Tema de perfil no valido';
    END IF;

END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER trg_recompensa_primera_publicacion_reto

AFTER INSERT ON publicaciones

FOR EACH ROW

BEGIN

    IF NEW.id_reto IS NOT NULL
       AND EXISTS (
           SELECT 1
           FROM retos_del_dia r
           WHERE r.id_reto = NEW.id_reto
             AND CURDATE() BETWEEN r.fecha_inicio AND r.fecha_fin
       )
    THEN

        -- Registrar recompensa solo una vez por reto
        INSERT IGNORE INTO recompensas_reto (
            id_usuario,
            id_reto,
            id_publicacion,
            drawings_ganados
        )
        VALUES (
            NEW.id_usuario,
            NEW.id_reto,
            NEW.id_publicacion,
            20
        );

        -- Solo si se registró una recompensa nueva
        IF ROW_COUNT() = 1 THEN

            UPDATE usuarios
            SET drawings = drawings + 20
            WHERE id_usuario = NEW.id_usuario;

        END IF;

    END IF;

END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER trg_impedir_seguir_bloqueado

BEFORE INSERT ON seguimientos

FOR EACH ROW

BEGIN

    IF NEW.id_seguidor = NEW.id_seguido THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No puedes seguirte a ti mismo';

    END IF;

    IF EXISTS (
        SELECT 1
        FROM bloqueos
        WHERE id_bloqueador = NEW.id_seguidor
          AND id_bloqueado = NEW.id_seguido
    ) THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Desbloquea al usuario antes de seguirlo';

    END IF;

END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER trg_impedir_autoreporte

BEFORE INSERT ON reportes

FOR EACH ROW

BEGIN

    IF NEW.id_reportador = NEW.id_reportado THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No puedes reportarte a ti mismo';

    END IF;

END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER trg_validar_mensaje_soporte

BEFORE INSERT ON mensajes_soporte

FOR EACH ROW

BEGIN

    IF NEW.mensaje IS NULL
       OR CHAR_LENGTH(TRIM(NEW.mensaje)) = 0
    THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El mensaje de soporte no puede estar vacio';

    END IF;

    SET NEW.mensaje = TRIM(NEW.mensaje);

END$$

DELIMITER ;








