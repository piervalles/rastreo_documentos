<?php
/**
 * views/admin/departamentos.php - Catálogo Dinámico de Departamentos / Oficinas
 */
$titulo = "Administración de Áreas y Departamentos";
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- FORMULARIO CREAR / EDITAR -->
    <div class="card">
        <div class="card-header">
            <h3>Nueva Área / Departamento</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?c=admin&a=guardarDepartamento">
                <?= Seguridad::inputCsrf() ?>
                <input type="hidden" name="id" id="depto_id">

                <div class="form-group">
                    <label class="form-label">Nombre del Área: *</label>
                    <input type="text" name="nombre" id="depto_nombre" class="form-control" required placeholder="Ej. Asesoría Legal">
                </div>

                <div class="form-group">
                    <label class="form-label">Código / Sigla: *</label>
                    <input type="text" name="codigo" id="depto_codigo" class="form-control" required placeholder="Ej. AL o ASES-LEG">
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción (opcional):</label>
                    <textarea name="descripcion" id="depto_desc" class="form-control" rows="2" placeholder="Funciones principales del área..."></textarea>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="activo" id="depto_activo" value="1" checked>
                        <span>Área activa para recibir documentos</span>
                    </label>
                </div>

                <div style="display: flex; gap: 8px; margin-top: 16px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="limpiarFormDepto()">Limpiar</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="flex-grow: 1;">Guardar Área</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTADO -->
    <div class="card">
        <div class="card-header">
            <h3>Catálogo de Departamentos Registrados</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre del Área</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departamentos as $d): ?>
                        <tr>
                            <td><strong><?= e($d['codigo']) ?></strong></td>
                            <td><?= e($d['nombre']) ?></td>
                            <td style="color: var(--text-muted); font-size: 12px;"><?= e($d['descripcion'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= $d['activo'] ? 'badge-emerald' : 'badge-slate' ?>">
                                    <?= $d['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="editarDepto(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editarDepto(d) {
    document.getElementById('depto_id').value = d.id;
    document.getElementById('depto_nombre').value = d.nombre;
    document.getElementById('depto_codigo').value = d.codigo;
    document.getElementById('depto_desc').value = d.descripcion || '';
    document.getElementById('depto_activo').checked = (d.activo == 1);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function limpiarFormDepto() {
    document.getElementById('depto_id').value = '';
    document.getElementById('depto_nombre').value = '';
    document.getElementById('depto_codigo').value = '';
    document.getElementById('depto_desc').value = '';
    document.getElementById('depto_activo').checked = true;
}
</script>
