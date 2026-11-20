<?php
require_once __DIR__ . '/includes/app.php';

// Si ya hay una sesión, enviar a cada persona a su página correspondiente.
$actual = usuarioActual();
if ($actual !== null) {
    // usuarioActual() ya obtiene es_admin desde un PROCEDURE.
    if ((int) ($actual['es_admin'] ?? 0) === 1) {
        header('Location: admin_reportes.php');
    } else {
        header('Location: inicio.php');
    }
    exit;
}

$error = '';
$correoPrevio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correoPrevio = trim((string) ($_POST['email'] ?? ''));
    $contrasena = (string) ($_POST['contrasena'] ?? '');

    if ($correoPrevio === '' || $contrasena === '') {
        $error = 'Por favor ingresa tu correo/usuario y contraseña.';
    } else {
        try {
            // Acepta correo electrónico para todos y nombre de usuario (ej. admin).
            $stmt = conexion()->prepare('CALL sp_drawu_autenticacion(?)');
            try {
                $stmt->execute([$correoPrevio]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            } finally {
                $stmt->closeCursor();
            }

            // El usuario debe estar activo y la clave se comprueba con su hash.
            if ($usuario && (int) $usuario['activo'] === 1
                && password_verify($contrasena, $usuario['contrasena_hash'])) {
                iniciarSesionUsuario((int) $usuario['id_usuario']);

                if ((bool) $usuario['es_admin']) {
                    $destino = 'admin_reportes.php';
                } else {
                    $destino = empty($usuario['id_mascota_activa'])
                        ? 'elegir_mascota.php'
                        : 'inicio.php';
                }

                header('Location: ' . $destino);
                exit;
            } else {
                $error = 'Usuario/correo o contraseña incorrectos. Inténtalo de nuevo.';
            }
        } catch (Throwable $ex) {
            error_log('[DrawU Login] ' . $ex->getMessage());
            $error = 'No se pudo conectar a la base de datos. Verifica tu configuración.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | DrawU</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/pagina_login.css">
</head>
<body>
    <main class="pantalla">
        <section class="panel-izquierdo" aria-label="Presentación">
            <p class="logo">Logo</p>
            <div class="imagen-mascotas">imagen de mascotas<br>de la página</div>
        </section>
        <section class="panel-derecho" aria-labelledby="titulo">
            <h1 id="titulo">¡Bienvenido!</h1>
            <p class="subtitulo">Iniciar sesión</p>

            <form id="formRegistro" action="pagina_login.php" method="POST">
                <label class="campo">
                    <svg viewBox="0 0 24 24" width="27" height="27" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="4.5" width="19" height="15" rx="2"></rect><path d="m3.5 6 8.5 6.5L20.5 6"></path></svg>
                    <input type="text" name="email" placeholder="Correo electrónico o usuario" autocomplete="username" value="<?= e($correoPrevio) ?>" required>
                </label>
                <label class="campo">
                    <svg viewBox="0 0 24 24" width="27" height="27" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="15" r="5"></circle><path d="m11.5 11.5 8-8 2 2-2.5 2.5 1.5 1.5-2.5 2.5-1.5-1.5"></path></svg>
                    <input id="contrasena" type="password" name="contrasena" placeholder="Contraseña" autocomplete="current-password" minlength="8" required>
                </label>

                <button class="boton" type="submit">Iniciar sesión</button>
            </form>
            <p class="mensaje" id="mensaje" role="alert" style="<?= $error !== '' ? 'display:block;' : '' ?>"><?= e($error ?: 'Usuario/correo o contraseña incorrectos.') ?></p>
            <p class="cuenta"><a href="pagina_registro.php">¿Deseas crear una cuenta?</a></p>
        </section>
    </main>

    <footer>
        <?php // include __DIR__ . '/includes/footer.php'; ?>
    </footer>

    <script src="../assets/js/pagina_login.js"></script>
</body>
</html>
