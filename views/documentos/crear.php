<?php
/**
 * views/documentos/crear.php - Registro de Nuevo Documento Físico
 */
$titulo = "Registrar Nuevo Documento Físico";
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <h3>Formulario de Ingreso de Documento Físico</h3>
        <a href="index.php?c=documentos&a=index" class="btn btn-secondary btn-sm">← Volver a la Bandeja</a>
    </div>

    <div class="card-body">
        <?php if ($docPadre): ?>
            <div class="alert alert-info" style="margin-bottom: 20px;">
                <span>ℹ️ Este documento se registrará como <strong>respuesta o derivado</strong> del expediente original: 
                    <strong><?= e($docPadre['folio_unico']) ?> - <?= e($docPadre['asunto']) ?></strong>.
                </span>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?c=documentos&a=guardar">
            <?= Seguridad::inputCsrf() ?>
            <?php if ($docPadre): ?>
                <input type="hidden" name="documento_padre_id" value="<?= $docPadre['id'] ?>">
            <?php endif; ?>

            <div class="card" style="background: #f8fafc; border: 1px dashed #cbd5e1; margin-bottom: 20px;">
                <div class="card-body">
                    <label class="form-label" style="font-size: 14px;">Identificación del Documento Físico (Código o Folio):</label>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: normal;">Folio sugerido automáticamente:</label>
                            <input type="text" class="form-control" value="<?= e($folioSugerido) ?>" readonly style="background: #e2e8f0; font-family: monospace; font-weight: 700;">
                            <span style="font-size: 11px; color: var(--text-muted);">Se asignará automáticamente si dejas el campo de abajo en blanco.</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: normal;">O ingresar folio manual si el papel ya trae código previo:</label>
                            <input type="text" name="folio_manual" class="form-control" placeholder="Ej. OFICIO-045-2026 / REG-998">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Asunto o Referencia del Documento: *</label>
                    <input type="text" name="asunto" class="form-control" required placeholder="Ej. Solicitud de adquisición de equipos informáticos">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Persona Remitente: *</label>
                    <input type="text" name="remitente" class="form-control" required placeholder="Ej. Juan Pérez Gómez / Lic. Ana Morales">
                </div>
                <div class="form-group">
                    <label class="form-label">Entidad u Organización de Origen (opcional):</label>
                    <input type="text" name="entidad_origen" class="form-control" placeholder="Ej. Municipalidad Provincial / Particular">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tipo de Documento: *</label>
                    <select name="tipo_id" class="form-control" required>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Departamento de Recepción / Ubicación Inicial: *</label>
                    <select name="departamento_actual_id" class="form-control" required>
                        <?php foreach ($departamentos as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= (!empty($_SESSION['departamento_id']) && $_SESSION['departamento_id'] == $d['id']) ? 'selected' : '' ?>>
                                <?= e($d['nombre']) ?> (<?= e($d['codigo']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Estado de Trámite Inicial (Operativo): *</label>
                    <select name="estado_tramite_id" class="form-control" required>
                        <?php foreach ($estadosTramite as $et): ?>
                            <option value="<?= $et['id'] ?>"><?= e($et['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Estado de Resolución Inicial (Dictamen): *</label>
                    <select name="estado_resolucion_id" class="form-control" required>
                        <?php foreach ($estadosResolucion as $er): ?>
                            <option value="<?= $er['id'] ?>"><?= e($er['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="card" style="background: #fafafa; border: 1px solid var(--border-color); margin-top: 10px;">
                <div class="card-body">
                    <label class="form-label">Comprobaciones Físicas del Papel:</label>
                    <div style="display: flex; gap: 24px; margin-top: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="tiene_sello_fisico" value="1" checked>
                            <span>🏷️ El documento cuenta con <strong>Sello Físico</strong> verificado</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="tiene_firma_fisica" value="1" checked>
                            <span>✍️ El documento cuenta con <strong>Firma Física</strong> autógrafa cotejada</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label">Observaciones o Notas de Ingreso (opcional):</label>
                <textarea name="observaciones" class="form-control" rows="3" placeholder="Ej. Documento ingresado con 12 folios útiles y 1 CD adjunto en sobre cerrado."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <a href="index.php?c=documentos&a=index" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                    ✓ Guardar y Registrar Expediente
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$vistaHija = __DIR__ . '/crear.php';
require_once __DIR__ . '/../layouts/main.php';
