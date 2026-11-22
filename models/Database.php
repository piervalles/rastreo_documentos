<?php
/**
 * Clase Singleton para Conexión PDO a MySQL
 */

class Database {
    private static ?PDO $instance = null;

    /**
     * Obtiene la instancia activa de PDO
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';
            
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                // Si la BD no existe, intentamos conectar al servidor sin seleccionar base de datos
                if ($e->getCode() == 1049) {
                    throw new Exception("La base de datos '{$config['database']}' aún no existe. Por favor ejecuta el instalador.");
                }
                throw new Exception("Error al conectar a la base de datos: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Conexión directa al servidor sin seleccionar base de datos (usado por el instalador)
     */
    public static function getServerConnection(): PDO {
        $config = require __DIR__ . '/../config/database.php';
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
        return new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    /**
     * Helpers para transacciones ACID
     */
    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool {
        return self::getConnection()->rollBack();
    }
}
