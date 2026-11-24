<?php
/**
 * controllers/AdminController.php - Panel de Administración y Catálogos Dinámicos
 */

require_once __DIR__ . '/../models/Catalogo.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/Seguridad.php';
require_once __DIR__ . '/../helpers/View.php';

class AdminController {

    public function __construct() {
        Seguridad::exigirAdmin();
    }

    // ==========================================
    // DEPARTAMENTOS / OFICINAS
    // ==========================================
    public function departamentos(): void {
        $departamentos = Catalogo::listarDepartamentos(false);
        View::render('admin/departamentos', compact('departamentos'), 'Áreas y Departamentos');
    }

    public function guardarDepartamento(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Seguridad::validarCsrf($_POST['csrf_token'] ?? '')) {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $res = Catalogo::guardarDepartamento($_POST, $id);
            if ($res['ok']) {
                Seguridad::setFlash('success', $res['mensaje']);
            } else {
                Seguridad::setFlash('error', $res['mensaje']);
            }
        } else {
            Seguridad::setFlash('error', 'Error de seguridad al procesar la solicitud (Token CSRF inválido).');
        }
        header('Location: index.php?c=admin&a=departamentos');
        exit;
    }

    public function eliminarDepartamento(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Seguridad::validarCsrf($_POST['csrf_token'] ?? '')) {
            $id = (int)($_POST['id'] ?? 0);
            $res = Catalogo::eliminarDepartamento($id);
            if ($res['ok']) {
                Seguridad::setFlash('success', $res['mensaje']);
            } else {
                Seguridad::setFlash('error', $res['mensaje']);
            }
        } else {
            Seguridad::setFlash('error', 'Token de seguridad inválido o petición no permitida.');
        }
        header('Location: index.php?c=admin&a=departamentos');
        exit;
    }

    public function cambiarEstadoDepartamento(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Seguridad::validarCsrf($_POST['csrf_token'] ?? '')) {
            $id = (int)($_POST['id'] ?? 0);
            $activo = (int)($_POST['activo'] ?? 0);
            $res = Catalogo::cambiarEstadoDepartamento($id, $activo);
            if ($res['ok']) {
                Seguridad::setFlash('success', $res['mensaje']);
            } else {
                Seguridad::setFlash('error', $res['mensaje']);
            }
        } else {
            Seguridad::setFlash('error', 'Token de seguridad inválido.');
        }
        header('Location: index.php?c=admin&a=departamentos');
        exit;
    }

    // ==========================================
    // ESTADOS DE TRÁMITE Y RESOLUCIÓN
    // ==========================================
    public function estados(): void {
        $estadosTramite = Catalogo::listarEstadosTramite(false);
        $estadosResolucion = Catalogo::listarEstadosResolucion(false);
        View::render('admin/estados', compact('estadosTramite', 'estadosResolucion'), 'Catálogos de Estados');
    }

    public function guardarEstadoTramite(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Seguridad::validarCsrf($_POST['csrf_token'] ?? '')) {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            Catalogo::guardarEstadoTramite($_POST, $id);
            Seguridad::setFlash('success', 'Estado de trámite guardado correctamente.');
        }
        header('Location: index.php?c=admin&a=estados');
        exit;
    }

    public function guardarEstadoResolucion(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Seguridad::validarCsrf($_POST['csrf_token'] ?? '')) {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            Catalogo::guardarEstadoResolucion($_POST, $id);
            Seguridad::setFlash('success', 'Estado de resolución guardado correctamente.');
        }
        header('Location: index.php?c=admin&a=estados');
        exit;
    }

    // ==========================================
    // USUARIOS
    // ==========================================
    public function usuarios(): void {
        $usuarios = Usuario::listar();
        $departamentos = Catalogo::listarDepartamentos(true);
        View::render('admin/usuarios', compact('usuarios', 'departamentos'), 'Personal y Usuarios');
    }

    public function guardarUsuario(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Seguridad::validarCsrf($_POST['csrf_token'] ?? '')) {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if ($id) {
                Usuario::actualizar($id, $_POST);
                Seguridad::sincronizarUsuarioSesion();
                Seguridad::setFlash('success', 'Usuario actualizado correctamente.');
            } else {
                Usuario::crear($_POST);
                Seguridad::setFlash('success', 'Nuevo usuario creado correctamente.');
            }
        }
        header('Location: index.php?c=admin&a=usuarios');
        exit;
    }
}
