<?php
/**
 * controllers/BackupController.php - Respaldo y Descarga de Base de Datos en 1 Clic
 * Implementación en PHP puro (no depende de mysqldump ni permisos de consola).
 */

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../helpers/Seguridad.php';
require_once __DIR__ . '/../helpers/View.php';

class BackupController {

    public function __construct() {
        Seguridad::exigirAdmin();
    }

    public function index(): void {
        View::render('admin/respaldos', [], 'Copias de Seguridad');
    }

    /**
     * Genera y descarga el archivo .sql directamente en el navegador
     */
    public function descargar(): void {
        try {
            $pdo = Database::getConnection();
            $config = require __DIR__ . '/../config/database.php';
            $dbName = $config['database'];

            $fecha = date('Y-m-d_H-i-s');
            $nombreArchivo = "respaldo_{$dbName}_{$fecha}.sql";

            // Headers para forzar descarga directa en el navegador
            header('Content-Type: application/sql; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"{$nombreArchivo}\"");
            header('Pragma: no-cache');
            header('Expires: 0');

            // Encabezado del volcado SQL
            echo "-- ======================================================\n";
            echo "-- SISTEMA DE RASTREO Y TRAZABILIDAD DOCUMENTAL\n";
            echo "-- Respaldo Automático de Base de Datos\n";
            echo "-- Fecha de Generación: " . date('d/m/Y H:i:s') . "\n";
            echo "-- Base de Datos: {$dbName}\n";
            echo "-- ======================================================\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n";
            echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            echo "START TRANSACTION;\n";
            echo "SET time_zone = \"+00:00\";\n\n";

            // Obtener todas las tablas
            $tables = [];
            $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            foreach ($tables as $table) {
                echo "-- --------------------------------------------------------\n";
                echo "-- Estructura de tabla para `{$table}`\n";
                echo "-- --------------------------------------------------------\n";
                echo "DROP TABLE IF EXISTS `{$table}`;\n";

                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $createRow = $createStmt->fetch(PDO::FETCH_NUM);
                echo $createRow[1] . ";\n\n";

                echo "-- Volcado de datos para la tabla `{$table}`\n";
                $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
                $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    $columnas = array_map(function($col) { return "`{$col}`"; }, array_keys($rows[0]));
                    $colsSql = implode(', ', $columnas);

                    echo "INSERT INTO `{$table}` ({$colsSql}) VALUES\n";
                    $totalRows = count($rows);
                    $i = 0;

                    foreach ($rows as $r) {
                        $i++;
                        $valores = array_map(function($val) use ($pdo) {
                            if ($val === null) return "NULL";
                            return $pdo->quote($val);
                        }, array_values($r));

                        $valSql = implode(', ', $valores);
                        echo "(" . $valSql . ")" . ($i === $totalRows ? ";\n\n" : ",\n");
                    }
                }
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
            echo "COMMIT;\n";
            exit;

        } catch (Exception $e) {
            Seguridad::setFlash('error', 'Error al generar el respaldo: ' . $e->getMessage());
            header('Location: index.php?c=backup&a=index');
            exit;
        }
    }
}
