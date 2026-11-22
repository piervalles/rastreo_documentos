<?php
/**
 * Seeders.php - Datos Iniciales del Sistema
 * Carga usuarios por defecto, catálogos estándar y configuración base.
 */

class Seeders {
    public static function run(PDO $pdo): array {
        $logs = [];

        // 1. Departamentos iniciales
        $deptos = [
            ['Mesa de Partes', 'MP', 'Recepción y despacho general de documentos'],
            ['Gerencia General', 'GG', 'Dirección institucional y toma de decisiones'],
            ['Asesoría Legal', 'AL', 'Dictámenes jurídicos y revisión normativa'],
            ['Contabilidad y Finanzas', 'CF', 'Presupuesto, pagos y fiscalización'],
            ['Recursos Humanos', 'RRHH', 'Gestión de personal y planillas'],
            ['Archivo Central', 'AC', 'Custodia y archivado definitivo de expedientes']
        ];
        $stmtDepto = $pdo->prepare("INSERT IGNORE INTO departamentos (nombre, codigo, descripcion) VALUES (?, ?, ?)");
        foreach ($deptos as $d) {
            $stmtDepto->execute($d);
        }
        $logs[] = "Departamentos base inicializados (" . count($deptos) . " áreas).";

        // 2. Estados de Trámite (Situación Operativa)
        $estadosTramite = [
            ['Recibido', 'emerald', 'Documento en poder físico de la oficina', 1],
            ['En traslado', 'amber', 'Documento en camino físico entre oficinas', 2],
            ['En revisión', 'blue', 'En análisis o evaluación activa por el personal', 3],
            ['Respondido', 'purple', 'Trámite con respuesta emitida o derivada', 4],
            ['Devuelto', 'rose', 'Devuelto al área de origen para subsanaciones', 5],
            ['Archivado', 'slate', 'Expediente concluido y en custodia pasiva', 6]
        ];
        $stmtET = $pdo->prepare("INSERT IGNORE INTO estados_tramite (nombre, color, descripcion, orden) VALUES (?, ?, ?, ?)");
        foreach ($estadosTramite as $et) {
            $stmtET->execute($et);
        }
        $logs[] = "Estados de trámite operativo inicializados (" . count($estadosTramite) . " estados).";

        // 3. Estados de Resolución (Dictamen / Fondo)
        $estadosResolucion = [
            ['Pendiente', 'slate', 'Aún sin dictamen o evaluación final', 1],
            ['En Evaluación', 'blue', 'En estudio técnico o legal', 2],
            ['Observado', 'amber', 'Requiere correcciones o recaudos adicionales', 3],
            ['Con Visto Bueno', 'cyan', 'Conformidad técnica intermedia otorgada', 4],
            ['Autorizado / Aprobado', 'emerald', 'Resolución favorable o visto bueno final', 5],
            ['Rechazado', 'rose', 'Denegado o no procedente', 6]
        ];
        $stmtER = $pdo->prepare("INSERT IGNORE INTO estados_resolucion (nombre, color, descripcion, orden) VALUES (?, ?, ?, ?)");
        foreach ($estadosResolucion as $er) {
            $stmtER->execute($er);
        }
        $logs[] = "Estados de resolución/dictamen inicializados (" . count($estadosResolucion) . " estados).";

        // 4. Tipos de Documento
        $tipos = [
            ['Oficio', 'Comunicaciones formales externas o interinstitucionales'],
            ['Solicitud / Carta', 'Peticiones presentadas por ciudadanos o usuarios externos'],
            ['Informe Técnico', 'Reportes de evaluación técnica o pericial'],
            ['Memorándum', 'Comunicaciones internas directas entre dependencias'],
            ['Expediente Administrativo', 'Conjunto foliado de documentos con trámite continuo']
        ];
        $stmtTipo = $pdo->prepare("INSERT IGNORE INTO tipos_documento (nombre, descripcion) VALUES (?, ?)");
        foreach ($tipos as $t) {
            $stmtTipo->execute($t);
        }
        $logs[] = "Tipos de documento base inicializados (" . count($tipos) . " tipos).";

        // 5. Acciones de Movimiento
        $acciones = [
            ['Registro Inicial', 'Creación e ingreso del documento al sistema', 0, 0],
            ['Recepción Física', 'Confirmación de entrega y tenencia física en mano', 0, 0],
            ['Derivación / Pase', 'Envío físico del documento hacia otra oficina', 0, 0],
            ['Verificación de Sello Físico', 'Comprobación de sello húmedo/estampilla física reglamentaria', 1, 0],
            ['Verificación de Firma Física', 'Cotejo y comprobación de firma autógrafa', 0, 1],
            ['Observación Emitida', 'Se detecta un defecto formal o material en el expediente', 0, 0],
            ['Aprobación / Dictamen', 'Firma y aprobación de la resolución', 1, 1],
            ['Devolución', 'Retorno formal del papel a su procedencia', 0, 0],
            ['Archivado Definitivo', 'Pase final al archivo central institucional', 1, 0]
        ];
        $stmtAcc = $pdo->prepare("INSERT IGNORE INTO acciones_movimiento (nombre, descripcion, comprueba_sello, comprueba_firma) VALUES (?, ?, ?, ?)");
        foreach ($acciones as $a) {
            $stmtAcc->execute($a);
        }
        $logs[] = "Acciones registrables inicializadas (" . count($acciones) . " acciones).";

        // 6. Usuario Administrador por defecto
        // Contraseña por defecto: admin123
        $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
        $stmtAdmin = $pdo->prepare("
            INSERT INTO usuarios (nombre_completo, usuario, email, password_hash, rol, departamento_id, activo)
            VALUES (?, ?, ?, ?, 'admin', 1, 1)
            ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo)
        ");
        $stmtAdmin->execute(['Administrador Principal', 'admin', 'admin@institucion.gob', $adminPass]);
        $logs[] = "Usuario Administrador creado: usuario 'admin' / contraseña 'admin123'.";

        // 7. Usuario Operador por defecto (Mesa de Partes)
        // Contraseña: usuario123
        $userPass = password_hash('usuario123', PASSWORD_BCRYPT);
        $stmtOperador = $pdo->prepare("
            INSERT INTO usuarios (nombre_completo, usuario, email, password_hash, rol, departamento_id, activo)
            VALUES (?, ?, ?, ?, 'usuario', 1, 1)
            ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo)
        ");
        $stmtOperador->execute(['Recepcionista Mesa de Partes', 'mesapartes', 'recepcion@institucion.gob', $userPass]);
        $logs[] = "Usuario Operador creado: usuario 'mesapartes' / contraseña 'usuario123'.";

        return $logs;
    }
}
