<?php
/**
 * models/Catalogo.php - Gestión de Catálogos Configurables Dinámicamente
 */

require_once __DIR__ . '/Database.php';

class Catalogo {

    // ==========================================
    // DEPARTAMENTOS / OFICINAS
    // ==========================================
    public static function listarDepartamentos(bool $soloActivos = false): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM departamentos";
        if ($soloActivos) $sql .= " WHERE activo = 1";
        $sql .= " ORDER BY nombre ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function guardarDepartamento(array $datos, ?int $id = null): bool {
        $pdo = Database::getConnection();
        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE departamentos 
                SET nombre = :nombre, codigo = :codigo, descripcion = :descripcion, activo = :activo 
                WHERE id = :id
            ");
            return $stmt->execute([
                'id'          => $id,
                'nombre'      => $datos['nombre'],
                'codigo'      => strtoupper(trim($datos['codigo'])),
                'descripcion' => $datos['descripcion'] ?? null,
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO departamentos (nombre, codigo, descripcion, activo) 
                VALUES (:nombre, :codigo, :descripcion, :activo)
            ");
            return $stmt->execute([
                'nombre'      => $datos['nombre'],
                'codigo'      => strtoupper(trim($datos['codigo'])),
                'descripcion' => $datos['descripcion'] ?? null,
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        }
    }

    // ==========================================
    // ESTADOS DE TRÁMITE (OPERATIVO)
    // ==========================================
    public static function listarEstadosTramite(bool $soloActivos = false): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM estados_tramite";
        if ($soloActivos) $sql .= " WHERE activo = 1";
        $sql .= " ORDER BY orden ASC, nombre ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function guardarEstadoTramite(array $datos, ?int $id = null): bool {
        $pdo = Database::getConnection();
        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE estados_tramite 
                SET nombre = :nombre, color = :color, descripcion = :descripcion, orden = :orden, activo = :activo 
                WHERE id = :id
            ");
            return $stmt->execute([
                'id'          => $id,
                'nombre'      => $datos['nombre'],
                'color'       => $datos['color'] ?? 'gray',
                'descripcion' => $datos['descripcion'] ?? null,
                'orden'       => (int)($datos['orden'] ?? 0),
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO estados_tramite (nombre, color, descripcion, orden, activo) 
                VALUES (:nombre, :color, :descripcion, :orden, :activo)
            ");
            return $stmt->execute([
                'nombre'      => $datos['nombre'],
                'color'       => $datos['color'] ?? 'gray',
                'descripcion' => $datos['descripcion'] ?? null,
                'orden'       => (int)($datos['orden'] ?? 0),
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        }
    }

    // ==========================================
    // ESTADOS DE RESOLUCIÓN (DICTAMEN / FONDO)
    // ==========================================
    public static function listarEstadosResolucion(bool $soloActivos = false): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM estados_resolucion";
        if ($soloActivos) $sql .= " WHERE activo = 1";
        $sql .= " ORDER BY orden ASC, nombre ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function guardarEstadoResolucion(array $datos, ?int $id = null): bool {
        $pdo = Database::getConnection();
        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE estados_resolucion 
                SET nombre = :nombre, color = :color, descripcion = :descripcion, orden = :orden, activo = :activo 
                WHERE id = :id
            ");
            return $stmt->execute([
                'id'          => $id,
                'nombre'      => $datos['nombre'],
                'color'       => $datos['color'] ?? 'gray',
                'descripcion' => $datos['descripcion'] ?? null,
                'orden'       => (int)($datos['orden'] ?? 0),
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO estados_resolucion (nombre, color, descripcion, orden, activo) 
                VALUES (:nombre, :color, :descripcion, :orden, :activo)
            ");
            return $stmt->execute([
                'nombre'      => $datos['nombre'],
                'color'       => $datos['color'] ?? 'gray',
                'descripcion' => $datos['descripcion'] ?? null,
                'orden'       => (int)($datos['orden'] ?? 0),
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        }
    }

    // ==========================================
    // TIPOS DE DOCUMENTO
    // ==========================================
    public static function listarTiposDocumento(bool $soloActivos = false): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM tipos_documento";
        if ($soloActivos) $sql .= " WHERE activo = 1";
        $sql .= " ORDER BY nombre ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function guardarTipoDocumento(array $datos, ?int $id = null): bool {
        $pdo = Database::getConnection();
        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE tipos_documento 
                SET nombre = :nombre, descripcion = :descripcion, activo = :activo 
                WHERE id = :id
            ");
            return $stmt->execute([
                'id'          => $id,
                'nombre'      => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO tipos_documento (nombre, descripcion, activo) 
                VALUES (:nombre, :descripcion, :activo)
            ");
            return $stmt->execute([
                'nombre'      => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        }
    }

    // ==========================================
    // ACCIONES REGISTRABLES
    // ==========================================
    public static function listarAcciones(bool $soloActivas = false): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM acciones_movimiento";
        if ($soloActivas) $sql .= " WHERE activo = 1";
        $sql .= " ORDER BY nombre ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function guardarAccion(array $datos, ?int $id = null): bool {
        $pdo = Database::getConnection();
        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE acciones_movimiento 
                SET nombre = :nombre, descripcion = :descripcion, comprueba_sello = :sello,
                    comprueba_firma = :firma, activo = :activo 
                WHERE id = :id
            ");
            return $stmt->execute([
                'id'          => $id,
                'nombre'      => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'sello'       => isset($datos['comprueba_sello']) ? 1 : 0,
                'firma'       => isset($datos['comprueba_firma']) ? 1 : 0,
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO acciones_movimiento (nombre, descripcion, comprueba_sello, comprueba_firma, activo) 
                VALUES (:nombre, :descripcion, :sello, :firma, :activo)
            ");
            return $stmt->execute([
                'nombre'      => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'sello'       => isset($datos['comprueba_sello']) ? 1 : 0,
                'firma'       => isset($datos['comprueba_firma']) ? 1 : 0,
                'activo'      => isset($datos['activo']) ? 1 : 0
            ]);
        }
    }
}
