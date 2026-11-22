<?php
/**
 * index.php - Enrutador Frontal y Punto de Entrada
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/models/Database.php';

// 1. VERIFICACIÓN DE INSTALACIÓN CODE-FIRST
try {
    $pdo = Database::getConnection();
    // Verificar si existe la tabla usuarios
    $test = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($test->rowCount() === 0) {
        header('Location: instalar.php');
        exit;
    }
} catch (Exception $e) {
    // Si no conecta o la base de datos no existe, redirigir al instalador
    header('Location: instalar.php');
    exit;
}

// 2. ENRUTAMIENTO DINÁMICO
$controladorNombre = $_GET['c'] ?? (isset($_SESSION['usuario_id']) ? 'documentos' : 'auth');
$accion = $_GET['a'] ?? (isset($_SESSION['usuario_id']) ? 'index' : 'login');

// Mapeo seguro de controladores
$controladores = [
    'auth'        => 'AuthController',
    'documentos'  => 'DocumentoController',
    'movimientos' => 'MovimientoController',
    'admin'       => 'AdminController',
    'backup'      => 'BackupController'
];

if (!array_key_exists($controladorNombre, $controladores)) {
    http_response_code(404);
    die("Error 404: Módulo no encontrado.");
}

$clase = $controladores[$controladorNombre];
$archivoControlador = __DIR__ . "/controllers/{$clase}.php";

if (!file_exists($archivoControlador)) {
    http_response_code(404);
    die("Error 404: Archivo controlador no existe.");
}

require_once $archivoControlador;

if (!class_exists($clase)) {
    http_response_code(500);
    die("Error 500: Clase controladora no implementada.");
}

$controladorInstancia = new $clase();

if (!method_exists($controladorInstancia, $accion)) {
    http_response_code(404);
    die("Error 404: Acción '{$accion}' no existe en el controlador.");
}

// Ejecutar la acción
$controladorInstancia->$accion();
