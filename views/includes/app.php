<?php
/**
 * Núcleo de la aplicación: sesión, usuario actual, utilidades y consultas
 * compartidas por las vistas (views/*.php) y la API (api/*.php).
 *
 * Uso: require_once __DIR__ . '/includes/app.php';   (desde views/)
 *      require_once __DIR__ . '/../views/includes/app.php';   (desde api/)
 */

require_once __DIR__ . '/../conexion.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/** Ruta del proyecto en disco (carpeta raíz de DrawU). */
const DRAWU_RAIZ = __DIR__ . '/../..';

// ---------------------------------------------------------------------
// Utilidades generales
// ---------------------------------------------------------------------

/** Escapa texto para imprimirlo en HTML. */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Convierte una ruta guardada en la BD (relativa a la raíz del proyecto,
 * p. ej. "uploads/publicaciones/x.png") en una URL usable desde /views.
 */
function url(?string $ruta): string
{
    $ruta = trim((string) $ruta);
    if ($ruta === '') {
        return '';
    }
    if (preg_match('#^(https?:|data:|/)#i', $ruta)) {
        return $ruta;
    }
    return '../' . ltrim($ruta, './');
}

/** Clase CSS global según el color de la mascota activa. */
function claseTemaMascota(?string $color): string
{
    return match ($color) {
        'aqua'  => 'tema-pet-aqua',
        'verde' => 'tema-pet-verde',
        default => '',
    };
}

/** Genera un slug único para una comunidad. */
function generarSlug(string $texto): string
{
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $slug));
    $slug = trim($slug, '-') ?: 'comunidad';
    $slug = substr($slug, 0, 60);

    $base = $slug;
    $n = 2;
    $consulta = conexion()->prepare('SELECT 1 FROM comunidades WHERE slug = ?');
    while (true) {
        $consulta->execute([$slug]);
        if (!$consulta->fetchColumn()) {
            return $slug;
        }
        $slug = $base . '-' . $n++;
    }
}

/** Texto corto tipo "67 miembros" / "1 miembro". */
function plural(int $n, string $singular, string $pluralTexto): string
{
    return $n . ' ' . ($n === 1 ? $singular : $pluralTexto);
}

// ---------------------------------------------------------------------
// Sesión y usuario actual
// ---------------------------------------------------------------------

function idUsuarioActual(): ?int
{
    return isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;
}

function iniciarSesionUsuario(int $idUsuario): void
{
    session_regenerate_id(true);
    $_SESSION['id_usuario'] = $idUsuario;
}

