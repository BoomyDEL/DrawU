-- =====================================================================
-- Actualizacion para bases de datos "drawu" creadas con la version
-- anterior de database.sql. Si creas la base desde cero con el
-- database.sql actual NO necesitas ejecutar este archivo.
-- =====================================================================
USE drawu;

-- Saldo de Drawings (monedas) del usuario
SET @existe := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'drawu' AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'drawings'
);
SET @sql := IF(@existe = 0,
    'ALTER TABLE usuarios ADD COLUMN drawings INT UNSIGNED NOT NULL DEFAULT 100 AFTER tema',
    'SELECT ''columna drawings ya existe''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS bloqueos (
    id_bloqueador INT UNSIGNED NOT NULL,
    id_bloqueado INT UNSIGNED NOT NULL,
    fecha_bloqueo TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_bloqueador, id_bloqueado),
    CONSTRAINT chk_no_autobloqueo CHECK (id_bloqueador <> id_bloqueado),
    CONSTRAINT fk_bloqueo_bloqueador
        FOREIGN KEY (id_bloqueador) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_bloqueo_bloqueado
        FOREIGN KEY (id_bloqueado) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reportes (
    id_reporte INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_reportador INT UNSIGNED NOT NULL,
    id_reportado INT UNSIGNED NOT NULL,
    motivo ENUM('spam', 'acoso', 'inapropiado', 'plagio', 'otro') NOT NULL,
    detalle VARCHAR(500) NULL,
    fecha_reporte TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reporte_reportador
        FOREIGN KEY (id_reportador) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_reporte_reportado
        FOREIGN KEY (id_reportado) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mensajes_soporte (
    id_mensaje INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    mensaje VARCHAR(1000) NOT NULL,
    fecha_envio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_soporte_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;
