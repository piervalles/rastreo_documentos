<?php
/**
 * instalador.php - Instalador Web Code-First de 1 Clic
 * Crea la base de datos, ejecuta las migraciones de tablas y carga los seeders iniciales.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/database/Schema.php';
require_once __DIR__ . '/database/Seeders.php';

$config = require __DIR__ . '/config/database.php';

$mensaje = null;
$tipoMensaje = 'info';
$detalles = [];
$instaladoConExito = false;

// Comprobar estado previo
$mysqlConectado = false;
$errorConexion = null;

try {
    $dsnServidor = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
    $pdoTest = new PDO($dsnServidor, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $mysqlConectado = true;
} catch (PDOException $e) {
    $errorConexion = $e->getMessage();
}

// Procesar formulario de instalación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'instalar') {
    if (!$mysqlConectado) {
        $tipoMensaje = 'error';
        $mensaje = "No es posible conectar a MySQL. Asegúrate de que XAMPP tenga el módulo MySQL iniciado.";
    } else {
        try {
            // 1. Crear la base de datos si no existe
            $pdoTest->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $detalles[] = ["ok" => true, "texto" => "Base de datos `{$config['database']}` verificada / creada con éxito."];

            // 2. Conectar directamente a la base de datos creada
            $dsnBD = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            $pdo = new PDO($dsnBD, $config['username'], $config['password'], $config['options']);

            // 3. Ejecutar las tablas del Schema Code-First
            $tablas = Schema::getTables();
            foreach ($tablas as $nombreTabla => $sql) {
                $pdo->exec($sql);
                $detalles[] = ["ok" => true, "texto" => "Tabla `{$nombreTabla}` creada / sincronizada correctamente."];
            }

            // 4. Ejecutar Seeders con datos iniciales
            $seederLogs = Seeders::run($pdo);
            foreach ($seederLogs as $log) {
                $detalles[] = ["ok" => true, "texto" => $log];
            }

            $tipoMensaje = 'success';
            $mensaje = "¡Instalación completada con éxito! El sistema está listo para operar.";
            $instaladoConExito = true;

        } catch (Exception $e) {
            $tipoMensaje = 'error';
            $mensaje = "Ocurrió un error durante la instalación: " . $e->getMessage();
            $detalles[] = ["ok" => false, "texto" => $e->getMessage()];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador del Sistema - Rastreo Documental</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background: #f1f5f9; color: #1e293b; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: #ffffff; width: 100%; max-width: 680px; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: #ffffff; padding: 32px 28px; text-align: center; }
        .header h1 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .content { padding: 28px; }
        .status-box { padding: 14px 18px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; }
        .status-online { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .status-offline { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-green { background: #10b981; color: #fff; }
        .badge-red { background: #ef4444; color: #fff; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px; font-size: 13px; line-height: 1.6; }
        .info-card ul { margin-left: 20px; margin-top: 8px; }
        .btn { display: inline-block; width: 100%; padding: 14px 20px; font-size: 15px; font-weight: 600; text-align: center; border-radius: 10px; border: none; cursor: pointer; text-decoration: none; transition: background 0.2s ease; }
        .btn-primary { background: #2563eb; color: #ffffff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #10b981; color: #ffffff; }
        .btn-success:hover { background: #059669; }
        .btn-disabled { background: #94a3b8; cursor: not-allowed; }
        .logs { margin-top: 24px; max-height: 220px; overflow-y: auto; background: #0f172a; border-radius: 10px; padding: 14px; color: #f8fafc; font-family: "Courier New", Courier, monospace; font-size: 12px; line-height: 1.7; }
        .log-item { display: flex; align-items: center; gap: 8px; }
        .log-ok { color: #34d399; }
        .log-err { color: #f87171; }
        .credentials-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px; margin-top: 20px; font-size: 14px; }
        .credentials-box strong { color: #1e40af; }
    </style>
</head>
<body>

<div class="card">
    <div class="header">
        <h1>Sistema de Rastreo Documental Físico</h1>
        <p>Instalador Web Code-First de 1 Clic (XAMPP / Red Local)</p>
    </div>

    <div class="content">
        <!-- Estado del Motor MySQL -->
        <div class="status-box <?= $mysqlConectado ? 'status-online' : 'status-offline' ?>">
            <div>
                <strong>Servicio MySQL (<?= htmlspecialchars($config['host'] . ':' . $config['port']) ?>):</strong>
                <span><?= $mysqlConectado ? 'Conectado y listo' : 'No disponible' ?></span>
            </div>
            <span class="badge <?= $mysqlConectado ? 'badge-green' : 'badge-red' ?>">
                <?= $mysqlConectado ? 'En línea' : 'Detenido' ?>
            </span>
        </div>

        <?php if (!$mysqlConectado): ?>
            <div class="info-card" style="border-left: 4px solid #ef4444;">
                <strong style="color: #991b1b;">¿Cómo solucionar este error?</strong>
                <ul>
                    <li>Abre el panel de control de <strong>XAMPP</strong> en esta computadora.</li>
                    <li>Busca el renglón <strong>MySQL</strong> y presiona el botón <strong>Start</strong> hasta que se ponga en verde.</li>
                    <li>Recarga esta página en tu navegador.</li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($mensaje): ?>
            <div class="status-box <?= $tipoMensaje === 'success' ? 'status-online' : 'status-offline' ?>">
                <span><?= htmlspecialchars($mensaje) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($detalles)): ?>
            <div class="logs">
                <?php foreach ($detalles as $d): ?>
                    <div class="log-item">
                        <span class="<?= $d['ok'] ? 'log-ok' : 'log-err' ?>"><?= $d['ok'] ? '✓' : '✗' ?></span>
                        <span><?= htmlspecialchars($d['texto']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($instaladoConExito): ?>
            <div class="credentials-box">
                <p><strong>Credenciales iniciales de acceso:</strong></p>
                <p>• <strong>Administrador:</strong> Usuario: <code>admin</code> | Contraseña: <code>admin123</code></p>
                <p>• <strong>Operador (Mesa de Partes):</strong> Usuario: <code>mesapartes</code> | Contraseña: <code>usuario123</code></p>
            </div>
            <div style="margin-top: 20px;">
                <a href="index.php" class="btn btn-success">Ir al Sistema de Trazabilidad →</a>
            </div>
        <?php else: ?>
            <div class="info-card">
                <strong>¿Qué realizará este instalador?</strong>
                <ul>
                    <li>Creará la base de datos <code><?= htmlspecialchars($config['database']) ?></code> si no existe.</li>
                    <li>Creará automáticamente las 8 tablas de entidades, relaciones y llaves foráneas.</li>
                    <li>Registrará los catálogos base (áreas, estados operativos, dictámenes y acciones).</li>
                    <li>Creará las cuentas de acceso iniciales.</li>
                </ul>
            </div>

            <form method="POST" action="instalar.php">
                <input type="hidden" name="accion" value="instalar">
                <button type="submit" class="btn <?= $mysqlConectado ? 'btn-primary' : 'btn-disabled' ?>" <?= !$mysqlConectado ? 'disabled' : '' ?>>
                    🚀 Inicializar / Reinstalar Base de Datos (1 Clic)
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
