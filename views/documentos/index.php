<?php
/**
 * views/documentos/index.php - Bandeja de Documentos Físicos
 */
$titulo = "Bandeja de Documentos";
?>

<div class="card">
    <div class="card-header">
        <h3>Filtrar y Buscar Documentos</h3>
        <a href="index.php?c=documentos&a=crear" class="btn btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Nuevo Documento Físico
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php">
            <input type="hidden" name="c" value="documentos">
            <input type="hidden" name="a" value="index">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Buscar por Folio, Asunto o Remitente:</label>
                    <input type="text" name="buscar" class="form-control" placeholder="Ej. EXP-2026-0001, Oficio..." value="<?= e($_GET['buscar'] ?? '') ?>">
                </div>

                <?php if (Seguridad::esAdmin()): ?>
                    <div class="form-group">
                        <label class="form-label">Filtrar por Departamento / Área:</label>
                        <select name="depto_id" class="form-control">
                            <option value="">-- Todos los Departamentos --</option>
                            <?php foreach ($departamentos as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= (($_GET['depto_id'] ?? '') == $d['id']) ? 'selected' : '' ?>>
                                    <?= e($d['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Estado de Trámite (Operativo):</label>
                    <select name="estado_tramite_id" class="form-control">
                        <option value="">-- Todos los Estados --</option>
                        <?php foreach ($estadosTramite as $et): ?>
                            <option value="<?= $et['id'] ?>" <?= (($_GET['estado_tramite_id'] ?? '') == $et['id']) ? 'selected' : '' ?>>
                                <?= e($et['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Estado de Resolución (Dictamen):</label>
                    <select name="estado_resolucion_id" class="form-control">
                        <option value="">-- Todas las Resoluciones --</option>
                        <?php foreach ($estadosResolucion as $er): ?>
                            <option value="<?= $er['id'] ?>" <?= (($_GET['estado_resolucion_id'] ?? '') == $er['id']) ? 'selected' : '' ?>>
                                <?= e($er['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 8px;">
                <a href="index.php?c=documentos&a=index" class="btn btn-secondary btn-sm">Limpiar Filtros</a>
                <button type="submit" class="btn btn-primary btn-sm">Aplicar Filtros</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Listado de Expedientes Físicos (<?= count($documentos) ?> registros)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Código / Folio</th>
                    <th>Asunto & Remitente</th>
                    <th>Ubicación Actual</th>
                    <th>Estado de Trámite</th>
                    <th>Resolución / Dictamen</th>
                    <th>Comprobaciones</th>
                    <th>Última Actualización</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($documentos)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No se encontraron documentos físicos registrados con los criterios seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($documentos as $doc): ?>
                        <tr>
                            <td>
                                <a href="index.php?c=documentos&a=ver&id=<?= $doc['id'] ?>" style="font-weight: 700; font-family: monospace; font-size: 13px;">
                                    <?= e($doc['folio_unico']) ?>
                                </a>
                                <?php if ($doc['documento_padre_id']): ?>
                                    <span style="display: block; font-size: 10px; color: #64748b;">(Vinculado)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="display: block; color: var(--text-main);"><?= e($doc['asunto']) ?></strong>
                                <span style="font-size: 12px; color: var(--text-muted);">De: <?= e($doc['remitente']) ?> (<?= e($doc['tipo_nombre']) ?>)</span>
                            </td>
                            <td>
                                <strong><?= e($doc['departamento_nombre']) ?></strong>
                                <span style="display: block; font-size: 11px; color: var(--text-muted);">
                                    Custodio: <?= e($doc['custodio_nombre'] ?? 'Sin asignar') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= e($doc['estado_tramite_color']) ?>">
                                    <?= e($doc['estado_tramite_nombre']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= e($doc['estado_resolucion_color']) ?>">
                                    <?= e($doc['estado_resolucion_nombre']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px; font-size: 11px;">
                                    <span title="Sello Físico"><?= $doc['tiene_sello_fisico'] ? '🏷️ Sello' : '—' ?></span>
                                    <span title="Firma Física"><?= $doc['tiene_firma_fisica'] ? '✍️ Firma' : '—' ?></span>
                                </div>
                                <span style="font-size: 10px; color: var(--text-muted); display: block;">Rev. #<?= $doc['total_revisiones'] ?></span>
                            </td>
                            <td style="white-space: nowrap; font-size: 12px; color: var(--text-muted);">
                                <?= date('d/m/Y H:i', strtotime($doc['updated_at'])) ?>
                            </td>
                            <td>
                                <a href="index.php?c=documentos&a=ver&id=<?= $doc['id'] ?>" class="btn btn-secondary btn-sm">
                                    Ver Trazabilidad →
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$vistaHija = __DIR__ . '/index.php';
require_once __DIR__ . '/../layouts/main.php';
