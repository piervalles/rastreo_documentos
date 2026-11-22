<?php
/**
 * models/Usuario.php - Gestión de Usuarios y Autenticación
 */

require_once __DIR__ . '/Database.php';

class Usuario {
    public static function autenticar(string $usuario, string $password): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, d.nombre AS departamento_nombre, d.codigo AS departamento_codigo
            FROM usuarios u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            WHERE u.usuario = :usuario AND u.activo = 1
            LIMIT 1
        ");
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Actualizar último acceso
            $update = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
            $update->execute(['id' => $user['id']]);
            
            unset($user['password_hash']);
            return $user;
        }

        return null;
    }

    public static function obtenerPorId(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre_completo, u.usuario, u.email, u.rol, u.departamento_id, u.activo, u.ultimo_acceso,
                   d.nombre AS departamento_nombre
            FROM usuarios u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            WHERE u.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function listar(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT u.id, u.nombre_completo, u.usuario, u.email, u.rol, u.departamento_id, u.activo, u.ultimo_acceso,
                   d.nombre AS departamento_nombre
            FROM usuarios u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            ORDER BY u.id ASC
        ");
        return $stmt->fetchAll();
    }

    public static function crear(array $datos): bool {
        $pdo = Database::getConnection();
        $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre_completo, usuario, email, password_hash, rol, departamento_id, activo)
            VALUES (:nombre, :usuario, :email, :password, :rol, :departamento_id, :activo)
        ");

        return $stmt->execute([
            'nombre'          => $datos['nombre_completo'],
            'usuario'         => $datos['usuario'],
            'email'           => $datos['email'] ?? null,
            'password'        => $passwordHash,
            'rol'             => $datos['rol'] ?? 'usuario',
            'departamento_id' => !empty($datos['departamento_id']) ? $datos['departamento_id'] : null,
            'activo'          => isset($datos['activo']) ? 1 : 0
        ]);
    }

    public static function actualizar(int $id, array $datos): bool {
        $pdo = Database::getConnection();
        
        if (!empty($datos['password'])) {
            $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET nombre_completo = :nombre, email = :email, password_hash = :password,
                    rol = :rol, departamento_id = :departamento_id, activo = :activo
                WHERE id = :id
            ");
            return $stmt->execute([
                'id'              => $id,
                'nombre'          => $datos['nombre_completo'],
                'email'           => $datos['email'] ?? null,
                'password'        => $passwordHash,
                'rol'             => $datos['rol'],
                'departamento_id' => !empty($datos['departamento_id']) ? $datos['departamento_id'] : null,
                'activo'          => isset($datos['activo']) ? 1 : 0
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET nombre_completo = :nombre, email = :email, rol = :rol,
                    departamento_id = :departamento_id, activo = :activo
                WHERE id = :id
            ");
            return $stmt->execute([
                'id'              => $id,
                'nombre'          => $datos['nombre_completo'],
                'email'           => $datos['email'] ?? null,
                'rol'             => $datos['rol'],
                'departamento_id' => !empty($datos['departamento_id']) ? $datos['departamento_id'] : null,
                'activo'          => isset($datos['activo']) ? 1 : 0
            ]);
        }
    }
}
