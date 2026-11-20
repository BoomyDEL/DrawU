<?php
/**
 * Conexión a la base de datos (PDO / MySQL).
 *
 * Los valores por defecto sirven para XAMPP. Para usar otras credenciales
 * sin subirlas al repositorio, crea config/local.php (está en .gitignore):
 *
 *   <?php
 *   return ['host' => '127.0.0.1', 'port' => 3306, 'db' => 'drawu',
 *           'user' => 'root', 'pass' => 'tu_contraseña'];
 */

define('PUERTO_DANI', 3306);
define('PUERTO_MEMO', 3307);
define('PASS_DANI', 'dannifer2018');
define('PASS_MEMO', '');

function conexion(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = [
        'host' => '127.0.0.1',
        'port' => PUERTO_MEMO,
        'db'   => 'drawu',
        'user' => 'root',
        'pass' => PASS_MEMO,
    ];

    $local = __DIR__ . '/../config/local.php';
    if (is_file($local)) {
        $config = array_merge($config, (array) require $local);
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'],
        (int) $config['port'],
        $config['db']
    );

    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}
