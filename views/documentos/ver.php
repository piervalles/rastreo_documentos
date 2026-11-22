<?php
/**
 * views/documentos/ver.php - Ficha Técnica y Línea de Tiempo de Trazabilidad
 */
$titulo = "Expediente: " . $documento['folio_unico'];
?>

<!-- BANNER DE IDENTIFICACIÓN Y CÓDIGO DE BARRAS -->
<div class="card">
    <div class="card-body" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-blue"><?= e($documento['tipo_nombre']) ?></span>
                <span style="font-size: 12px; color: var(--text-muted);">Registrado el <?= date('d/m/Y H:i', strtotime($documento['created_at'])) ?></span>
                <?php if ($documento['documento_padre_id']): ?>
                    <a href="index.php?c=documentos&a=ver&id=<?= $documento['documento_padre_id'] ?>" class="badge badge-purple">
                        ← Vinculado al Original: <?= e($documento['documento_padre_folio']) ?>
                    </a>
                <?php endif; ?>
            </div>
            <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                <?= e($documento['asunto']) ?>
            </h2>
            <p style="font-size: 13px; color: var(--text-muted);">
                Remitente: <strong><?= e($documento['remitente']) ?></strong> 
                <?= $documento['entidad_origen'] ? ' (' . e($documento['entidad_origen']) . ')' : '' ?>
            </p>
        </div>

        <!-- CÓDIGO DE BARRAS VISUAL PARA APOYO FÍSICO -->
        <div class="barcode-box">
            <div class="barcode-lines">||| | |||| | || | |||</div>
            <div class="barcode-text"><?= e($documento['folio_unico']) ?></div>
            <button onclick="window.print()" class="btn btn-secondary btn-sm" style="margin-top: 6px; font-size: 11px; padding: 3px 8px;">
                🖨️ Imprimir Ficha / Rótulo
            </button>
        </div>
    </div>
</div>

<!-- ESTADO ACTUAL Y UBICACIÓN FÍSICA -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0;">
        <div class="card-body" style="padding: 16px;">
            <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Ubicación Física Actual</span>
            <div style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-top: 4px;">
                🏢 <?= e($documento['departamento_nombre']) ?> (<?= e($documento['departamento_codigo']) ?>)
            </div>
            <span style="font-size: 12px; color: var(--text-muted);">Custodio: <?= e($documento['custodio_nombre'] ?? 'Sin asignar') ?></span>
        </div>
    </div>

    <div class="card" style="margin-bottom: 0;">
        <div class="card-body" style="padding: 16px;">
            <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Estado de Trámite (Operativo)</span>
            <div style="margin-top: 6px;">
                <span class="badge badge-<?= e($documento['estado_tramite_color']) ?>" style="font-size: 13px; padding: 6px 12px;">
                    <?= e($documento['estado_tramite_nombre']) ?>
                </span>
            </div>
            <span style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 4px;">Situación física en oficina</span>
        </div>
    </div>

    <div class="card" style="margin-bottom: 0;">
        <div class="card-body" style="padding: 16px;">
            <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Estado de Resolución (Dictamen)</span>
            <div style="margin-top: 6px;">
                <span class="badge badge-<?= e($documento['estado_resolucion_color']) ?>" style="font-size: 13px; padding: 6px 12px;">
                    <?= e($documento['estado_resolucion_nombre']) ?>
                </span>
            </div>
            <span style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 4px;">Evaluación de fondo</span>
        </div>
    </div>

    <div class="card" style="margin-bottom: 0;">
        <div class="card-body" style="padding: 16px;">
            <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Comprobaciones Físicas</span>
            <div style="font-size: 13px; font-weight: 600; margin-top: 6px;">
                <?= $documento['tiene_sello_fisico'] ? '🏷️ Sello Físico Verificado' : '— Sin Sello' ?><br>
                <?= $documento['tiene_firma_fisica'] ? '✍️ Firma Física Verificada' : '— Sin Firma' ?>
            </div>
            <span style="font-size: 11px; color: var(--text-muted);">Total Revisiones: #<?= $documento['total_revisiones'] ?></span>
        </div>
    </div>
</div>

<!-- DOCUMENTOS HIJOS / DERIVADOS (SI EXISTEN) -->
<?php if (!empty($documentosHijos)): ?>
    <div class="card" style="border-left: 4px solid #8b5cf6;">
        <div class="card-header">
            <h3>Documentos Vinculados (Respuestas / Subsanaciones Derivadas)</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Asunto</th>
                        <th>Remitente</th>
                        <th>Ubicación Actual</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentosHijos as $hijo): ?>
                        <tr>
                            <td><strong><?= e($hijo['folio_unico']) ?></strong></td>
                            <td><?= e($hijo['asunto']) ?></td>
                            <td><?= e($hijo['remitente']) ?></td>
                            <td><?= e($hijo['departamento_nombre']) ?></td>
                            <td><span class="badge badge-<?= e($hijo['estado_tramite_color']) ?>"><?= e($hijo['estado_tramite_nombre']) ?></span></td>
                            <td><a href="index.php?c=documentos&a=ver&id=<?= $hijo['id'] ?>" class="btn btn-secondary btn-sm">Ver Documento</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- ACCIONES OPERATIVAS Y REGISTRO DE MOVIMIENTO -->
