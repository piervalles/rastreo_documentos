<?php
/**
 * helpers/Seguridad.php - Utilidades de Seguridad, Sanitización y CSRF
 */

class Seguridad {
    /**
     * Sanitiza cadenas para prevenir ataques XSS
     */
    public static function sanitizar(?string $valor): string {
        return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Genera un token CSRF para formularios
     */
    public static function generarCsrf(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token CSRF enviado por POST
     */
    public static function validarCsrf(?string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Retorna el input oculto de CSRF listo para formularios
     */
    public static function inputCsrf(): string {
        $token = self::generarCsrf();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Comprueba si el usuario autenticado tiene rol de Administrador
     */
    public static function esAdmin(): bool {
        return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
    }

    /**
     * Exige que el usuario esté autenticado. Redirige a login si no lo está.
     */
    public static function exigirAutenticado(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }
    }

    /**
     * Exige que el usuario sea Administrador
     */
    public static function exigirAdmin(): void {
        self::exigirAutenticado();
        if (!self::esAdmin()) {
            http_response_code(403);
            die("Acceso Denegado: Esta acción requiere privilegios de Administrador.");
        }
    }

    /**
     * Mensajes Flash (notificaciones temporales de éxito/error)
     */
    public static function setFlash(string $tipo, string $mensaje): void {
        $_SESSION['flash'] = [
            'tipo' => $tipo, // 'success', 'error', 'warning', 'info'
            'mensaje' => $mensaje
        ];
    }

    public static function getFlash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

// Función helper abreviada para imprimir seguro
function e(?string $valor): string {
    return Seguridad::sanitizar($valor);
}
