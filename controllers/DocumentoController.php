<?php
/**
 * controllers/DocumentoController.php - Gestión de Documentos y Ficha
 */

require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../models/Movimiento.php';
require_once __DIR__ . '/../models/Catalogo.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/Seguridad.php';
require_once __DIR__ . '/../helpers/View.php';

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
            'asunto'               => $_GET['asunto'] ?? null,
            'estado_tramite_id'    => $_GET['estado_tramite_id'] ?? null,
            'estado_resolucion_id' => $_GET['estado_resolucion_id'] ?? null,
            'fecha_desde'          => $_GET['fecha_desde'] ?? null,
            'fecha_hasta'          => $_GET['fecha_hasta'] ?? null,
        ];

        $documentos = Documento::listarBandeja($deptoId, $filtros);
        $asuntos = Documento::listarAsuntosUnicos();
        $departamentos = Catalogo::listarDepartamentos(true);
        $estadosTramite = Catalogo::listarEstadosTramite(true);
        $estadosResolucion = Catalogo::listarEstadosResolucion(true);

        View::render('documentos/index', compact('documentos', 'asuntos', 'departamentos', 'estadosTramite', 'estadosResolucion'), 'Bandeja de Documentos');
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

        View::render('documentos/crear', compact('tipos', 'estadosTramite', 'estadosResolucion', 'departamentos', 'folioSugerido', 'docPadre'), 'Registrar Nuevo Documento Físico');
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

        View::render('documentos/ver', compact('documento', 'historial', 'documentosHijos', 'departamentos', 'estadosTramite', 'estadosResolucion', 'acciones', 'usuarios'), 'Expediente: ' . $documento['folio_unico']);
    }

    /**
     * Endpoint API JSON para sincronización reactiva en segundo plano (Fase 6)
     */
    public function comprobarNovedades(): void {
        header('Content-Type: application/json');

        $deptoId = $_SESSION['departamento_id'] ?? null;
        $desde = $_GET['desde'] ?? date('Y-m-d H:i:s', time() - 30);

        if (!$deptoId) {
            echo json_encode(['nuevos' => 0]);
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT m.id, m.fecha_registro, d.folio_unico, d.asunto, orig.nombre AS depto_origen
            FROM movimientos_bitacora m
            INNER JOIN documentos d ON m.documento_id = d.id
            INNER JOIN departamentos orig ON m.departamento_origen_id = orig.id
            WHERE m.departamento_destino_id = :depto_id
              AND m.departamento_origen_id != :depto_id
              AND m.fecha_registro > :desde
            ORDER BY m.id DESC
            LIMIT 1
        ");
        $stmt->execute([
            'depto_id' => $deptoId,
            'desde'    => $desde
        ]);
        $novedad = $stmt->fetch();

        if ($novedad) {
            echo json_encode([
                'nuevos'        => 1,
                'folio'         => $novedad['folio_unico'],
                'asunto'        => $novedad['asunto'],
                'origen'        => $novedad['depto_origen'],
                'timestamp'     => $novedad['fecha_registro']
            ]);
        } else {
            echo json_encode(['nuevos' => 0, 'timestamp' => date('Y-m-d H:i:s')]);
        }
        exit;
    }
}