<div class="card" style="border: 1px solid #3b82f6;">
    <div class="card-header" style="background: #eff6ff;">
        <h3 style="color: #1e40af;">📝 Registrar Nuevo Movimiento / Pase Físico</h3>
        <a href="index.php?c=documentos&a=crear&padre_id=<?= $documento['id'] ?>" class="btn btn-secondary btn-sm">
            + Vincular Respuesta o Derivado
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?c=movimientos&a=registrar">
            <?= Seguridad::inputCsrf() ?>
            <input type="hidden" name="documento_id" value="<?= $documento['id'] ?>">
            <!-- CONTROL DE CONCURRENCIA OPTIMISTA: VERSIÓN ESPERADA -->
            <input type="hidden" name="version_occ" value="<?= $documento['version_occ'] ?>">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Acción a Realizar: *</label>
                    <select name="accion_id" class="form-control" required>
                        <?php foreach ($acciones as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= e($acc['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Departamento Destino (o mantener ubicación): *</label>
                    <select name="departamento_destino_id" class="form-control" required>
                        <?php foreach ($departamentos as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($documento['departamento_actual_id'] == $d['id']) ? 'selected' : '' ?>>
                                <?= e($d['nombre']) ?> (<?= e($d['codigo']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nuevo Estado de Trámite (Operativo): *</label>
                    <select name="estado_tramite_id" class="form-control" required>
                        <?php foreach ($estadosTramite as $et): ?>
                            <option value="<?= $et['id'] ?>" <?= ($documento['estado_tramite_id'] == $et['id']) ? 'selected' : '' ?>>
                                <?= e($et['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nuevo Estado de Resolución (Dictamen): *</label>
                    <select name="estado_resolucion_id" class="form-control" required>
                        <?php foreach ($estadosResolucion as $er): ?>
                            <option value="<?= $er['id'] ?>" <?= ($documento['estado_resolucion_id'] == $er['id']) ? 'selected' : '' ?>>
                                <?= e($er['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                <label class="form-label">Comprobaciones Físicas al momento de este movimiento:</label>
                <div style="display: flex; gap: 24px; margin-top: 6px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="sello_verificado" value="1" <?= $documento['tiene_sello_fisico'] ? 'checked' : '' ?>>
                        <span>🏷️ Sello Físico Verificado y Conforme</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="firma_verificada" value="1" <?= $documento['tiene_firma_fisica'] ? 'checked' : '' ?>>
                        <span>✍️ Firma Física Verificada y Conforme</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Observaciones del Movimiento (opcional):</label>
                <textarea name="observacion" class="form-control" rows="2" placeholder="Ej. Se remite para opinión legal con 15 folios útiles adjuntos..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">
                    ✓ Registrar Movimiento en Bitácora
                </button>
            </div>
        </form>
    </div>
</div>

<!-- LÍNEA DE TIEMPO HISTÓRICA INMUTABLE (TIMELINE) -->
<div class="card">
    <div class="card-header">
        <h3>Historial Completo e Inmutable de Trazabilidad (<?= count($historial) ?> eventos registrados)</h3>
    </div>
    <div class="card-body">
        <div class="timeline">
            <?php foreach ($historial as $mov): ?>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-card">
                        <div class="timeline-header">
                            <div>
                                <strong><?= date('d/m/Y H:i:s', strtotime($mov['fecha_registro'])) ?></strong>
                                <span> • Registrado por: <?= e($mov['usuario_nombre']) ?> (<?= e($mov['usuario_login']) ?>)</span>
                            </div>
                            <span class="badge badge-<?= e($mov['estado_tramite_color']) ?>">
                                <?= e($mov['estado_tramite_nombre']) ?>
                            </span>
                        </div>

                        <div class="timeline-title">
                            <?= e($mov['accion_nombre']) ?>: 
                            <span style="font-weight: normal; color: var(--text-muted);">
                                <?= e($mov['depto_origen_nombre']) ?> → <strong><?= e($mov['depto_destino_nombre']) ?></strong>
                            </span>
                        </div>

                        <?php if (!empty($mov['observacion'])): ?>
                            <p style="font-size: 13px; color: #334155; margin-bottom: 8px; background: #f8fafc; padding: 8px 12px; border-radius: 6px; border-left: 3px solid #cbd5e1;">
                                "<?= e($mov['observacion']) ?>"
                            </p>
                        <?php endif; ?>

                        <div style="display: flex; gap: 14px; font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                            <span>Dictamen: <strong><?= e($mov['estado_resolucion_nombre']) ?></strong></span>
                            <span>Sello: <strong><?= $mov['sello_verificado'] ? '✓ Verificado' : '—' ?></strong></span>
                            <span>Firma: <strong><?= $mov['firma_verificada'] ? '✓ Verificada' : '—' ?></strong></span>
                            <span>Revisión #<?= $mov['numero_revision'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
$vistaHija = __DIR__ . '/ver.php';
require_once __DIR__ . '/../layouts/main.php';
