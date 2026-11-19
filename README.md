# DrawU

Plataforma artística conectada a MySQL/MariaDB mediante PDO.

## 1. Preparar la base de datos

Ejecuta en tu gestor MySQL (phpMyAdmin, MySQL Workbench o CLI):

1. **`database/database.sql`** (crea la base de datos `drawu` y todas las tablas).
   - *Nota:* Si ya tenías creada la base de datos con una versión anterior de `database.sql`, ejecuta **`database/actualizacion.sql`** para agregar la columna `drawings` y las tablas `bloqueos`, `reportes` y `mensajes_soporte`.
2. **`database/datos_iniciales.sql`** (inserta el catálogo de mascotas, artículos de tienda, comunidades, reto del día y publicaciones de ejemplo).

### Usuarios de prueba incluidos en `datos_iniciales.sql`
- **Correo:** `fulanito@drawu.com` | **Contraseña:** `drawu1234`
- **Correo:** `lilly@drawu.com` | **Contraseña:** `drawu1234`

## 2. Configurar credenciales de conexión

Por defecto, `views/conexion.php` se conecta con las credenciales estándar de XAMPP:
- Host: `127.0.0.1`
- Puerto: `3306`
- Base de datos: `drawu`
- Usuario: `root`
- Contraseña: *(vacía)*

Si tu servidor MySQL utiliza contraseña u otro puerto, crea el archivo **`config/local.php`** (ignorado por Git):

```php
<?php
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'db'   => 'drawu',
    'user' => 'root',
    'pass' => 'dannifer2018',
];
```
