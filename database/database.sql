CREATE DATABASE IF NOT EXISTS drawu
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE drawu;

-- 1. Catalogo de mascotas: mascotas
CREATE TABLE mascotas (
    id_mascota INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    ruta_imagen VARCHAR(500) DEFAULT NULL,
    color_tema VARCHAR(20) DEFAULT NULL,
    es_inicial TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_mascota)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 2. Usuarios (incluye activo y es_admin): usuarios
CREATE TABLE usuarios (
    id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
    primer_nombre VARCHAR(80) NOT NULL,
    primer_apellido VARCHAR(80) DEFAULT NULL,
    nombre_usuario VARCHAR(50) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    contrasena_hash VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE DEFAULT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    imagen_perfil VARCHAR(500) DEFAULT NULL,
    descripcion_perfil VARCHAR(500) DEFAULT NULL,
    tema ENUM('claro','oscuro') NOT NULL DEFAULT 'claro',
    drawings INT UNSIGNED NOT NULL DEFAULT 100,
    id_mascota_activa INT UNSIGNED DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    es_admin TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuario_nombre (nombre_usuario),
    UNIQUE KEY uq_usuario_correo (correo),
    KEY fk_usuario_mascota_activa (id_mascota_activa),
    CONSTRAINT fk_usuario_mascota_activa
        FOREIGN KEY (id_mascota_activa) REFERENCES mascotas (id_mascota) ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 3. Comunidades: comunidades
CREATE TABLE comunidades (
    id_comunidad INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_creador INT UNSIGNED NOT NULL,
    slug VARCHAR(80) NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    contenido TEXT DEFAULT NULL,
    privacidad ENUM('publica','privada') NOT NULL DEFAULT 'publica',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    fecha_edicion TIMESTAMP NULL DEFAULT NULL,
    imagen_perfil VARCHAR(500) DEFAULT NULL,
    imagen_banner VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (id_comunidad),
    UNIQUE KEY uq_comunidad_slug (slug),
    KEY fk_comunidad_creador (id_creador),
    CONSTRAINT fk_comunidad_creador
        FOREIGN KEY (id_creador) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 4. Retos del dia: retos_del_dia
CREATE TABLE retos_del_dia (
    id_reto INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(160) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    ruta_imagen VARCHAR(500) DEFAULT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    PRIMARY KEY (id_reto),
    CONSTRAINT chk_reto_fechas
        CHECK (fecha_fin >= fecha_inicio)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 5. Publicaciones: publicaciones
CREATE TABLE publicaciones (
    id_publicacion INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    id_comunidad INT UNSIGNED DEFAULT NULL,
    id_reto INT UNSIGNED DEFAULT NULL,
    tipo ENUM('imagen','video','texto','aviso') NOT NULL DEFAULT 'imagen',
    titulo VARCHAR(180) DEFAULT NULL,
    descripcion TEXT DEFAULT NULL,
    contenido TEXT DEFAULT NULL,
    ruta_imagen VARCHAR(500) DEFAULT NULL,
    fecha_publicacion TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    fecha_edicion TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id_publicacion),
    KEY idx_publicacion_usuario_fecha (id_usuario,fecha_publicacion),
    KEY idx_publicacion_comunidad_fecha (id_comunidad,fecha_publicacion),
    KEY idx_publicacion_reto (id_reto),
    CONSTRAINT fk_publicacion_comunidad
        FOREIGN KEY (id_comunidad) REFERENCES comunidades (id_comunidad) ON DELETE SET NULL,
    CONSTRAINT fk_publicacion_reto
        FOREIGN KEY (id_reto) REFERENCES retos_del_dia (id_reto) ON DELETE SET NULL,
    CONSTRAINT fk_publicacion_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 6. Videos: videos
CREATE TABLE videos (
    id_video INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_publicacion INT UNSIGNED NOT NULL,
    ruta_video VARCHAR(500) NOT NULL,
    duracion_segundos INT UNSIGNED DEFAULT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_video),
    UNIQUE KEY uq_video_publicacion (id_publicacion),
    CONSTRAINT fk_video_publicacion
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id_publicacion) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 7. Colaboradores de publicaciones: colaboradores
CREATE TABLE colaboradores (
    id_publicacion INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_publicacion,id_usuario),
    KEY fk_colaborador_usuario (id_usuario),
    CONSTRAINT fk_colaborador_publicacion
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id_publicacion) ON DELETE CASCADE,
    CONSTRAINT fk_colaborador_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 8. Miembros de comunidades: comunidades_usuarios
CREATE TABLE comunidades_usuarios (
    id_comunidad INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    fecha_union TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_comunidad,id_usuario),
    KEY fk_miembro_usuario (id_usuario),
    CONSTRAINT fk_miembro_comunidad
        FOREIGN KEY (id_comunidad) REFERENCES comunidades (id_comunidad) ON DELETE CASCADE,
    CONSTRAINT fk_miembro_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 9. Comentarios: comentarios
CREATE TABLE comentarios (
    id_comentario INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_publicacion INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    contenido TEXT NOT NULL,
    fecha_comentario TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_comentario),
    KEY idx_comentario_publicacion_fecha (id_publicacion,fecha_comentario),
    KEY fk_comentario_usuario (id_usuario),
    CONSTRAINT fk_comentario_publicacion
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id_publicacion) ON DELETE CASCADE,
    CONSTRAINT fk_comentario_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 10. Me gusta en publicaciones: likes_publicaciones