function cerrarSesionUsuario(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Devuelve los datos del usuario con sesión iniciada (o null).
 * Incluye la mascota activa y los contadores del perfil.
 */
function usuarioActual(bool $recargar = false): ?array
{
    static $usuario = null;
    $id = idUsuarioActual();
    if ($id === null) {
        return null;
    }
    if ($usuario === null || $recargar) {
        $usuario = obtenerUsuario($id) ?: null;
        if ($usuario === null) {
            cerrarSesionUsuario();
        }
    }
    return $usuario;
}

/** Datos públicos de un usuario + contadores. */

function obtenerUsuario(int $idUsuario): ?array
{
    $sql = 'SELECT *
            FROM vista_usuario_completo
            WHERE id_usuario = ?';

    $consulta = conexion()->prepare($sql);

    $consulta->execute([$idUsuario]);

    return $consulta->fetch() ?: null;
}


/**
 * Protege una vista: si no hay sesión manda al login; si el usuario aún
 * no eligió mascota lo manda a elegir_mascota.php.
 */
function requerirLogin(bool $exigirMascota = true): array
{
    $usuario = usuarioActual();
    if ($usuario === null) {
        header('Location: pagina_login.php');
        exit;
    }
    if ($exigirMascota && empty($usuario['id_mascota_activa'])) {
        header('Location: elegir_mascota.php');
        exit;
    }
    return $usuario;
}

// ---------------------------------------------------------------------
// API JSON
// ---------------------------------------------------------------------

/** Envía una respuesta JSON y termina la ejecución. */
function responder(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function responderError(string $mensaje, int $codigo = 400): void
{
    responder(['ok' => false, 'error' => $mensaje], $codigo);
}

/**
 * Inicializa un endpoint de la API: exige sesión y (opcionalmente) POST.
 * Convierte cualquier excepción en un error JSON.
 */
function iniciarApi(bool $soloPost = true): array
{
    set_exception_handler(function (Throwable $ex) {
        error_log('[DrawU API] ' . $ex->getMessage());
        responderError($ex instanceof RuntimeException ? $ex->getMessage() : 'Error interno del servidor.', 500);
    });

    $usuario = usuarioActual();
    if ($usuario === null) {
        responderError('Tu sesión expiró. Vuelve a iniciar sesión.', 401);
    }
    if ($soloPost && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        responderError('Método no permitido.', 405);
    }
    return $usuario;
}

function entradaEntero(string $clave): int
{
    return (int) ($_POST[$clave] ?? $_GET[$clave] ?? 0);
}

function entradaTexto(string $clave, int $maximo = 0): string
{
    $valor = trim((string) ($_POST[$clave] ?? $_GET[$clave] ?? ''));
    return $maximo > 0 ? mb_substr($valor, 0, $maximo) : $valor;
}

// ---------------------------------------------------------------------
// Subida de archivos
// ---------------------------------------------------------------------

const TIPOS_IMAGEN = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/gif' => 'gif'];
const TIPOS_VIDEO  = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/ogg' => 'ogv', 'video/quicktime' => 'mov'];

/**
 * Guarda un archivo subido en uploads/<carpeta>/ y devuelve su ruta
 * relativa a la raíz del proyecto, o null si no se envió archivo.
 *
 * @throws RuntimeException si el archivo no es válido.
 */
function guardarArchivoSubido(string $campo, string $carpeta, array $tiposPermitidos, int $maxBytes): ?string
{
    if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $archivo = $_FILES[$campo];
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir el archivo (código ' . $archivo['error'] . ').');
    }
    if ($archivo['size'] > $maxBytes) {
        throw new RuntimeException('El archivo supera el tamaño máximo de ' . round($maxBytes / 1048576) . ' MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset($tiposPermitidos[$mime])) {
        throw new RuntimeException('Tipo de archivo no permitido.');
    }

    $relativa = 'uploads/' . $carpeta;
    $destino = DRAWU_RAIZ . '/' . $relativa;
    if (!is_dir($destino) && !mkdir($destino, 0775, true)) {
        throw new RuntimeException('No se pudo crear la carpeta de subidas.');
    }

    $nombre = bin2hex(random_bytes(12)) . '.' . $tiposPermitidos[$mime];
    if (!move_uploaded_file($archivo['tmp_name'], $destino . '/' . $nombre)) {
        throw new RuntimeException('No se pudo guardar el archivo.');
    }
    return $relativa . '/' . $nombre;
}

// ---------------------------------------------------------------------
// Consultas compartidas
// ---------------------------------------------------------------------

/**
 * Publicaciones con autor, contadores y estado para el usuario actual.
 *
 * Filtros soportados:
 *   id_comunidad, id_usuario, id_reto, tipos (array), retos_desde (fecha Y-m-d),
 *   solo_con_imagen (bool), orden ('recientes' | 'likes')
 */

function obtenerPublicaciones(array $filtros = [], int $limite = 20): array
{
    $yo = idUsuarioActual() ?? 0;

    // Seguridad: bloqueos y comunidades privadas
    $donde = [
        'NOT EXISTS (
            SELECT 1 FROM bloqueos b
            WHERE b.id_bloqueador = :yo_b
            AND b.id_bloqueado = p.id_usuario
        )',

        "(p.id_comunidad IS NULL
          OR p.comunidad_privacidad = 'publica'
          OR EXISTS (
              SELECT 1 FROM comunidades_usuarios cu
              WHERE cu.id_comunidad = p.id_comunidad
              AND cu.id_usuario = :yo_m
          ))"
    ];

    $parametros = [
        ':yo_b' => $yo,
        ':yo_m' => $yo,
        ':yo_l' => $yo,
        ':yo_s' => $yo
    ];

    // Filtros por ID
    foreach (['id_comunidad', 'id_usuario', 'id_reto'] as $campo) {
        if (!empty($filtros[$campo])) {
            $donde[] = "p.$campo = :$campo";
            $parametros[":$campo"] = (int) $filtros[$campo];
        }
    }

    // Filtro por fecha
    if (!empty($filtros['retos_desde'])) {
        $donde[] = 'p.id_reto IS NOT NULL
                    AND p.fecha_publicacion >= :fecha';

        $parametros[':fecha'] = $filtros['retos_desde'];
    }

    // Filtro por imagen
    if (!empty($filtros['solo_con_imagen'])) {
        $donde[] = "p.ruta_imagen IS NOT NULL
                    AND p.ruta_imagen <> ''";
    }

    // Filtro por tipo
    if (!empty($filtros['tipos'])) {
        $marcas = [];

        foreach (array_values($filtros['tipos']) as $i => $tipo) {
            $clave = ":tipo$i";
            $marcas[] = $clave;
            $parametros[$clave] = $tipo;
        }

        $donde[] = 'p.tipo IN (' . implode(',', $marcas) . ')';
    }

    // Orden de publicaciones
    $orden = ($filtros['orden'] ?? 'recientes') === 'likes'
        ? 'p.total_likes DESC, p.fecha_publicacion DESC'
        : 'p.fecha_publicacion DESC, p.id_publicacion DESC';

    $limite = max(1, (int) $limite);

    // Consulta principal utilizando la VIEW
    $sql = "SELECT
                p.*,

                EXISTS (
                    SELECT 1 FROM likes_publicaciones l
                    WHERE l.id_publicacion = p.id_publicacion
                    AND l.id_usuario = :yo_l
                ) AS liked,

                EXISTS (
                    SELECT 1 FROM seguimientos s
                    WHERE s.id_seguidor = :yo_s
                    AND s.id_seguido = p.id_usuario
                ) AS siguiendo

            FROM vista_publicaciones_detalle p

            WHERE " . implode(' AND ', $donde) . "

            ORDER BY $orden
            LIMIT $limite";

    $consulta = conexion()->prepare($sql);
    $consulta->execute($parametros);

    return $consulta->fetchAll();
}


