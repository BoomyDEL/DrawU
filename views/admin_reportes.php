<?php

require_once __DIR__ . '/includes/app.php';

$idSesion = idUsuarioActual();
if ($idSesion === null) {
    header('Location: pagina_login.php');
    exit;
}

/** Capa de acceso a datos: solo invoca procedimientos permitidos. */
final class AdminRepositorio
{
    private PDO $pdo;
    private const PERMITIDOS = [
        'sp_admin_identidad', 'sp_admin_resumen', 'sp_admin_listar_reportes',
        'sp_admin_listar_soporte', 'sp_admin_listar_publicaciones',
        'sp_admin_listar_comunidades', 'sp_admin_listar_usuarios',
        'sp_admin_estado_reporte', 'sp_admin_estado_soporte',
        'sp_admin_eliminar_publicacion', 'sp_admin_editar_comunidad',
        'sp_admin_eliminar_comunidad', 'sp_admin_cambiar_estado_usuario'
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function llamar(string $nombre, array $parametros = []): array
    {
        if (!in_array($nombre, self::PERMITIDOS, true)) {
            throw new InvalidArgumentException('Procedimiento no permitido.');
        }
        $marcadores = implode(', ', array_fill(0, count($parametros), '?'));
        $consulta = $this->pdo->prepare("CALL {$nombre}({$marcadores})");
        try {
            $consulta->execute($parametros);
            return $consulta->columnCount() > 0 ? $consulta->fetchAll(PDO::FETCH_ASSOC) : [];
        } finally {
            // Imprescindible antes de llamar otro PROCEDURE con la misma conexión.
            $consulta->closeCursor();
        }
    }
}

function campoBuscar(string $clave, int $max = 80): ?string
{
    $valor = trim((string) ($_GET[$clave] ?? ''));
    return $valor === '' ? null : mb_substr($valor, 0, $max);
}

function opcionFiltro(string $clave, array $permitidos): ?string
{
    $valor = trim((string) ($_GET[$clave] ?? ''));
    return in_array($valor, $permitidos, true) ? $valor : null;
}

function fechaFiltro(string $clave): ?string
{
    $valor = (string) ($_GET[$clave] ?? '');
    if ($valor === '') {
        return null;
    }
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
    return $fecha && $fecha->format('Y-m-d') === $valor ? $valor : null;
}

function texto($dato): string
{
    return htmlspecialchars((string) ($dato ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function estadoEtiqueta(string $estado): string
{
    return match ($estado) {
        'resuelto' => 'Resuelto',
        'descartado' => 'Descartado',
        'en_revision' => 'En revisión',
        default => 'Pendiente',
    };
}

$repo = new AdminRepositorio(conexion());

// La autorización se verifica desde MySQL en CADA petición.
try {
    $identidad = $repo->llamar('sp_admin_identidad', [(int) $idSesion])[0] ?? null;
} catch (Throwable $ex) {
    error_log('[DrawU admin identidad] ' . $ex->getMessage());
    http_response_code(503);
    exit('No se pudo verificar el permiso. Instala primero admin_procedimientos.sql.');
}

if (!$identidad || (int) $identidad['es_admin'] !== 1) {
    http_response_code(403);
    exit('403 - Acceso denegado. Esta página requiere permisos de administrador.');
}

if (!isset($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}
$csrf = (string) $_SESSION['csrf_admin'];
$mensaje = '';
$error = '';

// Acciones administrativas (solo por POST + CSRF).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenRecibido = (string) ($_POST['csrf'] ?? '');
    if (!hash_equals($csrf, $tokenRecibido)) {
        http_response_code(403);
        exit('Solicitud rechazada: token de seguridad no válido.');
    }

    $accion = (string) ($_POST['accion'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        $error = 'Selecciona un registro válido.';
    } else {
        try {
            $id = (int) $id;
            switch ($accion) {
                case 'reporte_estado':
                    $nuevo = (string) ($_POST['estado'] ?? '');
                    if (!in_array($nuevo, ['pendiente', 'en_revision', 'resuelto', 'descartado'], true)) {
                        throw new InvalidArgumentException('Estado de reporte no válido.');
                    }
                    $repo->llamar('sp_admin_estado_reporte', [$idSesion, $id, $nuevo]);
                    break;
                case 'soporte_estado':
                    $nuevo = (string) ($_POST['estado'] ?? '');
                    if (!in_array($nuevo, ['pendiente', 'en_revision', 'resuelto'], true)) {
                        throw new InvalidArgumentException('Estado de soporte no válido.');
                    }
                    $repo->llamar('sp_admin_estado_soporte', [$idSesion, $id, $nuevo]);
                    break;
                case 'publicacion_eliminar':
                    if (($_POST['confirmacion'] ?? '') !== 'ELIMINAR') {
                        throw new InvalidArgumentException('Debes confirmar la eliminación.');
                    }
                    $repo->llamar('sp_admin_eliminar_publicacion', [$idSesion, $id]);
                    break;
                case 'comunidad_editar':
                    $titulo = trim((string) ($_POST['titulo'] ?? ''));
                    $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
                    $privacidad = (string) ($_POST['privacidad'] ?? '');
                    if (mb_strlen($titulo) < 3 || mb_strlen($titulo) > 150 ||
                        mb_strlen($descripcion) > 1000 ||
                        !in_array($privacidad, ['publica', 'privada'], true)) {
                        throw new InvalidArgumentException('Revisa el título, la descripción o la privacidad.');
                    }
                    $repo->llamar('sp_admin_editar_comunidad', [$idSesion, $id, $titulo, $descripcion, $privacidad]);
                    break;
                case 'usuario_estado':
                    $activo = (string) ($_POST['activo'] ?? '');
                    if (!in_array($activo, ['0', '1'], true)) {
                        throw new InvalidArgumentException('Estado de cuenta no válido.');
                    }
                    $repo->llamar('sp_admin_cambiar_estado_usuario', [(int) $idSesion, $id, (int) $activo]);
                    break;
                case 'comunidad_eliminar':
                    if (($_POST['confirmacion'] ?? '') !== 'ELIMINAR') {
                        throw new InvalidArgumentException('Debes confirmar la eliminación.');
                    }
                    $repo->llamar('sp_admin_eliminar_comunidad', [$idSesion, $id]);
                    break;
                default:
                    throw new InvalidArgumentException('Acción no válida.');
            }
            // Evita repetir acciones al actualizar el navegador (POST/Redirect/GET).
            header('Location: admin_reportes.php?ok=1');
            exit;
        } catch (InvalidArgumentException $ex) {
            $error = $ex->getMessage();
        } catch (Throwable $ex) {
            error_log('[DrawU admin accion] ' . $ex->getMessage());
            $error = 'No se pudo aplicar el cambio. Comprueba que el registro exista y no tenga dependencias.';
        }
    }
}

if (isset($_GET['ok'])) {
    $mensaje = 'Cambio guardado correctamente.';
}

$buscar = campoBuscar('buscar');
$motivo = opcionFiltro('motivo', ['spam', 'acoso', 'inapropiado', 'plagio', 'otro']);
$estadoReporte = opcionFiltro('estado_reporte', ['pendiente', 'en_revision', 'resuelto', 'descartado']);
$estadoSoporte = opcionFiltro('estado_soporte', ['pendiente', 'en_revision', 'resuelto']);
$tipo = opcionFiltro('tipo', ['imagen', 'video', 'texto', 'aviso']);
$desde = fechaFiltro('desde');
$hasta = fechaFiltro('hasta');

if ($desde !== null && $hasta !== null && $desde > $hasta) {
    $error = 'La fecha inicial no puede ser posterior a la final.';
    $desde = $hasta = null;
}

$resumen = [];
$reportes = [];
$soporte = [];
$usuarios = [];
$publicaciones = [];
$comunidades = [];
try {
    $resumen = $repo->llamar('sp_admin_resumen', [$idSesion])[0] ?? [];
    $reportes = $repo->llamar('sp_admin_listar_reportes', [$idSesion, $motivo, $estadoReporte, $buscar, $desde, $hasta]);
    $soporte = $repo->llamar('sp_admin_listar_soporte', [$idSesion, $estadoSoporte, $buscar, $desde, $hasta]);
    $publicaciones = $repo->llamar('sp_admin_listar_publicaciones', [$idSesion, $buscar, $tipo, $desde, $hasta]);
    $comunidades = $repo->llamar('sp_admin_listar_comunidades', [$idSesion, $buscar]);
    $usuarios = $repo->llamar('sp_admin_listar_usuarios', [$idSesion, $buscar]);
} catch (Throwable $ex) {
    error_log('[DrawU admin consulta] ' . $ex->getMessage());
    $error = 'No fue posible cargar los datos. Comprueba que instalaste los procedimientos almacenados.';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Administración | DrawU</title>
<style>
:root{font-family:system-ui,-apple-system,Segoe UI,sans-serif;color-scheme:light;--fondo:#f7f5fb;--panel:#fff;--texto:#252033;--secundario:#655b70;--borde:#e8e0f0;--primario:#6933a2;--suave:#f0e8fa;--peligro:#a72c46}
body.oscuro{color-scheme:dark;--fondo:#161320;--panel:#241e2f;--texto:#f1ebf8;--secundario:#c9bbd4;--borde:#453552;--primario:#bd91ff;--suave:#342944;--peligro:#ff7894}
*{box-sizing:border-box} body{margin:0;background:var(--fondo);color:var(--texto)}
a{color:var(--primario)}button,select,input,textarea{font:inherit}button{cursor:pointer}
header{background:#43256d;color:#fff;padding:23px max(20px,calc((100% - 1280px)/2))}
.barra{display:flex;align-items:center;gap:16px;justify-content:space-between;flex-wrap:wrap}.barra h1{margin:0;font-size:1.7rem}.barra p{margin:6px 0 0;color:#e1d4f3}
.acciones{display:flex;gap:9px;flex-wrap:wrap}.acciones a,.acciones button{border:1px solid #d4b8f1;background:transparent;color:#fff;padding:9px 13px;border-radius:10px;text-decoration:none}
main{max-width:1280px;margin:auto;padding:25px 18px 65px}.ayuda{color:var(--secundario)}
.kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin:19px 0 23px}
.kpi{border:1px solid var(--borde);border-radius:14px;background:var(--panel);padding:17px}.kpi b{display:block;color:var(--primario);font-size:1.9rem}.kpi span{font-size:.88rem;color:var(--secundario)}
section,.filtros{background:var(--panel);border:1px solid var(--borde);border-radius:16px;padding:20px;margin:20px 0}
h2{font-size:1.4rem;margin:0 0 6px}.nota{font-size:.91rem;color:var(--secundario);margin:6px 0 15px}
.filtro-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}label{display:block;font-size:.86rem;font-weight:650;margin-bottom:5px}
input,select,textarea{width:100%;border:1px solid var(--borde);border-radius:9px;background:var(--panel);color:var(--texto);padding:9px}
.enviar{background:var(--primario);color:var(--fondo);border:0;border-radius:10px;padding:10px 16px;font-weight:700}.aplicar{display:flex;gap:10px;margin-top:14px;align-items:center}
nav{display:flex;gap:9px;flex-wrap:wrap;margin:15px 0}nav a{background:var(--suave);border-radius:10px;padding:10px 13px;font-weight:700;text-decoration:none}
.tabla{overflow:auto}table{width:100%;border-collapse:collapse;min-width:790px;font-size:.88rem}th,td{text-align:left;vertical-align:top;border-bottom:1px solid var(--borde);padding:11px;max-width:350px;overflow-wrap:anywhere}
th{background:var(--suave);color:var(--texto)}.celda-form{display:flex;flex-wrap:wrap;gap:6px;align-items:center}.celda-form select{width:auto;min-width:115px}.celda-form button,.boton-peligro{background:var(--suave);color:var(--texto);border:1px solid var(--borde);border-radius:9px;padding:8px}
.boton-peligro{color:var(--peligro);font-weight:700}.aviso{padding:13px;border:1px solid var(--borde);border-radius:10px;margin:12px 0}.aviso.exito{background:var(--suave)}.aviso.error{border-color:var(--peligro)}
details{background:var(--suave);border-radius:8px;padding:10px}details summary{cursor:pointer;font-weight:650}details form{margin-top:10px;display:grid;gap:8px;max-width:480px}
.oculto{display:none}.vacio{color:var(--secundario);padding:20px}
@media(max-width:1000px){.kpis{grid-template-columns:repeat(3,minmax(0,1fr))}.filtro-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:550px){.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.filtro-grid{grid-template-columns:1fr}.barra h1{font-size:1.3rem}}
</style>
</head>
<body>
<header><div class="barra"><div><h1>DrawU · Administración</h1><p>Sesión: <?= texto($identidad['nombre_usuario']) ?> · acceso exclusivo para administradores</p></div>
<div class="acciones"><button type="button" id="temaBoton" aria-label="Cambiar tema">Cambiar tema</button><a href="cerrar_sesion.php">Cerrar sesión</a></div></div></header>
<main>
<p class="ayuda">Panel para consultar reportes, gestionar solicitudes de soporte, moderar publicaciones y administrar comunidades. Todas sus consultas a MySQL se realizan mediante procedimientos almacenados.</p>
<?php if ($mensaje !== ''): ?><div class="aviso exito" role="status"><?= texto($mensaje) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="aviso error" role="alert"><?= texto($error) ?></div><?php endif; ?>
<div class="kpis">
<?php foreach ([['Usuarios', 'total_usuarios'], ['Publicaciones', 'total_publicaciones'], ['Comunidades', 'total_comunidades'], ['Reportes pendientes', 'reportes_pendientes'], ['Soporte pendiente', 'soporte_pendiente']] as $item): ?>
  <div class="kpi"><b><?= (int) ($resumen[$item[1]] ?? 0) ?></b><span><?= texto($item[0]) ?></span></div>
<?php endforeach; ?>
</div>
<nav aria-label="Secciones"><a href="#reportes">Reportes</a><a href="#soporte">Soporte</a><a href="#publicaciones">Publicaciones</a><a href="#comunidades">Comunidades</a><a href="#usuarios">Usuarios</a></nav>
<form class="filtros" method="get" action="admin_reportes.php">
<h2>Filtros de consulta</h2><p class="nota">Los valores se validan en PHP y se envían como parámetros a los procedimientos almacenados.</p>
<div class="filtro-grid">
<div><label for="buscar">Buscar usuario o texto</label><input id="buscar" type="search" name="buscar" maxlength="80" value="<?= texto($buscar) ?>" placeholder="Nombre o contenido"></div>
<div><label for="motivo">Motivo del reporte</label><select id="motivo" name="motivo"><option value="">Todos</option><?php foreach (['spam','acoso','inapropiado','plagio','otro'] as $o): ?><option value="<?= texto($o) ?>" <?= $motivo===$o?'selected':'' ?>><?= texto(ucfirst($o)) ?></option><?php endforeach; ?></select></div>
<div><label for="estado_reporte">Estado del reporte</label><select id="estado_reporte" name="estado_reporte"><option value="">Todos</option><?php foreach (['pendiente','en_revision','resuelto','descartado'] as $o): ?><option value="<?= texto($o) ?>" <?= $estadoReporte===$o?'selected':'' ?>><?= texto(estadoEtiqueta($o)) ?></option><?php endforeach; ?></select></div>
<div><label for="estado_soporte">Estado de soporte</label><select id="estado_soporte" name="estado_soporte"><option value="">Todos</option><?php foreach (['pendiente','en_revision','resuelto'] as $o): ?><option value="<?= texto($o) ?>" <?= $estadoSoporte===$o?'selected':'' ?>><?= texto(estadoEtiqueta($o)) ?></option><?php endforeach; ?></select></div>
<div><label for="tipo">Tipo de publicación</label><select id="tipo" name="tipo"><option value="">Todos</option><?php foreach (['imagen','video','texto','aviso'] as $o): ?><option value="<?= texto($o) ?>" <?= $tipo===$o?'selected':'' ?>><?= texto(ucfirst($o)) ?></option><?php endforeach; ?></select></div>
<div><label for="desde">Desde</label><input id="desde" type="date" name="desde" value="<?= texto($desde) ?>"></div>
<div><label for="hasta">Hasta</label><input id="hasta" type="date" name="hasta" value="<?= texto($hasta) ?>"></div>
</div>
<div class="aplicar"><button class="enviar" type="submit">Aplicar filtros</button><a href="admin_reportes.php">Limpiar filtros</a></div>
</form>

<section id="reportes"><h2>Reportes de usuarios</h2><p class="nota">Reportador, reportado, motivo y estado · <?= count($reportes) ?> registros mostrados (máx. 150).</p>
<?php if (!$reportes): ?><p class="vacio">No hay reportes para estos filtros.</p><?php else: ?><div class="tabla"><table><thead><tr><th>ID</th><th>Reportó</th><th>Reportado</th><th>Motivo</th><th>Detalle</th><th>Fecha</th><th>Estado / acción</th></tr></thead><tbody>
<?php foreach ($reportes as $r): ?><tr>
<td><?= (int) $r['id_reporte'] ?></td><td><?= texto($r['usuario_reportador']) ?></td><td><?= texto($r['usuario_reportado']) ?></td>
<td><?= texto($r['motivo']) ?></td><td><?= texto($r['detalle'] ?? '—') ?></td><td><?= texto($r['fecha_reporte']) ?></td>
<td><form class="celda-form" method="post"><input type="hidden" name="csrf" value="<?= texto($csrf) ?>"><input type="hidden" name="accion" value="reporte_estado"><input type="hidden" name="id" value="<?= (int) $r['id_reporte'] ?>">
<label class="oculto" for="reporte-<?= (int) $r['id_reporte'] ?>">Nuevo estado</label><select id="reporte-<?= (int) $r['id_reporte'] ?>" name="estado"><?php foreach (['pendiente','en_revision','resuelto','descartado'] as $o): ?><option value="<?= texto($o) ?>" <?= $r['estado']===$o?'selected':'' ?>><?= texto(estadoEtiqueta($o)) ?></option><?php endforeach; ?></select><button type="submit">Guardar</button></form></td></tr>
<?php endforeach; ?></tbody></table></div><?php endif; ?></section>

<section id="soporte"><h2>Mensajes de soporte</h2><p class="nota">Solicitudes de ayuda y su seguimiento · <?= count($soporte) ?> registros.</p>
<?php if (!$soporte): ?><p class="vacio">No hay mensajes para estos filtros.</p><?php else: ?><div class="tabla"><table><thead><tr><th>ID</th><th>Usuario</th><th>Mensaje</th><th>Fecha</th><th>Estado / acción</th></tr></thead><tbody>
<?php foreach ($soporte as $s): ?><tr><td><?= (int) $s['id_mensaje'] ?></td><td><?= texto($s['nombre_usuario']) ?></td><td><?= texto($s['mensaje']) ?></td><td><?= texto($s['fecha_envio']) ?></td>
<td><form class="celda-form" method="post"><input type="hidden" name="csrf" value="<?= texto($csrf) ?>"><input type="hidden" name="accion" value="soporte_estado"><input type="hidden" name="id" value="<?= (int) $s['id_mensaje'] ?>"><label class="oculto" for="soporte-<?= (int) $s['id_mensaje'] ?>">Nuevo estado</label><select id="soporte-<?= (int) $s['id_mensaje'] ?>" name="estado"><?php foreach (['pendiente','en_revision','resuelto'] as $o): ?><option value="<?= texto($o) ?>" <?= $s['estado']===$o?'selected':'' ?>><?= texto(estadoEtiqueta($o)) ?></option><?php endforeach; ?></select><button type="submit">Guardar</button></form></td></tr>
<?php endforeach; ?></tbody></table></div><?php endif; ?></section>

<section id="publicaciones"><h2>Moderación de publicaciones</h2><p class="nota">Puedes eliminar contenido que incumpla las reglas. La eliminación es permanente y afecta comentarios, likes y videos asociados. Se preserva el historial de recompensas para evitar cobrar nuevamente.</p>
<?php if (!$publicaciones): ?><p class="vacio">No hay publicaciones para estos filtros.</p><?php else: ?><div class="tabla"><table><thead><tr><th>ID</th><th>Autor</th><th>Tipo</th><th>Contenido</th><th>Comunidad</th><th>Actividad</th><th>Fecha</th><th>Acción</th></tr></thead><tbody>
<?php foreach ($publicaciones as $p): ?><tr><td><?= (int) $p['id_publicacion'] ?></td><td><?= texto($p['nombre_usuario']) ?></td><td><?= texto($p['tipo']) ?></td><td><?= texto(mb_strimwidth((string) ($p['titulo'] ?: $p['descripcion'] ?: ''),0,170,'…','UTF-8')) ?></td><td><?= texto($p['comunidad_titulo'] ?? 'General') ?></td><td><?= (int) $p['total_likes'] ?> likes · <?= (int) $p['total_comentarios'] ?> comentarios</td><td><?= texto($p['fecha_publicacion']) ?></td>
<td><form class="celda-form confirmar-borrado" method="post"><input type="hidden" name="csrf" value="<?= texto($csrf) ?>"><input type="hidden" name="accion" value="publicacion_eliminar"><input type="hidden" name="confirmacion" value="ELIMINAR"><input type="hidden" name="id" value="<?= (int) $p['id_publicacion'] ?>"><button class="boton-peligro" type="submit">Eliminar</button></form></td></tr>
<?php endforeach; ?></tbody></table></div><?php endif; ?></section>

<section id="comunidades"><h2>Administración de comunidades</h2><p class="nota">Edita títulos, descripciones y privacidad. Por seguridad, solo puedes eliminar comunidades sin publicaciones asociadas.</p>
<?php if (!$comunidades): ?><p class="vacio">No hay comunidades para estos filtros.</p><?php else: ?><div class="tabla"><table><thead><tr><th>ID</th><th>Comunidad</th><th>Privacidad</th><th>Miembros</th><th>Publicaciones hoy</th><th>Administrar</th></tr></thead><tbody>
<?php foreach ($comunidades as $c): ?><tr><td><?= (int) $c['id_comunidad'] ?></td><td><?= texto($c['titulo']) ?><br><small><?= texto($c['slug']) ?></small></td><td><?= texto($c['privacidad']) ?></td><td><?= (int) $c['total_miembros'] ?></td><td><?= (int) $c['publicaciones_hoy'] ?></td><td>
<details><summary>Editar comunidad</summary><form method="post"><input type="hidden" name="csrf" value="<?= texto($csrf) ?>"><input type="hidden" name="accion" value="comunidad_editar"><input type="hidden" name="id" value="<?= (int) $c['id_comunidad'] ?>"><label>Título<input name="titulo" maxlength="150" minlength="3" value="<?= texto($c['titulo']) ?>" required></label><label>Descripción<textarea name="descripcion" maxlength="1000" rows="3"><?= texto($c['descripcion']) ?></textarea></label><label>Privacidad<select name="privacidad"><option value="publica" <?= $c['privacidad']==='publica'?'selected':'' ?>>Pública</option><option value="privada" <?= $c['privacidad']==='privada'?'selected':'' ?>>Privada</option></select></label><button class="enviar" type="submit">Guardar comunidad</button></form></details>
<form class="celda-form confirmar-borrado" method="post" style="margin-top:7px"><input type="hidden" name="csrf" value="<?= texto($csrf) ?>"><input type="hidden" name="accion" value="comunidad_eliminar"><input type="hidden" name="confirmacion" value="ELIMINAR"><input type="hidden" name="id" value="<?= (int) $c['id_comunidad'] ?>"><button class="boton-peligro" type="submit">Eliminar si está vacía</button></form>
</td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>

<section id="usuarios"><h2>Usuarios registrados</h2>
<p class="nota">Solo el administrador puede dar de baja o reactivar cuentas normales. La baja es lógica: no se borran sus publicaciones ni comentarios. Los administradores no pueden desactivarse desde este panel.</p>
<?php if (!$usuarios): ?><p class="vacio">No hay usuarios para estos filtros.</p><?php else: ?>
<div class="tabla"><table><thead><tr><th>ID</th><th>Nombre de usuario</th><th>Correo</th><th>Registro</th><th>Rol</th><th>Estado</th><th>Acción administrativa</th></tr></thead><tbody>
<?php foreach ($usuarios as $u): ?>
<tr>
<td><?= (int) $u['id_usuario'] ?></td>
<td><?= texto($u['nombre_usuario']) ?></td>
<td><?= texto($u['correo']) ?></td>
<td><?= texto($u['fecha_registro']) ?></td>
<td><?= (int) $u['es_admin'] === 1 ? 'Administrador' : 'Usuario' ?></td>
<td><?= (int) $u['activo'] === 1 ? 'Activo' : 'Inactivo' ?></td>
<td>
<?php if ((int) $u['es_admin'] !== 1 && (int) $u['id_usuario'] !== (int) $idSesion): ?>
<form class="celda-form <?= (int) $u['activo'] === 1 ? 'confirmar-baja' : '' ?>" method="post">
<input type="hidden" name="csrf" value="<?= texto($csrf) ?>">
<input type="hidden" name="accion" value="usuario_estado">
<input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
<input type="hidden" name="activo" value="<?= (int) $u['activo'] === 1 ? '0' : '1' ?>">
<button type="submit" class="<?= (int) $u['activo'] === 1 ? 'boton-peligro' : 'enviar' ?>">
<?= (int) $u['activo'] === 1 ? 'Dar de baja' : 'Reactivar' ?>
</button>
</form>
<?php else: ?><small>Protegido</small><?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<p class="ayuda">Los reportes y mensajes son privados. Limita esta página a administradores y cambia la contraseña de prueba antes de publicar DrawU.</p>
</main>
<script>
const temaBoton=document.getElementById('temaBoton');
try{if(localStorage.getItem('drawu-admin-tema')==='oscuro')document.body.classList.add('oscuro');}catch(e){}
temaBoton.addEventListener('click',()=>{document.body.classList.toggle('oscuro');try{localStorage.setItem('drawu-admin-tema',document.body.classList.contains('oscuro')?'oscuro':'claro')}catch(e){}});
document.querySelectorAll('.confirmar-borrado').forEach(form=>form.addEventListener('submit',ev=>{if(!window.confirm('¿Confirmas la eliminación definitiva? Esta acción no se puede deshacer.'))ev.preventDefault();}));
document.querySelectorAll('.confirmar-baja').forEach(form=>form.addEventListener('submit',ev=>{if(!window.confirm('¿Dar de baja esta cuenta? No podrá iniciar sesión ni seguir usando DrawU hasta que la reactives.'))ev.preventDefault();}));
</script>
</body>
</html>
