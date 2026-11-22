<?php
/**
 * controllers/MovimientoController.php - Registro de Movimientos y Pases
 */

require_once __DIR__ . '/../models/Movimiento.php';
require_once __DIR__ . '/../helpers/Seguridad.php';

class MovimientoController {

    public function __construct() {
        Seguridad::exigirAutenticado();
    }

    /**
     * Procesa el registro de un movimiento físico (pase, sello, firma, dictamen, archivo)
     */
    public function registrar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?c=documentos&a=index');
            exit;
        }

        $docId = (int)($_POST['documento_id'] ?? 0);

        if (!Seguridad::validarCsrf($_POST['csrf_token'] ?? '')) {
            Seguridad::setFlash('error', 'Token de seguridad inválido. Operación cancelada.');
            header("Location: index.php?c=documentos&a=ver&id={$docId}");
            exit;
        }

        try {
            Movimiento::registrar($_POST, (int)$_SESSION['usuario_id']);
            Seguridad::setFlash('success', 'Movimiento registrado correctamente en la bitácora inmutable.');
            header("Location: index.php?c=documentos&a=ver&id={$docId}");
            exit;

        } catch (ConcurrenciaException $e) {
            // Manejo de conflicto optimista de concurrencia
            Seguridad::setFlash('warning', '⚠️ ' . $e->getMessage());
            header("Location: index.php?c=documentos&a=ver&id={$docId}&conflicto=1");
            exit;

        } catch (Exception $e) {
            Seguridad::setFlash('error', 'Error al registrar el movimiento: ' . $e->getMessage());
            header("Location: index.php?c=documentos&a=ver&id={$docId}");
            exit;
        }
    }
}
