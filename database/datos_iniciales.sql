-- =====================================================================
-- Datos iniciales de DrawU (catalogos + contenido de ejemplo).
-- Ejecutar DESPUES de database.sql.
-- Usuarios de prueba (contrasena para todos: drawu1234):
--   fulanito@drawu.com  /  lilly@drawu.com
-- =====================================================================
USE drawu;

-- Mascotas (las rutas son relativas a la raiz del proyecto)
INSERT INTO mascotas (id_mascota, nombre, descripcion, ruta_imagen, color_tema, es_inicial) VALUES
    (1, 'Dylan', 'ESTILO MORADO', 'assets/img/personajes/dylan.png', 'morado', TRUE),
    (2, 'Tommy', 'ESTILO AQUA',   'assets/img/personajes/tommy.png', 'aqua',   TRUE),
    (3, 'Boomy', 'ESTILO VERDE',  'assets/img/personajes/boomy.png', 'verde',  TRUE);

-- Tienda: estilos de mascota que se canjean con Drawings
INSERT INTO articulos (titulo, descripcion, tipo, id_mascota, ruta_imagen, precio_drawings) VALUES
    ('Mascota 1 (Dylan)', 'Estilo morado', 'mascota', 1, 'assets/img/personajes/dylan.png', 0),
    ('Mascota 2 (Tommy)', 'Estilo aqua',   'mascota', 2, 'assets/img/personajes/tommy.png', 50),
    ('Mascota 3 (Boomy)', 'Estilo verde',  'mascota', 3, 'assets/img/personajes/boomy.png', 100);

-- Usuarios de ejemplo (contrasena: drawu1234)
INSERT INTO usuarios (id_usuario, primer_nombre, nombre_usuario, correo, contrasena_hash, descripcion_perfil, id_mascota_activa) VALUES
    (1, 'Fulanito', 'Fulanito_detal', 'fulanito@drawu.com', '$2y$10$veN0m5pIQd.RFTpo9TZkUe3SGmTb9JlGrXTyFFzO5G4iU8dkLnt7.', 'Me encanta dibujar perritos.', 1),
    (2, 'Lilly',    'Lilly_art',      'lilly@drawu.com',    '$2y$10$veN0m5pIQd.RFTpo9TZkUe3SGmTb9JlGrXTyFFzO5G4iU8dkLnt7.', 'Diseño de personajes y visemas.', 3);

INSERT INTO usuarios_mascotas (id_usuario, id_mascota) VALUES (1, 1), (2, 3);

-- Comunidades
INSERT INTO comunidades (id_comunidad, id_creador, slug, titulo, descripcion, privacidad, imagen_perfil, imagen_banner) VALUES
    (1, 1, 'boomy',     'Boom día, Boomy!',       'Comparte tus dibujos, personajes e ideas con otros artistas.', 'publica', 'assets/img/comunidades/boomy_lentes.png', 'assets/img/comunidades/boomy_lentes.png'),
    (2, 2, 'redisenos', 'Rediseño de personajes', 'Comparte rediseños de tus personajes favoritos.',             'publica', 'assets/img/comunidades/LSS_Lilly.png',    'assets/img/comunidades/LSS_Lilly.png'),
    (3, 1, 'arte',      'Arte y color',           'Descubre nuevas técnicas y estilos artísticos.',              'publica', 'assets/img/comunidades/hermanitos.png',   'assets/img/comunidades/hermanitos.png'),
    (4, 2, 'noobs',     'Noobs del dibujo',       'Aprende y mejora junto a otros artistas.',                    'publica', 'assets/img/comunidades/claro_oscuro.png', 'assets/img/comunidades/claro_oscuro.png'),
    (5, 2, 'lss',       'Little Silly Singer',    'Dibujos y personajes inspirados en Little Silly Singer.',     'publica', 'assets/img/comunidades/LSS_Lilly.png',    'assets/img/comunidades/LSS_Lilly.png');

INSERT INTO comunidades_usuarios (id_comunidad, id_usuario) VALUES
    (1, 1), (3, 1), (2, 2), (4, 2), (5, 2), (1, 2);

-- Reto del dia (vigente durante todo el mes actual)
INSERT INTO retos_del_dia (id_reto, titulo, descripcion, ruta_imagen, fecha_inicio, fecha_fin) VALUES
    (1, 'Reto: Kirby paisaje', 'Dibuja a Kirby en un paisaje de tu elección.', NULL,
     DATE_FORMAT(CURDATE(), '%Y-%m-01'), LAST_DAY(CURDATE()));

-- Publicaciones
INSERT INTO publicaciones (id_usuario, id_comunidad, id_reto, tipo, descripcion, ruta_imagen) VALUES
    (1, 1,    NULL, 'imagen', 'Mi ilustración de hoy. ¿Qué les parece?',       'assets/img/publis/Luke.png'),
    (2, 1,    NULL, 'imagen', 'Comparto otro dibujo con la comunidad.',        'assets/img/publis/visemas_lilly.png'),
    (2, 2,    NULL, 'imagen', 'Probando un nuevo estilo de personaje.',        'assets/img/publis/visemas_lilly.png'),
    (1, 3,    NULL, 'imagen', 'Práctica de color e iluminación.',              'assets/img/publis/Luke.png'),
    (2, 4,    NULL, 'imagen', 'Mi práctica de dibujo de hoy.',                 'assets/img/publis/Luke.png'),
    (2, 5,    NULL, 'imagen', 'Compartiendo fanart de Little Silly Singer.',   'assets/img/publis/visemas_lilly.png'),
    (1, NULL, 1,    'imagen', 'Reviviendo los recuerdos de un lindo Beagle.', 'assets/img/publis/Luke.png'),
    (2, NULL, 1,    'imagen', 'Mi participación en el reto.',                  'assets/img/publis/visemas_lilly.png');

INSERT INTO likes_publicaciones (id_publicacion, id_usuario) VALUES (1, 2), (7, 2), (8, 1);
INSERT INTO comentarios (id_publicacion, id_usuario, contenido) VALUES
    (1, 2, '¡Qué bonito Beagle!'),
    (2, 1, 'Me encantan los colores.');
INSERT INTO seguimientos (id_seguidor, id_seguido) VALUES (2, 1);