CREATE TABLE likes_publicaciones (
    id_like INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_publicacion INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    fecha_like TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_like),
    UNIQUE KEY uq_like_usuario_publicacion (id_usuario,id_publicacion),
    KEY fk_like_publicacion (id_publicacion),
    CONSTRAINT fk_like_publicacion
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id_publicacion) ON DELETE CASCADE,
    CONSTRAINT fk_like_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 11. Seguimientos entre usuarios: seguimientos
CREATE TABLE seguimientos (
    id_seguidor INT UNSIGNED NOT NULL,
    id_seguido INT UNSIGNED NOT NULL,
    fecha_seguimiento TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_seguidor,id_seguido),
    KEY fk_seguimiento_seguido (id_seguido),
    CONSTRAINT fk_seguimiento_seguido
        FOREIGN KEY (id_seguido) REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_seguimiento_seguidor
        FOREIGN KEY (id_seguidor) REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT chk_no_autoseguimiento
        CHECK (id_seguidor <> id_seguido)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 12. Articulos de la tienda: articulos
CREATE TABLE articulos (
    id_articulo INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    contenido TEXT DEFAULT NULL,
    tipo ENUM('mascota','accesorio','otro') NOT NULL DEFAULT 'otro',
    id_mascota INT UNSIGNED DEFAULT NULL,
    ruta_imagen VARCHAR(500) DEFAULT NULL,
    precio_drawings INT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_alta TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_articulo),
    KEY fk_articulo_mascota (id_mascota),
    CONSTRAINT fk_articulo_mascota
        FOREIGN KEY (id_mascota) REFERENCES mascotas (id_mascota) ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 13. Compras de la tienda: compras
CREATE TABLE compras (
    id_compra INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    id_articulo INT UNSIGNED NOT NULL,
    precio_pagado INT UNSIGNED NOT NULL,
    fecha_compra TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_compra),
    UNIQUE KEY uq_compra_usuario_articulo (id_usuario,id_articulo),
    KEY fk_compra_articulo (id_articulo),
    CONSTRAINT fk_compra_articulo
        FOREIGN KEY (id_articulo) REFERENCES articulos (id_articulo),
    CONSTRAINT fk_compra_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 14. Mascotas obtenidas por usuarios: usuarios_mascotas
CREATE TABLE usuarios_mascotas (
    id_usuario INT UNSIGNED NOT NULL,
    id_mascota INT UNSIGNED NOT NULL,
    fecha_obtencion TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_usuario,id_mascota),
    KEY fk_usuario_mascota_catalogo (id_mascota),
    CONSTRAINT fk_usuario_mascota_catalogo
        FOREIGN KEY (id_mascota) REFERENCES mascotas (id_mascota),
    CONSTRAINT fk_usuario_mascota_dueno
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 15. Usuarios bloqueados: bloqueos
CREATE TABLE bloqueos (
    id_bloqueador INT UNSIGNED NOT NULL,
    id_bloqueado INT UNSIGNED NOT NULL,
    fecha_bloqueo TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id_bloqueador,id_bloqueado),
    KEY fk_bloqueo_bloqueado (id_bloqueado),
    CONSTRAINT fk_bloqueo_bloqueado
        FOREIGN KEY (id_bloqueado) REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_bloqueo_bloqueador
        FOREIGN KEY (id_bloqueador) REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT chk_no_autobloqueo
        CHECK (id_bloqueador <> id_bloqueado)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 16. Reportes de usuarios (incluye estado): reportes
CREATE TABLE reportes (
    id_reporte INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_reportador INT UNSIGNED NOT NULL,
    id_reportado INT UNSIGNED NOT NULL,
    motivo ENUM('spam','acoso','inapropiado','plagio','otro') NOT NULL,
    detalle VARCHAR(500) DEFAULT NULL,
    fecha_reporte TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    estado ENUM('pendiente','en_revision','resuelto','descartado') NOT NULL DEFAULT 'pendiente',
    fecha_revision DATETIME DEFAULT NULL,
    PRIMARY KEY (id_reporte),
    KEY fk_reporte_reportador (id_reportador),
    KEY fk_reporte_reportado (id_reportado),
    CONSTRAINT fk_reporte_reportado
        FOREIGN KEY (id_reportado) REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_reporte_reportador
        FOREIGN KEY (id_reportador) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 17. Mensajes de soporte (incluye estado): mensajes_soporte
CREATE TABLE mensajes_soporte (
    id_mensaje INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    mensaje VARCHAR(1000) NOT NULL,
    fecha_envio TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    estado ENUM('pendiente','en_revision','resuelto') NOT NULL DEFAULT 'pendiente',
    fecha_revision DATETIME DEFAULT NULL,
    PRIMARY KEY (id_mensaje),
    KEY fk_soporte_usuario (id_usuario),
    CONSTRAINT fk_soporte_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 18. Historial de likes (para triggers): historial_likes
CREATE TABLE historial_likes (
    id_historial BIGINT NOT NULL AUTO_INCREMENT,
    id_publicacion INT NOT NULL,
    id_usuario INT NOT NULL,
    accion VARCHAR(10) NOT NULL,
    fecha DATETIME DEFAULT current_timestamp(),
    PRIMARY KEY (id_historial)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 19. Recompensas otorgadas por reto (para triggers): recompensas_reto
CREATE TABLE recompensas_reto (
    id_usuario INT NOT NULL,
    id_reto INT NOT NULL,
    id_publicacion INT NOT NULL,
    drawings_ganados INT NOT NULL DEFAULT 20,
    fecha_recompensa DATETIME DEFAULT current_timestamp(),
    PRIMARY KEY (id_usuario,id_reto)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
