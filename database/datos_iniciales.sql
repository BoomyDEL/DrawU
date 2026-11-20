-- =====================================================================
-- DRAWU - DATOS INICIALES DE PRUEBA
-- Ejecutar SOLO con una base vacia cuyo esquema, vistas, funciones y
-- triggers ya esten instalados. El archivo de reinicio completo ya
-- incluye estos datos y NO requiere ejecutar este archivo por separado.
--
-- CUENTAS DE PRUEBA (solo entorno local, contrasena: drawu1234):
--   Fulanito | fulanito@drawu.com | mascota Dylan; primer reto premiado
--   Lilly    | lilly@drawu.com    | Boomy; compra de Tommy ya registrada
--   Nube     | nube@drawu.com     | Tommy; puede comprar mascotas
--   Pixel    | pixel@drawu.com    | Dylan; solo 10 Drawings (saldo bajo)
--   Novato   | novato@drawu.com   | sin mascota; probar seleccion inicial
-- =====================================================================
USE drawu;
SET NAMES utf8mb4;
START TRANSACTION;

-- 1. Catalogo de mascotas y tienda (rutas del proyecto original)
INSERT INTO mascotas (id_mascota, nombre, descripcion, ruta_imagen, color_tema, es_inicial) VALUES
 (1, 'Dylan', 'Estilo morado', 'assets/img/personajes/dylan.png', 'morado', TRUE),
 (2, 'Tommy', 'Estilo aqua', 'assets/img/personajes/tommy.png', 'aqua', TRUE),
 (3, 'Boomy', 'Estilo verde', 'assets/img/personajes/boomy.png', 'verde', TRUE);

INSERT INTO articulos (id_articulo, titulo, descripcion, tipo, id_mascota, ruta_imagen, precio_drawings, activo) VALUES
 (1, 'Mascota 1 (Dylan)', 'Estilo morado', 'mascota', 1, 'assets/img/personajes/dylan.png', 0, TRUE),
 (2, 'Mascota 2 (Tommy)', 'Estilo aqua', 'mascota', 2, 'assets/img/personajes/tommy.png', 50, TRUE),
 (3, 'Mascota 3 (Boomy)', 'Estilo verde', 'mascota', 3, 'assets/img/personajes/boomy.png', 100, TRUE);

-- 2. Usuarios de prueba (hash bcrypt comprobado para drawu1234)
INSERT INTO usuarios
 (id_usuario, primer_nombre, nombre_usuario, correo, contrasena_hash,
  descripcion_perfil, tema, drawings, id_mascota_activa)
VALUES
 (1, 'Fulanito', 'Fulanito_detal', 'fulanito@drawu.com', '$2y$10$veN0m5pIQd.RFTpo9TZkUe3SGmTb9JlGrXTyFFzO5G4iU8dkLnt7.',
  'Me encanta dibujar perritos.', 'claro', 100, 1),
 (2, 'Lilly', 'Lilly_art', 'lilly@drawu.com', '$2y$10$veN0m5pIQd.RFTpo9TZkUe3SGmTb9JlGrXTyFFzO5G4iU8dkLnt7.',
  'Diseño de personajes y visemas.', 'oscuro', 180, 3),
 (3, 'Nube', 'Nube_dibuja', 'nube@drawu.com', '$2y$10$veN0m5pIQd.RFTpo9TZkUe3SGmTb9JlGrXTyFFzO5G4iU8dkLnt7.',
  'Practico todos los días.', 'claro', 250, 2),
 (4, 'Pixel', 'Pixel_test', 'pixel@drawu.com', '$2y$10$veN0m5pIQd.RFTpo9TZkUe3SGmTb9JlGrXTyFFzO5G4iU8dkLnt7.',
  'Cuenta para probar saldo insuficiente.', 'claro', 10, 1),
 (5, 'Novato', 'Novato_arte', 'novato@drawu.com', '$2y$10$veN0m5pIQd.RFTpo9TZkUe3SGmTb9JlGrXTyFFzO5G4iU8dkLnt7.',
  'Cuenta sin mascota activa para probar el inicio.', 'claro', 100, NULL);

