<?php
require_once __DIR__ . '/includes/app.php';

if (usuarioActual()) {
    header('Location: inicio.php');
    exit;
}

$error = '';
$correoPrevio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correoPrevio = trim((string) ($_POST['email'] ?? ''));
    $contrasena = (string) ($_POST['contrasena'] ?? '');
    $confirmar = (string) ($_POST['confirmar_contrasena'] ?? '');

    if (!filter_var($correoPrevio, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo electrónico válido.';
    } elseif (strlen($contrasena) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($contrasena !== $confirmar) {
        $error = 'Las contraseñas no coinciden. Inténtalo de nuevo.';
    } else {
        try {
            $pdo = conexion();
            $existe = $pdo->prepare('SELECT 1 FROM usuarios WHERE correo = ? LIMIT 1');
            $existe->execute([$correoPrevio]);

            if ($existe->fetchColumn()) {
                $error = 'Ya existe una cuenta registrada con ese correo.';
            } else {
                $local = explode('@', $correoPrevio)[0];
                $baseUser = preg_replace('/[^A-Za-z0-9_.]/', '', $local);
                if (strlen($baseUser) < 3) {
                    $baseUser = 'artista_' . $baseUser;
                }
                $baseUser = substr($baseUser, 0, 40);
                $nombreUsuario = $baseUser;
                $primerNombre = ucfirst($baseUser);

                $checkUser = $pdo->prepare('SELECT 1 FROM usuarios WHERE nombre_usuario = ? LIMIT 1');
                $contador = 1;
                while (true) {
                    $checkUser->execute([$nombreUsuario]);
                    if (!$checkUser->fetchColumn()) {
                        break;
                    }
                    $nombreUsuario = $baseUser . $contador++;
                }

                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $ins = $pdo->prepare(
                    'INSERT INTO usuarios (primer_nombre, nombre_usuario, correo, contrasena_hash, drawings, id_mascota_activa)
                     VALUES (?, ?, ?, ?, 100, NULL)'
                );
                $ins->execute([$primerNombre, $nombreUsuario, $correoPrevio, $hash]);
                $idNuevo = (int) $pdo->lastInsertId();

                iniciarSesionUsuario($idNuevo);
                header('Location: elegir_mascota.php');
                exit;
            }
        } catch (Throwable $ex) {
            error_log('[DrawU Registro] ' . $ex->getMessage());
            $error = 'No se pudo completar el registro. Verifica la base de datos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de usuario | DrawU</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="../assets/css/pagina_registro.css">
</head>
<body>
    <main class="pantalla">
        <section class="panel-izquierdo" aria-label="Presentación">
            <p class="logo">Logo</p>
            <div class="imagen-mascotas">imagen de mascotas<br>de la página</div>
        </section>
        <section class="panel-derecho" aria-labelledby="titulo">
            <h1 id="titulo">¡Bienvenido!</h1>
            <p class="subtitulo">Registrar usuario</p>

            <form id="formRegistro" action="pagina_registro.php" method="POST">
                <label class="campo">
                    <svg viewBox="0 0 24 24" width="27" height="27" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="4.5" width="19" height="15" rx="2"></rect><path d="m3.5 6 8.5 6.5L20.5 6"></path></svg>
                    <input type="email" name="email" placeholder="E-mail" autocomplete="email" value="<?= e($correoPrevio) ?>" required>
                </label>
                <label class="campo">
                    <svg viewBox="0 0 24 24" width="27" height="27" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="15" r="5"></circle><path d="m11.5 11.5 8-8 2 2-2.5 2.5 1.5 1.5-2.5 2.5-1.5-1.5"></path></svg>
                    <input id="contrasena" type="password" name="contrasena" placeholder="Contraseña" autocomplete="new-password" minlength="8" required>
                </label>
                <label class="campo">
                    <svg viewBox="0 0 24 24" width="27" height="27" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="15" r="5"></circle><path d="m11.5 11.5 8-8 2 2-2.5 2.5 1.5 1.5-2.5 2.5-1.5-1.5"></path></svg>
                    <input id="confirmar" type="password" name="confirmar_contrasena" placeholder="Confirmar contraseña" autocomplete="new-password" minlength="8" required>
                </label>
                <button class="boton" type="submit">Crear cuenta</button>
            </form>
            <p class="mensaje" id="mensaje" role="alert" style="<?= $error !== '' ? 'display:block;' : '' ?>"><?= e($error ?: 'Las contraseñas no coinciden. Inténtalo de nuevo.') ?></p>
            <p class="cuenta"><span>¿Ya tienes una cuenta?</span><a href="pagina_login.php">Iniciar sesión</a></p>
        </section>
    </main>

    <footer>
        <?php // include __DIR__ . '/includes/footer.php'; ?>
    </footer>

    <script src="../assets/js/pagina_registro.js"></script>
</body>
</html>
