<?php
/**
 * helpers/View.php - Motor Limpio de Renderizado de Vistas y Layouts
 */

class View {
    /**
     * Renderiza una vista dentro del layout principal con sidebar fijo
     *
     * @param string $vista Ruta relativa en views/ sin extensión .php (ej. 'documentos/index')
     * @param array $datos Variables disponibles para la vista
     * @param string|null $titulo Título de la página mostrado en la barra superior
     */
    public static function render(string $vista, array $datos = [], ?string $titulo = null): void {
        extract($datos);
        $titulo = $titulo ?? ($datos['titulo'] ?? 'DocuTrace');

        // 1. Capturar el contenido dinámico de la vista hija
        $archivoVista = __DIR__ . "/../views/{$vista}.php";
        if (!file_exists($archivoVista)) {
            http_response_code(500);
            die("Error: La vista '{$vista}' no fue encontrada en {$archivoVista}");
        }

        ob_start();
        require $archivoVista;
        $contenido = ob_get_clean();

        // 2. Cargar el layout maestro con el sidebar fijo inyectando $contenido
        require __DIR__ . "/../views/layouts/main.php";
        exit;
    }
}
