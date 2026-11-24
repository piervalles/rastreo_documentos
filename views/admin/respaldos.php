<?php
/**
 * views/admin/respaldos.php - Respaldo de Base de Datos
 */
$titulo = "Copias de Seguridad";
?>

<div style="max-width: 580px; margin: 40px auto;">
    <div class="card" style="border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); border-radius: 12px;">
        <div class="card-body" style="padding: 36px 32px; text-align: center;">
            <div style="width: 52px; height: 52px; background: #f1f5f9; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; color: #475569;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
            </div>

            <h3 style="font-size: 17px; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                Copia de Seguridad
            </h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
                Base de datos institucional completa en formato SQL.
            </p>

            <div style="display: inline-flex; align-items: center; justify-content: center; gap: 24px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 28px; margin-bottom: 28px;">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; letter-spacing: 0.5px;">Peso estimado</span>
                    <strong style="font-size: 15px; color: var(--text-main); font-family: monospace;"><?= e($info['tamano_formateado']) ?></strong>
                </div>
                <div style="width: 1px; height: 28px; background: var(--border-color);"></div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; letter-spacing: 0.5px;">Tablas</span>
                    <strong style="font-size: 15px; color: var(--text-main);"><?= e($info['tablas']) ?></strong>
                </div>
                <div style="width: 1px; height: 28px; background: var(--border-color);"></div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; letter-spacing: 0.5px;">Formato</span>
                    <strong style="font-size: 15px; color: var(--text-main);">.sql</strong>
                </div>
            </div>

            <div>
                <a href="index.php?c=backup&a=descargar" class="btn btn-primary" style="padding: 10px 24px; font-size: 13.5px; font-weight: 600;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Descargar Respaldo
                </a>
            </div>
        </div>
    </div>
</div>
