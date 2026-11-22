<?php
/**
 * views/auth/login.php - Pantalla de Inicio de Sesión
 */
require_once __DIR__ . '/../../helpers/Seguridad.php';
$flash = Seguridad::getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - DocuTrace</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #0f172a; }
        .login-card { width: 100%; max-width: 420px; background: #ffffff; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); overflow: hidden; }
        .login-header { background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #ffffff; padding: 32px 24px; text-align: center; }
        .login-header h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .login-header p { font-size: 13px; opacity: 0.9; }
        .login-body { padding: 28px; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h1>DocuTrace</h1>
        <p>Sistema de Rastreo y Trazabilidad Documental</p>
    </div>

    <div class="login-body">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['tipo']) ?>">
                <span><?= e($flash['mensaje']) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?c=auth&a=procesarLogin">
            <?= Seguridad::inputCsrf() ?>

            <div class="form-group">
                <label class="form-label" for="usuario">Usuario del Sistema:</label>
                <input type="text" id="usuario" name="usuario" class="form-control" required autofocus placeholder="Ej. admin o mesapartes">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 8px;">
                Ingresar al Sistema →
            </button>
        </form>

        <div style="margin-top: 24px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; line-height: 1.6; color: #475569;">
            <strong style="color: #1e293b; display: block; margin-bottom: 4px;">Acceso de Demostración:</strong>
            • <strong>Admin:</strong> <code>admin</code> / <code>admin123</code><br>
            • <strong>Mesa de Partes:</strong> <code>mesapartes</code> / <code>usuario123</code>
        </div>
    </div>
</div>

</body>
</html>
