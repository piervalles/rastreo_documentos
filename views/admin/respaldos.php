<?php
/**
 * views/admin/respaldos.php - Módulo de Respaldos de 1 Clic
 */
$titulo = "Copias de Seguridad (Respaldos)";
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card" style="border-top: 4px solid #10b981;">
        <div class="card-header">
            <h3>Copia de Seguridad de la Base de Datos (1 Clic)</h3>
        </div>
        <div class="card-body">
            <p style="font-size: 14px; color: #334155; line-height: 1.6; margin-bottom: 20px;">
                La copia de seguridad genera un archivo <code>.sql</code> completo que contiene todas las tablas, la bitácora histórica inmutable de trazabilidad, las cuentas de usuario y los catálogos configurados.
            </p>

            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 24px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="margin-bottom: 12px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <h4 style="font-size: 16px; font-weight: 700; color: #15803d; margin-bottom: 8px;">
                    Descarga Inmediata al Navegador
                </h4>
                <p style="font-size: 13px; color: #166534; margin-bottom: 16px; max-width: 480px; margin-left: auto; margin-right: auto;">
                    Presiona el botón para descargar de inmediato el archivo de respaldo a tu computadora o memoria USB.
                </p>
                <a href="index.php?c=backup&a=descargar" class="btn btn-success" style="padding: 12px 28px; font-size: 15px;">
                    ⬇️ Descargar Copia de Seguridad Actual (.sql)
                </a>
            </div>

            <div class="card" style="background: #f8fafc; border: 1px solid #e2e8f0; margin-bottom: 0;">
                <div class="card-body">
                    <strong style="color: #1e293b; display: block; margin-bottom: 8px;">
                        📌 Recomendaciones para el Personal Administrativo:
                    </strong>
                    <ul style="margin-left: 20px; font-size: 13px; color: #475569; line-height: 1.7;">
                        <li><strong>Frecuencia:</strong> Se recomienda descargar este respaldo al menos una vez por semana o al finalizar la jornada laboral los viernes.</li>
                        <li><strong>Almacenamiento:</strong> Guarde el archivo descargado en una memoria USB externa o disco duro independiente de la computadora servidora.</li>
                        <li><strong>Restauración:</strong> En caso de que la computadora sufra algún daño físico, este archivo <code>.sql</code> puede importarse directamente en cualquier instalación nueva de XAMPP mediante phpMyAdmin o el instalador.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$vistaHija = __DIR__ . '/respaldos.php';
require_once __DIR__ . '/../layouts/main.php';
