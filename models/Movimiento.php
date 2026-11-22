<?php
/**
 * models/Movimiento.php - Motor de Movimientos, Trazabilidad Inmutable y OCC
 */

require_once __DIR__ . '/Database.php';

class ConcurrenciaException extends Exception {}

class Movimiento {

    /**
     * Registra un movimiento de documento con Control de Concurrencia Optimista (OCC)
     * e integridad transaccional ACID.
     */
    public static function registrar(array $datos, int $usuarioId): bool {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $docId = (int)$datos['documento_id'];
            $versionEsperada = (int)$datos['version_occ'];

            // 1. CONTROL DE CONCURRENCIA OPTIMISTA (OCC)
            // Consultamos la versión actual del documento bloqueando la fila durante la transacción
            $stmtCheck = $pdo->prepare("SELECT version_occ, departamento_actual_id, total_revisiones FROM documentos WHERE id = :id FOR UPDATE");
            $stmtCheck->execute(['id' => $docId]);
            $docActual = $stmtCheck->fetch();

            if (!$docActual) {
                throw new Exception("El documento solicitado no existe.");
            }

            if ((int)$docActual['version_occ'] !== $versionEsperada) {
                // Alguien modificó el documento mientras este usuario lo tenía en pantalla
                throw new ConcurrenciaException("Conflicto de Concurrencia: El documento fue actualizado por otro usuario hace unos momentos. Los datos en pantalla estaban desfasados.");
            }

            // 2. Preparar nuevos valores
            $nuevaVersion = $versionEsperada + 1;
            $nuevasRevisiones = (int)$docActual['total_revisiones'] + 1;
            $deptoOrigen = (int)$docActual['departamento_actual_id'];
            $deptoDestino = !empty($datos['departamento_destino_id']) ? (int)$datos['departamento_destino_id'] : $deptoOrigen;
            $nuevoCustodio = !empty($datos['custodio_id']) ? (int)$datos['custodio_id'] : $usuarioId;

            // 3. Actualizar la tabla documentos
            $stmtUpdate = $pdo->prepare("
                UPDATE documentos SET
                    departamento_actual_id = :depto_destino,
                    custodio_id = :custodio,
                    estado_tramite_id = :estado_tramite,
                    estado_resolucion_id = :estado_resolucion,
                    tiene_sello_fisico = :sello,
                    tiene_firma_fisica = :firma,
                    total_revisiones = :revisiones,
                    version_occ = :nueva_version,
                    updated_at = NOW()
                WHERE id = :id AND version_occ = :version_esperada
            ");

            $resultado = $stmtUpdate->execute([
                'id'                 => $docId,
                'version_esperada'   => $versionEsperada,
                'nueva_version'      => $nuevaVersion,
                'depto_destino'      => $deptoDestino,
                'custodio'           => $nuevoCustodio,
                'estado_tramite'     => $datos['estado_tramite_id'],
                'estado_resolucion'  => $datos['estado_resolucion_id'],
                'sello'              => isset($datos['sello_verificado']) ? 1 : 0,
                'firma'              => isset($datos['firma_verificada']) ? 1 : 0,
                'revisiones'         => $nuevasRevisiones
            ]);

            if ($stmtUpdate->rowCount() === 0) {
                throw new ConcurrenciaException("Conflicto de Concurrencia al actualizar el registro.");
            }

            // 4. Inserción obligatoria en la Bitácora Inmutable (Append-Only)
            $stmtBitacora = $pdo->prepare("
                INSERT INTO movimientos_bitacora (
                    documento_id, departamento_origen_id, departamento_destino_id,
                    usuario_id, accion_id, estado_tramite_id, estado_resolucion_id,
                    observacion, sello_verificado, firma_verificada, numero_revision,
                    version_en_movimiento, fecha_registro
                ) VALUES (
                    :doc_id, :origen, :destino, :usuario, :accion, :est_tramite,
                    :est_resolucion, :obs, :sello, :firma, :revision, :version, NOW()
                )
            ");

            $stmtBitacora->execute([
                'doc_id'         => $docId,
                'origen'         => $deptoOrigen,
                'destino'        => $deptoDestino,
                'usuario'        => $usuarioId,
                'accion'         => $datos['accion_id'],
                'est_tramite'    => $datos['estado_tramite_id'],
                'est_resolucion' => $datos['estado_resolucion_id'],
                'obs'            => !empty($datos['observacion']) ? trim($datos['observacion']) : null,
                'sello'          => isset($datos['sello_verificado']) ? 1 : 0,
                'firma'          => isset($datos['firma_verificada']) ? 1 : 0,
                'revision'       => $nuevasRevisiones,
                'version'        => $nuevaVersion
            ]);

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene el historial completo e inmutable de movimientos de un documento (Timeline)
     */
    public static function obtenerHistorial(int $documentoId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT m.*,
                   d_orig.nombre AS depto_origen_nombre, d_orig.codigo AS depto_origen_codigo,
                   d_dest.nombre AS depto_destino_nombre, d_dest.codigo AS depto_destino_codigo,
                   u.nombre_completo AS usuario_nombre, u.usuario AS usuario_login,
                   a.nombre AS accion_nombre, a.comprueba_sello, a.comprueba_firma,
                   et.nombre AS estado_tramite_nombre, et.color AS estado_tramite_color,
                   er.nombre AS estado_resolucion_nombre, er.color AS estado_resolucion_color
            FROM movimientos_bitacora m
            INNER JOIN departamentos d_orig ON m.departamento_origen_id = d_orig.id
            INNER JOIN departamentos d_dest ON m.departamento_destino_id = d_dest.id
            INNER JOIN usuarios u ON m.usuario_id = u.id
            INNER JOIN acciones_movimiento a ON m.accion_id = a.id
            INNER JOIN estados_tramite et ON m.estado_tramite_id = et.id
            INNER JOIN estados_resolucion er ON m.estado_resolucion_id = er.id
            WHERE m.documento_id = :doc_id
            ORDER BY m.fecha_registro ASC, m.id ASC
        ");
        $stmt->execute(['doc_id' => $documentoId]);
        return $stmt->fetchAll();
    }
}