-- Mascotas iniciales. Novato queda SIN mascota para probar elegir_mascota.php.
INSERT INTO usuarios_mascotas (id_usuario, id_mascota) VALUES
 (1, 1), (2, 3), (3, 2), (4, 1);

-- 3. Comunidades: el TRIGGER agrega automaticamente a cada creador.
INSERT INTO comunidades
 (id_comunidad, id_creador, slug, titulo, descripcion, privacidad,
  imagen_perfil, imagen_banner)
VALUES
 (1, 1, 'boomy', 'Boom día, Boomy!',
  'Comparte dibujos, personajes e ideas.', 'publica',
  'assets/img/comunidades/boomy_lentes.png', 'assets/img/comunidades/boomy_lentes.png'),
 (2, 2, 'redisenos', 'Rediseño de personajes',
  'Comparte tus rediseños.', 'publica',
  'assets/img/comunidades/LSS_Lilly.png', 'assets/img/comunidades/LSS_Lilly.png'),
 (3, 1, 'arte', 'Arte y color',
  'Aprende técnicas y estilos artísticos.', 'publica',
  'assets/img/comunidades/hermanitos.png', 'assets/img/comunidades/hermanitos.png'),
 (4, 2, 'noobs', 'Noobs del dibujo',
  'Aprende junto a otros artistas.', 'publica',
  'assets/img/comunidades/claro_oscuro.png', 'assets/img/comunidades/claro_oscuro.png'),
 (5, 2, 'lss', 'Little Silly Singer',
  'Dibujos y fanart.', 'publica',
  'assets/img/comunidades/LSS_Lilly.png', 'assets/img/comunidades/LSS_Lilly.png'),
 (6, 3, 'taller-privado', 'Taller privado',
  'Comunidad privada para comprobar permisos.', 'privada', NULL, NULL);

-- Unir solo usuarios que NO son el creador.
-- El trigger trg_creador_miembro ya inserto las membresias de los creadores.
INSERT INTO comunidades_usuarios (id_comunidad, id_usuario) VALUES
 (1, 2), (1, 3), (2, 3), (3, 2), (4, 4), (5, 1), (6, 2);

-- 4. Retos: vigente, vencido y futuro (fechas calculadas al ejecutar).
INSERT INTO retos_del_dia
 (id_reto, titulo, descripcion, ruta_imagen, fecha_inicio, fecha_fin)
VALUES
 (1, 'Reto: Kirby paisaje', 'Dibuja a Kirby en un paisaje.', NULL,
  DATE_FORMAT(CURDATE(), '%Y-%m-01'), LAST_DAY(CURDATE())),
 (2, 'Reto vencido', 'Este reto debe rechazar nuevas participaciones.', NULL,
  DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
 (3, 'Reto futuro', 'Este reto todavía no debe aceptar participaciones.', NULL,
  DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 30 DAY));

-- 5. Publicaciones: imagen, texto, aviso, video y reto.
-- Las publicaciones del reto 1 generan +20 Drawings automaticamente
-- para Fulanito, Lilly y Nube mediante el trigger de recompensas.
INSERT INTO publicaciones
 (id_publicacion, id_usuario, id_comunidad, id_reto, tipo,
  titulo, descripcion, ruta_imagen)
