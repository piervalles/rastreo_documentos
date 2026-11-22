<?php
/**
 * views/admin/estados.php - Doble Dimensión de Estados Configurables Dinámicamente
 */
$titulo = "Catálogos de Estados (Trámite vs Resolución)";
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

    <!-- 1. ESTADOS DE TRÁMITE OPERATIVO -->
    <div class="card">
        <div class="card-header" style="background: #eff6ff;">
            <div>
                <h3 style="color: #1e40af;">1. Estados de Trámite (Situación Operativa)</h3>
                <span style="font-size: 11px; color: var(--text-muted);">¿Dónde está y qué le están haciendo al papel físicamente?</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Formulario Rápido -->
            <form method="POST" action="index.php?c=admin&a=guardarEstadoTramite" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed var(--border-color);">
                <?= Seguridad::inputCsrf() ?>
                <input type="hidden" name="id" id="et_id">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre del Estado: *</label>
                        <input type="text" name="nombre" id="et_nombre" class="form-control" required placeholder="Ej. En revisión en Asesoría">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Color de Etiqueta:</label>
                        <select name="color" id="et_color" class="form-control">
                            <option value="emerald">Verde (Emerald)</option>
                            <option value="amber">Ámbar / Amarillo (Amber)</option>
                            <option value="blue">Azul (Blue)</option>
                            <option value="purple">Morado (Purple)</option>
                            <option value="rose">Rojo / Rosa (Rose)</option>
                            <option value="cyan">Celeste (Cyan)</option>
                            <option value="slate">Gris (Slate)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción Operativa:</label>
                    <input type="text" name="descripcion" id="et_desc" class="form-control" placeholder="Significado para el personal...">
                </div>

                <div style="display: flex; gap: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                        <input type="checkbox" name="activo" id="et_activo" value="1" checked>
                        <span>Estado activo</span>
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm" style="margin-left: auto;">Guardar Estado Operativo</button>
                </div>
            </form>

            <!-- Listado -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estadosTramite as $et): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?= e($et['color']) ?>"><?= e($et['nombre']) ?></span>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);"><?= e($et['descripcion'] ?? '—') ?></td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="editarET(<?= htmlspecialchars(json_encode($et), ENT_QUOTES) ?>)">Editar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. ESTADOS DE RESOLUCIÓN / DICTAMEN -->
    <div class="card">
        <div class="card-header" style="background: #fdf4ff;">
            <div>
                <h3 style="color: #86198f;">2. Estados de Dictamen (Resultado / Resolución)</h3>
                <span style="font-size: 11px; color: var(--text-muted);">¿Cuál es la decisión o fondo del trámite?</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Formulario Rápido -->
            <form method="POST" action="index.php?c=admin&a=guardarEstadoResolucion" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed var(--border-color);">
                <?= Seguridad::inputCsrf() ?>
                <input type="hidden" name="id" id="er_id">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre de Resolución: *</label>
                        <input type="text" name="nombre" id="er_nombre" class="form-control" required placeholder="Ej. Aprobado con Observación">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Color de Etiqueta:</label>
                        <select name="color" id="er_color" class="form-control">
                            <option value="emerald">Verde (Emerald)</option>
                            <option value="amber">Ámbar / Amarillo (Amber)</option>
                            <option value="blue">Azul (Blue)</option>
                            <option value="purple">Morado (Purple)</option>
                            <option value="rose">Rojo / Rosa (Rose)</option>
                            <option value="cyan">Celeste (Cyan)</option>
                            <option value="slate">Gris (Slate)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción del Dictamen:</label>
                    <input type="text" name="descripcion" id="er_desc" class="form-control" placeholder="Significado resolutivo...">
                </div>

                <div style="display: flex; gap: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                        <input type="checkbox" name="activo" id="er_activo" value="1" checked>
                        <span>Estado activo</span>
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm" style="margin-left: auto;">Guardar Dictamen</button>
                </div>
            </form>

            <!-- Listado -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estadosResolucion as $er): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?= e($er['color']) ?>"><?= e($er['nombre']) ?></span>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);"><?= e($er['descripcion'] ?? '—') ?></td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="editarER(<?= htmlspecialchars(json_encode($er), ENT_QUOTES) ?>)">Editar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function editarET(item) {
    document.getElementById('et_id').value = item.id;
    document.getElementById('et_nombre').value = item.nombre;
    document.getElementById('et_color').value = item.color || 'gray';
    document.getElementById('et_desc').value = item.descripcion || '';
    document.getElementById('et_activo').checked = (item.activo == 1);
}

function editarER(item) {
    document.getElementById('er_id').value = item.id;
    document.getElementById('er_nombre').value = item.nombre;
    document.getElementById('er_color').value = item.color || 'gray';
    document.getElementById('er_desc').value = item.descripcion || '';
    document.getElementById('er_activo').checked = (item.activo == 1);
}
</script>

<?php
$vistaHija = __DIR__ . '/estados.php';
require_once __DIR__ . '/../layouts/main.php';
