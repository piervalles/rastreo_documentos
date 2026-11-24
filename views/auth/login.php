<?php
/**
 * views/auth/login.php - Pantalla de Inicio de Sesión Institucional (Machu Picchu & Gobierno del Perú)
 */
require_once __DIR__ . '/../../helpers/Seguridad.php';
$flash = Seguridad::getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión • Gobierno del Perú</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        
        /* FONDO PANORÁMICO MACHU PICCHU SIN SCROLL */
        html, body {
            height: 100vh;
            overflow: hidden;
        }

        body { 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: center; 
            background: 
                linear-gradient(135deg, rgba(15, 23, 42, 0.70) 0%, rgba(30, 41, 59, 0.65) 50%, rgba(15, 23, 42, 0.80) 100%),
                url('https://images.unsplash.com/photo-1526392060635-9d6019884377?auto=format&fit=crop&w=1920&q=85') center/cover no-repeat;
            background-color: #0f172a;
            padding: 16px;
            color: #1e293b;
        }

        /* FRANJA SUPERIOR BICOLOR NACIONAL */
        .national-stripe {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #b91c1c 0%, #b91c1c 33%, #ffffff 33%, #ffffff 66%, #b91c1c 66%, #b91c1c 100%);
            z-index: 100;
        }

        /* TARJETA DE ACCESO COMPACTA Y SIN SOMBRAS (PLANA) */
        .login-card { 
            width: 100%; 
            max-width: 390px; 
            background: rgba(255, 255, 255, 0.96); 
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 12px; 
            box-shadow: none; /* Cero sombreados pesados */
            overflow: hidden; 
        }

        /* CABECERA INTEGRADA SOBRIA CON ESCUDO */
        .card-header {
            padding: 22px 24px 14px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .header-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .header-brand .escudo-peru {
            width: 36px;
            height: 42px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .header-brand h2 {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #b91c1c; /* Rojo Patrio */
            margin: 0;
            line-height: 1.1;
        }

        .card-header h1 {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .card-header p {
            font-size: 12px;
            color: #64748b;
        }

        /* CUERPO DEL FORMULARIO COMPACTO */
        .card-body {
            padding: 18px 24px 20px;
        }

        .form-group {
            margin-bottom: 13px;
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #334155;
            margin-bottom: 5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            width: 16px;
            height: 16px;
            stroke: #64748b;
            fill: none;
            stroke-width: 2;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px 9px 38px;
            font-size: 13px;
            border: 1px solid #94a3b8;
            border-radius: 6px;
            background: #ffffff;
            color: #0f172a;
            outline: none;
            transition: border-color 0.15s ease;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
        }

        /* BOTÓN DE COLORES FRÍOS PLANO (SIN SOMBRAS) */
        .btn-cold {
            width: 100%;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #ffffff;
            background: #2563eb; /* Azul Frío Sólido */
            border: none;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: none; /* Cero sombreados */
            transition: background 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 6px;
        }

        .btn-cold:hover {
            background: #1d4ed8;
        }

        /* ALERTA FLASH */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 3px solid #ef4444;
            color: #991b1b;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        /* CUADRO DE CREDENCIALES COMPACTO */
        .demo-box {
            margin-top: 14px;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 11px;
            line-height: 1.5;
            color: #475569;
        }

        .demo-box strong {
            color: #0f172a;
            display: block;
            margin-bottom: 2px;
            font-size: 10px;
            text-transform: uppercase;
        }

        .demo-box code {
            font-family: monospace;
            background: #e2e8f0;
            color: #1e293b;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: 700;
        }

        /* PIE DE PÁGINA SOBRIO */
        .page-footer {
            margin-top: 14px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.85);
            text-align: center;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>

<!-- Franja Bicolor Superior -->
<div class="national-stripe"></div>

<!-- TARJETA DE ACCESO PERFECTAMENTE ENCUADRADA (SIN SCROLL) -->
<main class="login-card">
    <div class="card-header">
        <div class="header-brand">
            <!-- Escudo Nacional del Perú Oficial -->
            <img src="assets/img/escudo_peru.svg" alt="Escudo Nacional del Perú" class="escudo-peru">
            <h2>Gobierno del Perú</h2>
        </div>
        <h1>Control Documental</h1>
        <p>Sistema de Trazabilidad y Custodia de Expedientes</p>
    </div>

    <div class="card-body">
        <?php if ($flash): ?>
            <div class="alert-error">
                <span><?= e($flash['mensaje']) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?c=auth&a=procesarLogin">
            <?= Seguridad::inputCsrf() ?>

            <div class="form-group">
                <label class="form-label" for="usuario">Usuario</label>
                <div class="input-wrapper">
                    <svg class="input-icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <input type="text" id="usuario" name="usuario" class="form-control" required autofocus placeholder="Usuario o área">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-wrapper">
                    <svg class="input-icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
            </div>

            <!-- BOTÓN PLANO EN COLOR AZUL FRÍO -->
            <button type="submit" class="btn-cold">
                <span>Ingresar</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        </form>
    </div>
</main>

<footer class="page-footer">
    República del Perú • Intranet de Trámite Documentario
</footer>

</body>
</html>