VALUES
 (1, 1, 1, NULL, 'imagen', NULL,
  'Mi ilustración de hoy. ¿Qué les parece?', 'assets/img/publis/Luke.png'),
 (2, 2, 1, NULL, 'imagen', NULL,
  'Comparto otro dibujo con la comunidad.', 'assets/img/publis/visemas_lilly.png'),
 (3, 2, 2, NULL, 'imagen', NULL,
  'Probando un nuevo estilo de personaje.', 'assets/img/publis/visemas_lilly.png'),
 (4, 1, 3, NULL, 'imagen', NULL,
  'Práctica de color e iluminación.', 'assets/img/publis/Luke.png'),
 (5, 4, 4, NULL, 'texto', NULL,
  'Hoy practiqué dibujando círculos y sombras.', NULL),
 (6, 2, 5, NULL, 'imagen', NULL,
  'Compartiendo fanart de Little Silly Singer.', 'assets/img/publis/visemas_lilly.png'),
 (7, 1, NULL, 1, 'imagen', NULL,
  'Mi primera participación en el reto actual.', 'assets/img/publis/Luke.png'),
 (8, 2, NULL, 1, 'imagen', NULL,
  'Mi dibujo para el reto actual.', 'assets/img/publis/visemas_lilly.png'),
 (9, 3, 6, NULL, 'texto', NULL,
  'Mensaje privado para miembros del taller.', NULL),
 (10, 3, 1, NULL, 'texto', NULL,
  'Consejo rápido: practica cinco minutos cada día.', NULL),
 (11, 1, 1, NULL, 'aviso', 'Bienvenidos a Boomy',
  'Comparte tus dibujos con respeto.', NULL),
 (12, 3, NULL, NULL, 'video', NULL,
  'Prueba de una publicación de video.', NULL),
 (13, 3, NULL, 1, 'texto', NULL,
  'Mi participación en el reto de hoy.', NULL),
 (14, 4, NULL, NULL, 'texto', NULL,
  'Publicación pública de Pixel para probar bloqueos.', NULL);

-- IMPORTANTE: Esta es una ruta DE PRUEBA: crea o sube un video real
-- con ese nombre si quieres comprobar la reproduccion en la interfaz.
INSERT INTO videos (id_publicacion, ruta_video, duracion_segundos)
VALUES (12, 'uploads/videos/video_demo.mp4', 30);

-- 6. Likes y comentarios (los triggers llenan historial_likes).
INSERT INTO likes_publicaciones (id_publicacion, id_usuario) VALUES
 (1, 2), (1, 3), (2, 1), (7, 2), (7, 3),
 (8, 1), (9, 2), (12, 1), (13, 2), (14, 1);

INSERT INTO comentarios (id_publicacion, id_usuario, contenido) VALUES
 (1, 2, '¡Qué bonito Beagle!'),
 (1, 3, 'Me encantan los colores.'),
 (2, 1, 'Muy buen trabajo.'),
 (7, 2, '¡Suerte con el reto!'),
 (9, 2, 'Solo los miembros deberían ver este mensaje.'),
 (12, 1, 'Espero ver el video pronto.');

-- 7. Seguimientos y bloqueo.
-- Primero Fulanito sigue a Pixel. Despues lo bloquea: el trigger
-- elimina automaticamente el seguimiento (1,4).
INSERT INTO seguimientos (id_seguidor, id_seguido) VALUES
 (2, 1), (1, 3), (3, 2), (4, 2), (1, 4);
INSERT INTO bloqueos (id_bloqueador, id_bloqueado)
VALUES (1, 4);

-- 8. Una compra real de prueba para activar el trigger de desbloqueo:
-- Lilly compra Tommy por 50 Drawings (ya tenia Boomy).
INSERT INTO compras (id_usuario, id_articulo, precio_pagado)
VALUES (2, 2, 50);
-- En el archivo de datos el saldo se ajusta explicitamente; en la
-- aplicacion api/mascota.php realiza el descuento dentro de transaccion.
UPDATE usuarios SET drawings = drawings - 50 WHERE id_usuario = 2;

-- 9. Reportes y mensajes de soporte.
INSERT INTO reportes (id_reportador, id_reportado, motivo, detalle) VALUES
 (1, 4, 'spam', 'Publicación repetida de prueba.'),
 (2, 4, 'plagio', 'Reporte de ejemplo para moderación.');

INSERT INTO mensajes_soporte (id_usuario, mensaje) VALUES
 (1, 'No puedo cambiar mi foto de perfil.'),
 (4, 'Quiero consultar por qué tengo pocos Drawings.'),
 (3, '¿Cómo participo en el reto del día?');

COMMIT;

-- RESULTADOS ESPERADOS DESPUES DE LA CARGA:
-- usuarios 5; mascotas 3; articulos 3; comunidades 6; publicaciones 14;
-- videos 1; likes 10; comentarios 6; bloqueos 1; reportes 2; soporte 3;
-- historiales de like 10; recompensas de reto 3; compras 1;
-- Drawings finales: Fulanito 120, Lilly 150, Nube 270, Pixel 10,
-- Novato 100. Si falla un trigger, estas cifras podrian ser distintas.