/**
 * Comunidades con número de miembros, publicaciones de hoy y si el
 * usuario actual es miembro.
 *
 * Opciones: busqueda (texto), id_miembro (solo comunidades de ese usuario),
 *           limite (int)
 */

function obtenerComunidades(array $opciones = []): array
{
    $yo = idUsuarioActual() ?? 0;

    $donde = [];
    $parametros = [':yo' => $yo];

    // Buscar comunidades por nombre o descripción
    if (!empty($opciones['busqueda'])) {
        $donde[] = '(c.titulo LIKE :q1 OR c.descripcion LIKE :q2)';

        $parametros[':q1'] = '%' . $opciones['busqueda'] . '%';
        $parametros[':q2'] = '%' . $opciones['busqueda'] . '%';
    }

    // Filtrar comunidades a las que pertenece un usuario
    if (!empty($opciones['id_miembro'])) {
        $donde[] = 'EXISTS (
            SELECT 1 FROM comunidades_usuarios m
            WHERE m.id_comunidad = c.id_comunidad
            AND m.id_usuario = :id_miembro
        )';

        $parametros[':id_miembro'] = (int) $opciones['id_miembro'];
    }

    // Consulta utilizando la VIEW
    $sql = 'SELECT
                c.*,

                EXISTS (
                    SELECT 1 FROM comunidades_usuarios cu
                    WHERE cu.id_comunidad = c.id_comunidad
                    AND cu.id_usuario = :yo
                ) AS es_miembro

            FROM vista_comunidades_detalle c';

    // Agregar filtros cuando existan
    if (!empty($donde)) {
        $sql .= ' WHERE ' . implode(' AND ', $donde);
    }

    // Ordenar por número de miembros
    $sql .= ' ORDER BY c.total_miembros DESC,
                      c.fecha_creacion DESC';

    // Limitar resultados
    if (!empty($opciones['limite'])) {
        $sql .= ' LIMIT ' . max(1, (int) $opciones['limite']);
    }

    $consulta = conexion()->prepare($sql);
    $consulta->execute($parametros);

    return $consulta->fetchAll();
}

/** Reto vigente hoy (o el más reciente si no hay uno vigente). */

function obtenerRetoActual(): ?array
{
    return conexion()
        ->query('SELECT * FROM vista_reto_actual')
        ->fetch() ?: null;
}

