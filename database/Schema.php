<?php
/**
 * Schema.php - Definición Code-First de la Base de Datos
 * Motor InnoDB para soporte de transacciones ACID y Foreign Keys.
 */

class Schema {
    public static function getTables(): array {
        return [
            // 1. Departamentos / Oficinas
            'departamentos' => "
                CREATE TABLE IF NOT EXISTS departamentos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(120) NOT NULL,
                    codigo VARCHAR(20) NOT NULL UNIQUE,
                    descripcion VARCHAR(255) NULL,
                    activo TINYINT(1) DEFAULT 1,
                    es_sistema TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_depto_activo (activo),
                    INDEX idx_depto_codigo (codigo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            // 2. Estados de Trámite (Situación Operativa: En traslado, Recibido, En revisión, etc.)
            'estados_tramite' => "
                CREATE TABLE IF NOT EXISTS estados_tramite (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(80) NOT NULL UNIQUE,
                    color VARCHAR(30) DEFAULT 'gray',
                    descripcion VARCHAR(255) NULL,
                    activo TINYINT(1) DEFAULT 1,
                    orden INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            // 3. Estados de Resolución (Dictamen / Fondo: Pendiente, Observado, Aprobado, Rechazado)
            'estados_resolucion' => "
                CREATE TABLE IF NOT EXISTS estados_resolucion (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(80) NOT NULL UNIQUE,
                    color VARCHAR(30) DEFAULT 'gray',
                    descripcion VARCHAR(255) NULL,
                    activo TINYINT(1) DEFAULT 1,
                    orden INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            // 4. Tipos de Documento (Oficio, Solicitud, Informe, etc.)
            'tipos_documento' => "
                CREATE TABLE IF NOT EXISTS tipos_documento (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(100) NOT NULL UNIQUE,
                    descripcion VARCHAR(255) NULL,
                    activo TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            // 5. Acciones de Movimiento registrables (Recibir, Sellar, Firmar, Derivar, Archivar)
            'acciones_movimiento' => "
                CREATE TABLE IF NOT EXISTS acciones_movimiento (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(100) NOT NULL UNIQUE,
                    descripcion VARCHAR(255) NULL,
                    comprueba_sello TINYINT(1) DEFAULT 0,
                    comprueba_firma TINYINT(1) DEFAULT 0,
                    activo TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            // 6. Usuarios y Roles (RBAC)
            'usuarios' => "
                CREATE TABLE IF NOT EXISTS usuarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre_completo VARCHAR(150) NOT NULL,
                    usuario VARCHAR(50) NOT NULL UNIQUE,
                    email VARCHAR(120) NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    rol ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario',
                    departamento_id INT NULL,
                    activo TINYINT(1) DEFAULT 1,
                    ultimo_acceso TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    CONSTRAINT fk_usr_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL,
                    INDEX idx_usr_login (usuario, activo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            // 7. Documentos Físicos (Entidad Principal con OCC y Árbol de Documentos)
            'documentos' => "
                CREATE TABLE IF NOT EXISTS documentos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    folio_unico VARCHAR(60) NOT NULL UNIQUE,
                    asunto VARCHAR(255) NOT NULL,
                    remitente VARCHAR(150) NOT NULL,
                    entidad_origen VARCHAR(150) NULL,
                    tipo_id INT NOT NULL,
                    estado_tramite_id INT NOT NULL,
                    estado_resolucion_id INT NOT NULL,
                    departamento_actual_id INT NOT NULL,
                    custodio_id INT NULL,
                    documento_padre_id INT NULL,
                    version_occ INT NOT NULL DEFAULT 1,
                    tiene_sello_fisico TINYINT(1) DEFAULT 0,
                    tiene_firma_fisica TINYINT(1) DEFAULT 0,
                    total_revisiones INT DEFAULT 0,
                    observaciones_iniciales TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    CONSTRAINT fk_doc_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_documento(id) ON DELETE RESTRICT,
                    CONSTRAINT fk_doc_estado_tramite FOREIGN KEY (estado_tramite_id) REFERENCES estados_tramite(id) ON DELETE RESTRICT,
                    CONSTRAINT fk_doc_estado_resolucion FOREIGN KEY (estado_resolucion_id) REFERENCES estados_resolucion(id) ON DELETE RESTRICT,
                    CONSTRAINT fk_doc_departamento FOREIGN KEY (departamento_actual_id) REFERENCES departamentos(id) ON DELETE RESTRICT,
                    CONSTRAINT fk_doc_custodio FOREIGN KEY (custodio_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                    CONSTRAINT fk_doc_padre FOREIGN KEY (documento_padre_id) REFERENCES documentos(id) ON DELETE SET NULL,
                    INDEX idx_doc_folio (folio_unico),
                    INDEX idx_doc_asunto (asunto),
                    INDEX idx_doc_depto_tramite (departamento_actual_id, estado_tramite_id),
                    INDEX idx_doc_padre (documento_padre_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            // 8. Bitácora Histórica de Movimientos (Inmutable, Append-Only)
            'movimientos_bitacora' => "
                CREATE TABLE IF NOT EXISTS movimientos_bitacora (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    documento_id INT NOT NULL,
                    departamento_origen_id INT NOT NULL,
                    departamento_destino_id INT NOT NULL,
                    usuario_id INT NOT NULL,
                    accion_id INT NOT NULL,
                    estado_tramite_id INT NOT NULL,
                    estado_resolucion_id INT NOT NULL,
                    observacion TEXT NULL,
                    sello_verificado TINYINT(1) DEFAULT 0,
                    firma_verificada TINYINT(1) DEFAULT 0,
                    numero_revision INT DEFAULT 0,
                    version_en_movimiento INT NOT NULL,
                    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_mov_doc FOREIGN KEY (documento_id) REFERENCES documentos(id) ON DELETE CASCADE,
                    CONSTRAINT fk_mov_depto_orig FOREIGN KEY (departamento_origen_id) REFERENCES departamentos(id) ON DELETE RESTRICT,
                    CONSTRAINT fk_mov_depto_dest FOREIGN KEY (departamento_destino_id) REFERENCES departamentos(id) ON DELETE RESTRICT,
                    CONSTRAINT fk_mov_usr FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
                    CONSTRAINT fk_mov_accion FOREIGN KEY (accion_id) REFERENCES acciones_movimiento(id) ON DELETE RESTRICT,
                    CONSTRAINT fk_mov_est_tramite FOREIGN KEY (estado_tramite_id) REFERENCES estados_tramite(id) ON DELETE RESTRICT,
                    CONSTRAINT fk_mov_est_resolucion FOREIGN KEY (estado_resolucion_id) REFERENCES estados_resolucion(id) ON DELETE RESTRICT,
                    INDEX idx_mov_doc_fecha (documento_id, fecha_registro),
                    INDEX idx_mov_destino (departamento_destino_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            "
        ];
    }
}
