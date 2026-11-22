<?php
/**
 * models/Documento.php - Gestión de Documentos Físicos y Control de Concurrencia
 */

require_once __DIR__ . '/Database.php';

class Documento {

    /**
     * Genera un folio único correlativo anual (ej. EXP-2026-0001)
     */
    public static function generarFolio(): string {
        $pdo = Database::getConnection();
        $anio = date('Y');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documentos WHERE folio_unico LIKE :patron");
        $stmt->execute(['patron' => "EXP-{$anio}-%"]);
        $conteo = $stmt->fetchColumn() + 1;
        return sprintf("EXP-%s-%04d", $anio, $conteo);
    }

    /**
     * Registra un nuevo documento físico e inserta su primer movimiento en bitácora (Transaccional)
     */
    public static function crear(array $datos, int $usuarioId): int {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // Folio: usar el ingresado manualmente o generar uno automático
            $folio = !empty($datos['folio_manual']) ? trim($datos['folio_manual']) : self::generarFolio();

            // Verificar que no exista el folio
            $check = $pdo->prepare("SELECT id FROM documentos WHERE folio_unico = :folio");
            $check->execute(['folio' => $folio]);
            if ($check->fetch()) {
                throw new Exception("El código o folio '{$folio}' ya se encuentra registrado en el sistema.");
            }

            // Insertar documento
            $stmt = $pdo->prepare("
                INSERT INTO documentos (
                    folio_unico, asunto, remitente, entidad_origen, tipo_id,
                    estado_tramite_id, estado_resolucion_id, departamento_actual_id,
                    custodio_id, documento_padre_id, version_occ, tiene_sello_fisico,
                    tiene_firma_fisica, total_revisiones, observaciones_iniciales
                ) VALUES (
                    :folio, :asunto, :remitente, :entidad, :tipo_id,
                    :estado_tramite, :estado_resolucion, :departamento,
                    :custodio, :padre_id, 1, :sello, :firma, 1, :observaciones
                )
            ");

            $stmt->execute([
                'folio'             => $folio,
                'asunto'            => trim($datos['asunto']),
                'remitente'         => trim($datos['remitente']),
                'entidad'           => !empty($datos['entidad_origen']) ? trim($datos['entidad_origen']) : null,
                'tipo_id'           => $datos['tipo_id'],
                'estado_tramite'    => $datos['estado_tramite_id'],
                'estado_resolucion' => $datos['estado_resolucion_id'],
                'departamento'      => $datos['departamento_actual_id'],
                'custodio'          => $usuarioId,
                'padre_id'          => !empty($datos['documento_padre_id']) ? (int)$datos['documento_padre_id'] : null,
                'sello'             => isset($datos['tiene_sello_fisico']) ? 1 : 0,
                'firma'             => isset($datos['tiene_firma_fisica']) ? 1 : 0,
                'observaciones'     => !empty($datos['observaciones']) ? trim($datos['observaciones']) : null
            ]);

            $documentoId = (int)$pdo->lastInsertId();

            // Insertar el primer registro en la bitácora histórica inmutable
            $stmtBitacora = $pdo->prepare("
                INSERT INTO movimientos_bitacora (
                    documento_id, departamento_origen_id, departamento_destino_id,
                    usuario_id, accion_id, estado_tramite_id, estado_resolucion_id,
                    observacion, sello_verificado, firma_verificada, numero_revision,
                    version_en_movimiento, fecha_registro
                ) VALUES (
                    :doc_id, :origen, :destino, :usuario, :accion, :est_tramite,
                    :est_resolucion, :obs, :sello, :firma, 1, 1, NOW()
                )
            ");

            // Acción por defecto: Registro Inicial (ID 1)
            $stmtBitacora->execute([
                'doc_id'         => $documentoId,
                'origen'         => $datos['departamento_actual_id'],
                'destino'        => $datos['departamento_actual_id'],
                'usuario'        => $usuarioId,
                'accion'         => 1, // Registro Inicial
                'est_tramite'    => $datos['estado_tramite_id'],
                'est_resolucion' => $datos['estado_resolucion_id'],
                'obs'            => 'Ingreso y registro inicial del expediente físico en el sistema.',
                'sello'          => isset($datos['tiene_sello_fisico']) ? 1 : 0,
                'firma'          => isset($datos['tiene_firma_fisica']) ? 1 : 0
            ]);

            $pdo->commit();
            return $documentoId;

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene el detalle completo de un documento
     */
    public static function obtenerPorId(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT d.*, 
                   t.nombre AS tipo_nombre,
                   et.nombre AS estado_tramite_nombre, et.color AS estado_tramite_color,
                   er.nombre AS estado_resolucion_nombre, er.color AS estado_resolucion_color,
                   dep.nombre AS departamento_nombre, dep.codigo AS departamento_codigo,
                   u.nombre_completo AS custodio_nombre,
                   padre.folio_unico AS documento_padre_folio
            FROM documentos d
            INNER JOIN tipos_documento t ON d.tipo_id = t.id
            INNER JOIN estados_tramite et ON d.estado_tramite_id = et.id
            INNER JOIN estados_resolucion er ON d.estado_resolucion_id = er.id
            INNER JOIN departamentos dep ON d.departamento_actual_id = dep.id
            LEFT JOIN usuarios u ON d.custodio_id = u.id
            LEFT JOIN documentos padre ON d.documento_padre_id = padre.id
            WHERE d.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Obtiene documentos derivados / hijos
     */
    public static function listarHijos(int $padreId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT d.id, d.folio_unico, d.asunto, d.remitente, d.created_at,
                   et.nombre AS estado_tramite_nombre, et.color AS estado_tramite_color,
                   dep.nombre AS departamento_nombre
            FROM documentos d
            INNER JOIN estados_tramite et ON d.estado_tramite_id = et.id
            INNER JOIN departamentos dep ON d.departamento_actual_id = dep.id
            WHERE d.documento_padre_id = :padre_id
            ORDER BY d.id ASC
        ");
        $stmt->execute(['padre_id' => $padreId]);
        return $stmt->fetchAll();
    }

    /**
     * Bandeja de Documentos (Filtrable por Departamento o global)
     */
    public static function listarBandeja(?int $departamentoId = null, array $filtros = []): array {
        $pdo = Database::getConnection();
        
        $sql = "
            SELECT d.*, 
                   t.nombre AS tipo_nombre,
                   et.nombre AS estado_tramite_nombre, et.color AS estado_tramite_color,
                   er.nombre AS estado_resolucion_nombre, er.color AS estado_resolucion_color,
                   dep.nombre AS departamento_nombre, dep.codigo AS departamento_codigo,
                   u.nombre_completo AS custodio_nombre
            FROM documentos d
            INNER JOIN tipos_documento t ON d.tipo_id = t.id
            INNER JOIN estados_tramite et ON d.estado_tramite_id = et.id
            INNER JOIN estados_resolucion er ON d.estado_resolucion_id = er.id
            INNER JOIN departamentos dep ON d.departamento_actual_id = dep.id
            LEFT JOIN usuarios u ON d.custodio_id = u.id
            WHERE 1=1
        ";

        $params = [];

        if ($departamentoId !== null) {
            $sql .= " AND d.departamento_actual_id = :depto_id";
            $params['depto_id'] = $departamentoId;
        }

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (d.folio_unico LIKE :buscar OR d.asunto LIKE :buscar OR d.remitente LIKE :buscar)";
            $params['buscar'] = '%' . trim($filtros['buscar']) . '%';
        }

        if (!empty($filtros['estado_tramite_id'])) {
            $sql .= " AND d.estado_tramite_id = :est_tramite";
            $params['est_tramite'] = $filtros['estado_tramite_id'];
        }

        if (!empty($filtros['estado_resolucion_id'])) {
            $sql .= " AND d.estado_resolucion_id = :est_resolucion";
            $params['est_resolucion'] = $filtros['estado_resolucion_id'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(d.created_at) >= :fecha_desde";
            $params['fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(d.created_at) <= :fecha_hasta";
            $params['fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= " ORDER BY d.updated_at DESC, d.id DESC LIMIT 100";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
