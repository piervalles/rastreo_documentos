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
        $sql .= " ORDER BY es_sistema DESC, nombre ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function obtenerDepartamento(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM departamentos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function guardarDepartamento(array $datos, ?int $id = null): array {
        $pdo = Database::getConnection();
        $activo = !empty($datos['activo']) ? 1 : 0;

        if ($id) {
            $depto = self::obtenerDepartamento($id);
            if (!$depto) {
                return ['ok' => false, 'mensaje' => 'El departamento especificado no existe.'];
            }

            // Si es un departamento del sistema, solo se permite cambiar el estado (activo/inactivo)
            if (!empty($depto['es_sistema'])) {
                $stmt = $pdo->prepare("UPDATE departamentos SET activo = :activo WHERE id = :id");
                $stmt->execute([
                    'id'     => $id,
                    'activo' => $activo
                ]);
                $estadoTexto = $activo ? 'habilitado' : 'deshabilitado';
                return ['ok' => true, 'mensaje' => "El área base '{$depto['nombre']}' ha sido {$estadoTexto} correctamente."];
            }

            // Para departamentos creados por usuarios, se permite editar todos los campos
            $nombre = trim($datos['nombre'] ?? '');
            $codigo = strtoupper(trim($datos['codigo'] ?? ''));
            $descripcion = !empty($datos['descripcion']) ? trim($datos['descripcion']) : null;

            if ($nombre === '' || $codigo === '') {
                return ['ok' => false, 'mensaje' => 'El nombre y el código del área son campos obligatorios.'];
            }

            // Verificar que no se duplique el código con otro departamento
            $check = $pdo->prepare("SELECT id FROM departamentos WHERE codigo = :codigo AND id != :id");
            $check->execute(['codigo' => $codigo, 'id' => $id]);
            if ($check->fetch()) {
                return ['ok' => false, 'mensaje' => "El código o sigla '$codigo' ya está siendo utilizado por otra área."];
            }

            $stmt = $pdo->prepare("
                UPDATE departamentos 
                SET nombre = :nombre, codigo = :codigo, descripcion = :descripcion, activo = :activo 
                WHERE id = :id AND es_sistema = 0
            ");
            $stmt->execute([
                'id'          => $id,
                'nombre'      => $nombre,
                'codigo'      => $codigo,
                'descripcion' => $descripcion,
                'activo'      => $activo
            ]);

            return ['ok' => true, 'mensaje' => "El área '{$nombre}' fue actualizada exitosamente."];
        } else {
            // Alta de nueva área creada por el usuario (siempre es_sistema = 0)
            $nombre = trim($datos['nombre'] ?? '');
            $codigo = strtoupper(trim($datos['codigo'] ?? ''));
            $descripcion = !empty($datos['descripcion']) ? trim($datos['descripcion']) : null;

            if ($nombre === '' || $codigo === '') {
                return ['ok' => false, 'mensaje' => 'El nombre y el código del área son campos obligatorios.'];
            }

            $check = $pdo->prepare("SELECT id FROM departamentos WHERE codigo = :codigo");
            $check->execute(['codigo' => $codigo]);
            if ($check->fetch()) {
                return ['ok' => false, 'mensaje' => "El código o sigla '$codigo' ya está registrado en el catálogo."];
            }

            $stmt = $pdo->prepare("
                INSERT INTO departamentos (nombre, codigo, descripcion, activo, es_sistema) 
                VALUES (:nombre, :codigo, :descripcion, :activo, 0)
            ");
            $stmt->execute([
                'nombre'      => $nombre,
                'codigo'      => $codigo,
                'descripcion' => $descripcion,
                'activo'      => $activo
            ]);

            return ['ok' => true, 'mensaje' => "Nueva área '{$nombre}' registrada exitosamente."];
        }
    }

    public static function eliminarDepartamento(int $id): array {
        $pdo = Database::getConnection();
        $depto = self::obtenerDepartamento($id);

        if (!$depto) {
            return ['ok' => false, 'mensaje' => 'El departamento que intenta eliminar no existe.'];
        }

        // 1. Prohibir eliminación de departamentos del sistema
        if (!empty($depto['es_sistema'])) {
            return [
                'ok' => false,
                'es_sistema' => true,
                'mensaje' => "El área '{$depto['nombre']}' es un componente estructural base del sistema y no puede ser eliminada. Puede desactivarla para que no aparezca en las opciones operativas."
            ];
        }

        // 2. Comprobar integridad referencial (expedientes, movimientos, usuarios)
        $stmtDoc = $pdo->prepare("SELECT COUNT(*) FROM documentos WHERE departamento_actual_id = :id");
        $stmtDoc->execute(['id' => $id]);
        $totalDocs = (int)$stmtDoc->fetchColumn();

        $stmtMov = $pdo->prepare("SELECT COUNT(*) FROM movimientos_bitacora WHERE departamento_origen_id = :id1 OR departamento_destino_id = :id2");
        $stmtMov->execute(['id1' => $id, 'id2' => $id]);
        $totalMovs = (int)$stmtMov->fetchColumn();

        $stmtUsr = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE departamento_id = :id");
        $stmtUsr->execute(['id' => $id]);
        $totalUsrs = (int)$stmtUsr->fetchColumn();

        if ($totalDocs > 0 || $totalMovs > 0) {
            return [
                'ok' => false,
                'es_sistema' => false,
                'mensaje' => "No es posible eliminar el área '{$depto['nombre']}' porque ya cuenta con expedientes físicos o movimientos registrados en su historial. Para retirarla del uso, puede desactivarla."
            ];
        }

        if ($totalUsrs > 0) {
            return [
                'ok' => false,
                'es_sistema' => false,
                'mensaje' => "No es posible eliminar el área '{$depto['nombre']}' porque tiene usuarios asignados. Reasigne al personal antes de eliminarla o proceda a desactivarla."
            ];
        }

        // 3. Eliminar área de usuario
        $stmtDel = $pdo->prepare("DELETE FROM departamentos WHERE id = :id AND es_sistema = 0");
        $stmtDel->execute(['id' => $id]);

        return [
            'ok' => true,
            'mensaje' => "El departamento '{$depto['nombre']}' ha sido eliminado exitosamente del catálogo."
        ];
    }

    public static function cambiarEstadoDepartamento(int $id, int $activo): array {
        $pdo = Database::getConnection();
        $depto = self::obtenerDepartamento($id);
        if (!$depto) {
            return ['ok' => false, 'mensaje' => 'El departamento no existe.'];
        }

        $stmt = $pdo->prepare("UPDATE departamentos SET activo = :activo WHERE id = :id");
        $stmt->execute(['id' => $id, 'activo' => $activo ? 1 : 0]);

        $accionStr = $activo ? 'activada' : 'desactivada';
        return [
            'ok' => true,
            'mensaje' => "El área '{$depto['nombre']}' ha sido {$accionStr} correctamente."
        ];
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
