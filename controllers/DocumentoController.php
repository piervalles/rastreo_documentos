<?php
/**
 * controllers/DocumentoController.php - Gestión de Documentos y Ficha
 */

require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../models/Movimiento.php';
require_once __DIR__ . '/../models/Catalogo.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/Seguridad.php';

class DocumentoController {

    public function __construct() {
        Seguridad::exigirAutenticado();
    }

    /**
     * Bandeja Principal de Documentos
     */
    public function index(): void {
        $deptoId = null;
        $esAdmin = Seguridad::esAdmin();

        // Si no es admin y tiene departamento asignado, por defecto ve los de su departamento
        if (!$esAdmin && !empty($_SESSION['departamento_id'])) {
            $deptoId = (int)$_SESSION['departamento_id'];
        } elseif (isset($_GET['depto_id']) && $_GET['depto_id'] !== '') {
            $deptoId = (int)$_GET['depto_id'];
        }

        $filtros = [
            'buscar'               => $_GET['buscar'] ?? null,
            'estado_tramite_id'    => $_GET['estado_tramite_id'] ?? null,
            'estado_resolucion_id' => $_GET['estado_resolucion_id'] ?? null,
            'fecha_desde'          => $_GET['fecha_desde'] ?? null,
            'fecha_hasta'          => $_GET['fecha_hasta'] ?? null,
        ];

        $documentos = Documento::listarBandeja($deptoId, $filtros);
        $departamentos = Catalogo::listarDepartamentos(true);
        $estadosTramite = Catalogo::listarEstadosTramite(true);
        $estadosResolucion = Catalogo::listarEstadosResolucion(true);

        require __DIR__ . '/../views/documentos/index.php';
    }

    /**
     * Formulario de Nuevo Registro de Documento Físico
     */
    public function crear(): void {
        $tipos = Catalogo::listarTiposDocumento(true);
        $estadosTramite = Catalogo::listarEstadosTramite(true);
        $estadosResolucion = Catalogo::listarEstadosResolucion(true);
        $departamentos = Catalogo::listarDepartamentos(true);
        $folioSugerido = Documento::generarFolio();
        
        // Si viene derivado de otro documento (subsanación, respuesta)
        $docPadreId = $_GET['padre_id'] ?? null;
        $docPadre = null;
        if ($docPadreId) {
            $docPadre = Documento::obtenerPorId((int)$docPadreId);
        }

        require __DIR__ . '/../views/documentos/crear.php';
    }

    /**
     * Guardar Nuevo Documento en Base de Datos
     */
    public function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?c=documentos&a=index');
            exit;
        }

        if (!Seguridad::validarCsrf($_POST['csrf_token'] ?? '')) {
            Seguridad::setFlash('error', 'Token de seguridad inválido. Operación cancelada.');
            header('Location: index.php?c=documentos&a=crear');
            exit;
        }

        try {
            $nuevoId = Documento::crear($_POST, (int)$_SESSION['usuario_id']);
            Seguridad::setFlash('success', "Documento físico registrado exitosamente con folio único.");
            header("Location: index.php?c=documentos&a=ver&id={$nuevoId}");
            exit;
        } catch (Exception $e) {
            Seguridad::setFlash('error', "Error al registrar el documento: " . $e->getMessage());
            header('Location: index.php?c=documentos&a=crear');
            exit;
        }
    }

    /**
     * Ficha Técnica y Línea de Tiempo de Trazabilidad
     */
    public function ver(): void {
        $id = (int)($_GET['id'] ?? 0);
        $documento = Documento::obtenerPorId($id);

        if (!$documento) {
            Seguridad::setFlash('error', 'El documento solicitado no existe.');
            header('Location: index.php?c=documentos&a=index');
            exit;
        }

        $historial = Movimiento::obtenerHistorial($id);
        $documentosHijos = Documento::listarHijos($id);
        
        // Para el modal de nuevo movimiento
        $departamentos = Catalogo::listarDepartamentos(true);
        $estadosTramite = Catalogo::listarEstadosTramite(true);
        $estadosResolucion = Catalogo::listarEstadosResolucion(true);
        $acciones = Catalogo::listarAcciones(true);
        $usuarios = Usuario::listar();

        require __DIR__ . '/../views/documentos/ver.php';
    }
}
