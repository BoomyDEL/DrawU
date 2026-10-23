
CREATE DATABASE IF NOT EXISTS drawu
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE drawu;

-- 1. NUEVA: catalogo de mascotas
CREATE TABLE mascotas (
    id_mascota INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    ruta_imagen VARCHAR(500) NULL,
    color_tema VARCHAR(20) NULL,
    es_inicial BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB;

-- 2. ORIGINAL: usuarios
CREATE TABLE usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    primer_nombre VARCHAR(80) NOT NULL,
    primer_apellido VARCHAR(80) NULL,
    nombre_usuario VARCHAR(50) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    contrasena_hash VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    imagen_perfil VARCHAR(500) NULL,
    descripcion_perfil VARCHAR(500) NULL,
    tema ENUM('claro', 'oscuro') NOT NULL DEFAULT 'claro',
    id_mascota_activa INT UNSIGNED NULL,
    CONSTRAINT uq_usuario_nombre UNIQUE (nombre_usuario),
    CONSTRAINT uq_usuario_correo UNIQUE (correo),
    CONSTRAINT fk_usuario_mascota_activa
        FOREIGN KEY (id_mascota_activa) REFERENCES mascotas(id_mascota)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3. ORIGINAL: comunidades
CREATE TABLE comunidades (
    id_comunidad INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_creador INT UNSIGNED NOT NULL,
    slug VARCHAR(80) NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    contenido TEXT NULL,
    privacidad ENUM('publica', 'privada') NOT NULL DEFAULT 'publica',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_edicion TIMESTAMP NULL DEFAULT NULL,
    imagen_perfil VARCHAR(500) NULL,
    imagen_banner VARCHAR(500) NULL,
    CONSTRAINT uq_comunidad_slug UNIQUE (slug),
    CONSTRAINT fk_comunidad_creador
        FOREIGN KEY (id_creador) REFERENCES usuarios(id_usuario)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. ORIGINAL: reto del dia
CREATE TABLE retos_del_dia (
    id_reto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(160) NOT NULL,
    descripcion TEXT NULL,
    ruta_imagen VARCHAR(500) NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    CONSTRAINT chk_reto_fechas CHECK (fecha_fin >= fecha_inicio)
) ENGINE=InnoDB;

-- 5. ORIGINAL: publicaciones
CREATE TABLE publicaciones (
    id_publicacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_comunidad INT UNSIGNED NULL,
    id_reto INT UNSIGNED NULL,
    tipo ENUM('imagen', 'video', 'texto', 'aviso') NOT NULL DEFAULT 'imagen',
    titulo VARCHAR(180) NULL,
    descripcion TEXT NULL,
    contenido TEXT NULL,
    ruta_imagen VARCHAR(500) NULL,
    fecha_publicacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_edicion TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_publicacion_usuario_fecha (id_usuario, fecha_publicacion),
    INDEX idx_publicacion_comunidad_fecha (id_comunidad, fecha_publicacion),
    INDEX idx_publicacion_reto (id_reto),
    CONSTRAINT fk_publicacion_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_publicacion_comunidad
        FOREIGN KEY (id_comunidad) REFERENCES comunidades(id_comunidad)
        ON DELETE SET NULL,
    CONSTRAINT fk_publicacion_reto
        FOREIGN KEY (id_reto) REFERENCES retos_del_dia(id_reto)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. ORIGINAL: videos (maximo uno por publicacion)
CREATE TABLE videos (
    id_video INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_publicacion INT UNSIGNED NOT NULL,
    ruta_video VARCHAR(500) NOT NULL,
    duracion_segundos INT UNSIGNED NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_video_publicacion UNIQUE (id_publicacion),
    CONSTRAINT fk_video_publicacion
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. ORIGINAL: colaboradores (relacion muchos a muchos)
CREATE TABLE colaboradores (
    id_publicacion INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_publicacion, id_usuario),
    CONSTRAINT fk_colaborador_publicacion
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion)
        ON DELETE CASCADE,
    CONSTRAINT fk_colaborador_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. ORIGINAL: miembros de comunidades (muchos a muchos)
CREATE TABLE comunidades_usuarios (
    id_comunidad INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    fecha_union TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_comunidad, id_usuario),
    CONSTRAINT fk_miembro_comunidad
        FOREIGN KEY (id_comunidad) REFERENCES comunidades(id_comunidad)
        ON DELETE CASCADE,
    CONSTRAINT fk_miembro_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. ORIGINAL: comentarios (sin likes a comentarios)
CREATE TABLE comentarios (
    id_comentario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_publicacion INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    contenido TEXT NOT NULL,
    fecha_comentario TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_comentario_publicacion_fecha (id_publicacion, fecha_comentario),
    CONSTRAINT fk_comentario_publicacion
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion)
        ON DELETE CASCADE,
    CONSTRAINT fk_comentario_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. ORIGINAL: likes (solo de publicaciones)
CREATE TABLE likes_publicaciones (
    id_like INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_publicacion INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    fecha_like TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_like_usuario_publicacion UNIQUE (id_usuario, id_publicacion),
    CONSTRAINT fk_like_publicacion
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion)
        ON DELETE CASCADE,
    CONSTRAINT fk_like_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE seguimientos (
    id_seguidor INT UNSIGNED NOT NULL,
    id_seguido INT UNSIGNED NOT NULL,
    fecha_seguimiento TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_seguidor, id_seguido),
    CONSTRAINT chk_no_autoseguimiento CHECK (id_seguidor <> id_seguido),
    CONSTRAINT fk_seguimiento_seguidor
        FOREIGN KEY (id_seguidor) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_seguimiento_seguido
        FOREIGN KEY (id_seguido) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- 12. ORIGINAL: articulos de la tienda
CREATE TABLE articulos (
    id_articulo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    contenido TEXT NULL,
    tipo ENUM('mascota', 'accesorio', 'otro') NOT NULL DEFAULT 'otro',
    id_mascota INT UNSIGNED NULL,
    ruta_imagen VARCHAR(500) NULL,
    precio_drawings INT UNSIGNED NOT NULL DEFAULT 0,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_alta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_articulo_mascota
        FOREIGN KEY (id_mascota) REFERENCES mascotas(id_mascota)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- 13. ORIGINAL: compras
CREATE TABLE compras (
    id_compra INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_articulo INT UNSIGNED NOT NULL,
    precio_pagado INT UNSIGNED NOT NULL,
    fecha_compra TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_compra_usuario_articulo UNIQUE (id_usuario, id_articulo),
    CONSTRAINT fk_compra_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_compra_articulo
        FOREIGN KEY (id_articulo) REFERENCES articulos(id_articulo)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE usuarios_mascotas (
    id_usuario INT UNSIGNED NOT NULL,
    id_mascota INT UNSIGNED NOT NULL,
    fecha_obtencion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_mascota),
    CONSTRAINT fk_usuario_mascota_dueno
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_usuario_mascota_catalogo
        FOREIGN KEY (id_mascota) REFERENCES mascotas(id_mascota)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

SHOW TABLES;
