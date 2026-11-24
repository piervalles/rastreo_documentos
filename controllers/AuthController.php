<?php
/**
 * controllers/AuthController.php - Control de Acceso y Sesión
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/Seguridad.php';

class AuthController {

    public function login(): void {
        if (isset($_SESSION['usuario_id'])) {
            header('Location: index.php?c=documentos&a=index');
            exit;
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function procesarLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        if (!Seguridad::validarCsrf($_POST['csrf_token'] ?? '')) {
            Seguridad::setFlash('error', 'Token de seguridad inválido. Intenta nuevamente.');
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($usuario) || empty($password)) {
            Seguridad::setFlash('error', 'Por favor ingresa usuario y contraseña.');
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $user = Usuario::autenticar($usuario, $password);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre_completo'];
            $_SESSION['usuario_login'] = $user['usuario'];
            $_SESSION['usuario_rol'] = $user['rol'];
            $_SESSION['departamento_id'] = $user['departamento_id'];
            $_SESSION['departamento_nombre'] = !empty($user['departamento_nombre']) ? $user['departamento_nombre'] : 'Sin área asignada';

            Seguridad::setFlash('success', "¡Bienvenido/a, {$user['nombre_completo']}!");
            header('Location: index.php?c=documentos&a=index');
            exit;
        } else {
            Seguridad::setFlash('error', 'Credenciales incorrectas o usuario inactivo.');
            header('Location: index.php?c=auth&a=login');
            exit;
        }
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: index.php?c=auth&a=login');
        exit;
    }
}
