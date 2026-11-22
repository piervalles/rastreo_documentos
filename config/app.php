<?php
/**
 * Configuración General de la Aplicación
 */

// Iniciar sesión segura si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Detección dinámica de la URL base (funciona con localhost, IP o Hostname de red)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($protocol . $host . $scriptDir, '/');

return [
    'name'       => 'DocuTrace - Sistema de Trazabilidad Documental',
    'version'    => '1.0.0',
    'base_url'   => $baseUrl,
    'roles'      => [
        'ADMIN'   => 'admin',
        'USUARIO' => 'usuario'
    ],
    'timezone'   => 'America/Lima', // Ajustable según región
];
