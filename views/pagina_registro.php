<?php
require_once __DIR__ . '/includes/app.php';

if (usuarioActual()) {
    header('Location: inicio.php');
    exit;
}

$error = '';
$correoPrevio = '';
$contrasenaGeneradaTrasRegistro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correoPrevio = trim((string) ($_POST['email'] ?? ''));
    $contrasena = (string) ($_POST['contrasena'] ?? '');
    $confirmar = (string) ($_POST['confirmar_contrasena'] ?? '');
    $seUsoGenerador = (($_POST['contrasena_automatica'] ?? '0') === '1');

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

                if ($seUsoGenerador) {
                    // Se muestra UNA sola vez después del registro exitoso.
                    // No se almacena en la sesión ni se guarda en texto plano en MySQL.
                    $contrasenaGeneradaTrasRegistro = $contrasena;
                    header('Cache-Control: private, no-store, max-age=0');
                    header('Pragma: no-cache');
                } else {
                    // Mantener el comportamiento original si la contraseña fue escrita manualmente.
                    header('Location: elegir_mascota.php');
                    exit;
                }
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
    <style>
        /* Solo elementos nuevos; se conservan los CSS originales de DrawU. */
        .drawu-boton-secundario { width: 100%; padding: .65rem .9rem; border: 1px solid #8b5ac7; border-radius: 10px; background: transparent; color: inherit; cursor: pointer; font-weight: 600; margin: .5rem 0; }
        .drawu-boton-secundario:disabled { opacity: .6; cursor: wait; }
        .drawu-ayuda { font-size: .85rem; margin: .35rem 0 .8rem; line-height: 1.45; }
        .drawu-clave-previa { font-size: .85rem; padding: .6rem; border-radius: 8px; background: rgba(141,93,185,.12); overflow-wrap: anywhere; }
        .drawu-resultado-generador { margin: 1.4rem 0; }
        .drawu-clave-resultado { display: block; box-sizing: border-box; width: 100%; padding: .8rem; margin: .7rem 0; border: 1px solid #a69bb6; border-radius: 10px; background: transparent; color: inherit; font-size: 1rem; }
        .drawu-enlace-continuar { display: block; text-align: center; text-decoration: none; }
    </style>
</head>
<body>
    <main class="pantalla">
        <section class="panel-izquierdo" aria-label="Presentación">
            <p class="logo">Logo</p>
            <div class="imagen-mascotas">imagen de mascotas<br>de la página</div>
        </section>
        <section class="panel-derecho" aria-labelledby="titulo">
            <?php if ($contrasenaGeneradaTrasRegistro !== null): ?>
                <h1 id="titulo">¡Cuenta creada!</h1>
                <p class="subtitulo">Tu registro en DrawU se completó correctamente.</p>
                <div class="drawu-resultado-generador" role="status" aria-live="polite">
                    <label for="contrasenaCreada"><strong>Esta es tu contraseña generada:</strong></label>
                    <input
                        id="contrasenaCreada"
                        class="drawu-clave-resultado"
                        type="text"
                        readonly
                        autocomplete="off"
                        spellcheck="false"
                        value="<?= e($contrasenaGeneradaTrasRegistro) ?>"
                    >
                    <button id="copiarContrasena" class="drawu-boton-secundario" type="button">Copiar contraseña</button>
                    <p id="estadoCopia" class="drawu-ayuda" aria-live="polite"></p>
                    <p class="drawu-ayuda"><strong>Guárdala ahora:</strong> por seguridad, no volveremos a mostrarla en DrawU.</p>
                </div>
                <a href="elegir_mascota.php" class="boton drawu-enlace-continuar">Continuar a elegir mascota</a>
            <?php else: ?>
                <h1 id="titulo">¡Bienvenido!</h1>
                <p class="subtitulo">Registrar usuario</p>

            <form id="formRegistro" action="pagina_registro.php" method="POST">
                <input type="hidden" name="contrasena_automatica" id="contrasena_automatica" value="0">
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
                <button id="generarContrasena" class="drawu-boton-secundario" type="button">🔐 Generar contraseña automática</button>
                <p class="drawu-ayuda">Usamos una API externa y añadimos caracteres aleatorios en tu navegador para mayor seguridad.</p>
                <p id="estadoGenerador" class="drawu-ayuda" role="status" aria-live="polite"></p>
                <p id="vistaGenerada" class="drawu-clave-previa" hidden></p>
                <button class="boton" type="submit">Crear cuenta</button>
            </form>
            <p class="mensaje" id="mensaje" role="alert" style="<?= $error !== '' ? 'display:block;' : '' ?>"><?= e($error ?: 'Las contraseñas no coinciden. Inténtalo de nuevo.') ?></p>
            <p class="cuenta"><span>¿Ya tienes una cuenta?</span><a href="pagina_login.php">Iniciar sesión</a></p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <?php // include __DIR__ . '/includes/footer.php'; ?>
    </footer>

    <?php if ($contrasenaGeneradaTrasRegistro === null): ?>
    <!-- El JS original sigue validando que coincidan ambas contraseñas. -->
    <script src="../assets/js/pagina_registro.js"></script>
    <script>
    (() => {
        const boton = document.getElementById('generarContrasena');
        const contrasena = document.getElementById('contrasena');
        const confirmar = document.getElementById('confirmar');
        const indicador = document.getElementById('contrasena_automatica');
        const estado = document.getElementById('estadoGenerador');
        const vista = document.getElementById('vistaGenerada');

        // Generación local con fuente criptográfica (nunca Math.random).
        function aleatorioLocal(bytes) {
            const buffer = new Uint8Array(bytes);
            window.crypto.getRandomValues(buffer);
            return Array.from(buffer, b => b.toString(16).padStart(2, '0')).join('');
        }

        // Si se modifica manualmente, deja de tratarse como contraseña automática.
        contrasena.addEventListener('input', () => {
            indicador.value = '0';
            vista.hidden = true;
            vista.textContent = '';
        });

        boton.addEventListener('click', async () => {
            boton.disabled = true;
            estado.textContent = 'Generando contraseña...';
            let generada;
            let fuente;
            try {
                // API externa real, gratuita y sin API key.
                const respuesta = await fetch(
                    'https://api.suggestpassword.com/?type=password&length=20&symbols=basic&no_ambiguous=true',
                    { cache: 'no-store' }
                );
                if (!respuesta.ok) throw new Error('La API no respondió correctamente');
                const datos = await respuesta.json();
                if (typeof datos.generated_password !== 'string' || datos.generated_password.length < 12 || datos.generated_password.length > 128) {
                    throw new Error('Respuesta no válida de la API');
                }
                // El servicio externo NO conoce la contraseña final completa.
                generada = datos.generated_password + aleatorioLocal(12);
                fuente = 'Contraseña generada con API externa y reforzada localmente.';
            } catch (error) {
                // Si no hay conexión o la API falla, no impedimos el registro.
                generada = aleatorioLocal(18);
                fuente = 'API externa no disponible: contraseña segura generada localmente.';
            } finally {
                boton.disabled = false;
            }

            contrasena.value = generada;
            confirmar.value = generada;
            confirmar.dispatchEvent(new Event('input', { bubbles: true }));
            indicador.value = '1';
            vista.textContent = 'Contraseña sugerida: ' + generada;
            vista.hidden = false;
            estado.textContent = fuente + ' Se mostrará una vez más al completar el registro.';
        });
    })();
    </script>
    <?php else: ?>
    <script>
    document.getElementById('copiarContrasena').addEventListener('click', async () => {
        const campo = document.getElementById('contrasenaCreada');
        const estado = document.getElementById('estadoCopia');
        try {
            await navigator.clipboard.writeText(campo.value);
            estado.textContent = 'Contraseña copiada. Guárdala en un lugar seguro.';
        } catch (error) {
            campo.focus();
            campo.select();
            estado.textContent = 'Seleccionamos la contraseña. Cópiala con Ctrl+C.';
        }
    });
    </script>
    <?php endif; ?>
</body>
</html>
