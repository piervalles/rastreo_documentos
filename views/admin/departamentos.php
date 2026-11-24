<?php
/**
 * views/admin/departamentos.php - Catálogo Dinámico de Departamentos / Oficinas
 */
$titulo = "Administración de Áreas y Departamentos";
?>

<!-- LISTADO A ANCHO COMPLETO -->
<div class="card" style="width: 100%;">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px;">
        <div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">Catálogo de Departamentos Registrados</h3>
            <span style="font-size: 12px; color: var(--text-muted); display: block; margin-top: 2px;">
                <?= count($departamentos) ?> áreas configuradas en el sistema institucional
            </span>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalNuevoDepto()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Nueva Área
        </button>
    </div>
    <div class="table-responsive">
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 120px;">Código</th>
                    <th style="width: 250px;">Nombre del Área</th>
                    <th style="width: 130px;">Tipo</th>
                    <th>Descripción / Funciones</th>
                    <th style="width: 120px;">Estado</th>
                    <th style="width: 180px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($departamentos)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No hay departamentos registrados en el catálogo institucional.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($departamentos as $d): ?>
                        <tr>
                            <td>
                                <span style="font-family: monospace; font-weight: 700; font-size: 13px; color: var(--text-main); background: #f1f5f9; padding: 3px 8px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                    <?= e($d['codigo']) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--text-main); font-size: 13.5px;"><?= e($d['nombre']) ?></strong>
                            </td>
                            <td>
                                <?php if (!empty($d['es_sistema'])): ?>
                                    <span class="badge badge-blue" title="Área estructural base del sistema">🛡️ Base</span>
                                <?php else: ?>
                                    <span class="badge badge-slate" title="Área creada por usuarios">Personalizada</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--text-muted); font-size: 12.5px;">
                                <?= e($d['descripcion'] ?? '—') ?>
                            </td>
                            <td>
                                <span class="badge <?= $d['activo'] ? 'badge-emerald' : 'badge-slate' ?>">
                                    <?= $d['activo'] ? '● Activo' : '○ Inactivo' ?>
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="editarDepto(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)">
                                        Editar
                                    </button>

                                    <?php if (!empty($d['es_sistema'])): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" style="color: #94a3b8; border-color: #e2e8f0;" onclick="alertaDeptoSistema(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)" title="Área base protegida">
                                            🔒 Eliminar
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="solicitarEliminarDepto(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)">
                                            Eliminar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL EMERGENTE: FORMULARIO CREAR / EDITAR ÁREA -->
<div id="modal-form-depto" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(2px); padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: popIn 0.2s ease;">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <h3 id="form-titulo" style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Nueva Área / Departamento</h3>
            <button type="button" onclick="cerrarModalFormDepto()" style="background: none; border: none; font-size: 22px; color: #94a3b8; cursor: pointer; padding: 0 4px; line-height: 1;">&times;</button>
        </div>

        <form method="POST" action="index.php?c=admin&a=guardarDepartamento" id="form-depto" style="margin: 0;">
            <?= Seguridad::inputCsrf() ?>
            <input type="hidden" name="id" id="depto_id">

            <div style="padding: 22px 24px; max-height: calc(85vh - 130px); overflow-y: auto;">
                <!-- AVISO SUTIL PARA ÁREAS BASE DEL SISTEMA -->
                <div id="aviso-sistema-edicion" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px; color: #1e40af; line-height: 1.4;">
                    <div style="display: flex; align-items: flex-start; gap: 8px;">
                        <span style="font-size: 16px; line-height: 1;">🛡️</span>
                        <div>
                            <strong>Área Base Protegida del Sistema</strong>
                            <p style="margin-top: 3px; color: #2563eb; font-size: 11.5px; line-height: 1.35;">
                                El nombre y código de esta área son estructurales y no se pueden modificar. Puedes <strong>activar o desactivar</strong> su disponibilidad operativa para controlar si se muestra al crear o derivar documentos.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" id="label-nombre">Nombre del Área: *</label>
                    <input type="text" name="nombre" id="depto_nombre" class="form-control" required placeholder="Ej. Asesoría Legal">
                    <span id="helper-nombre" style="display: none; font-size: 11px; color: var(--text-muted); margin-top: 3px;">Nombre protegido por el sistema.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" id="label-codigo">Código / Sigla: *</label>
                    <input type="text" name="codigo" id="depto_codigo" class="form-control" required placeholder="Ej. AL o ASES-LEG">
                    <span id="helper-codigo" style="display: none; font-size: 11px; color: var(--text-muted); margin-top: 3px;">Código protegido por el sistema.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción (opcional):</label>
                    <textarea name="descripcion" id="depto_desc" class="form-control" rows="2" placeholder="Funciones principales del área..."></textarea>
                </div>

                <div class="form-group" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="activo" id="depto_activo" value="1" checked style="width: 16px; height: 16px;">
                        <div>
                            <strong style="display: block; font-size: 13px; color: var(--text-main);">Estado Operativo Activo</strong>
                            <span style="display: block; font-size: 11.5px; color: var(--text-muted);">
                                Si se desactiva, el área NO aparecerá en opciones para nuevos documentos ni traslados.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="cerrarModalFormDepto()">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm" id="btn-submit-depto">Guardar Área</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EMERGENTE: ÁREA PROTEGIDA DEL SISTEMA -->
<div id="modal-sistema" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(2px); padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: popIn 0.2s ease;">
        <div style="padding: 24px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: flex-start; gap: 14px;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                🛡️
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Área Base Protegida del Sistema</h3>
                <span style="font-size: 12px; color: #64748b;">Protección de integridad estructural de expedientes</span>
            </div>
        </div>
        <div style="padding: 24px; font-size: 13.5px; color: #334155; line-height: 1.5;">
            <p>
                El departamento <strong id="modal-sistema-nombre" style="color: #0f172a;"></strong> es un <strong>componente estructural base del sistema</strong> y no puede ser eliminado para garantizar la trazabilidad institucional.
            </p>
            <div style="margin-top: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 12.5px; color: #475569;">
                💡 <strong>¿No requieres usar esta oficina?</strong> Puedes <strong>desactivarla</strong> para que ningún usuario pueda seleccionarla al registrar nuevos documentos o generar derivaciones.
            </div>
        </div>
        <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="cerrarModalSistema()">Entendido</button>
            <button type="button" class="btn btn-primary btn-sm" id="btn-ir-desactivar" onclick="">Ir a Configurar Estado</button>
        </div>
    </div>
</div>

<!-- MODAL EMERGENTE: CONFIRMACIÓN DE ELIMINACIÓN DE ÁREA PERSONALIZADA -->
<div id="modal-eliminar" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(2px); padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 460px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: popIn 0.2s ease;">
        <div style="padding: 24px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: flex-start; gap: 14px;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                ⚠️
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Confirmar Eliminación de Área</h3>
                <span style="font-size: 12px; color: #dc2626;">Esta operación es irreversible</span>
            </div>
        </div>
        <div style="padding: 24px; font-size: 13.5px; color: #334155; line-height: 1.5;">
            <p>
                ¿Está seguro de que desea eliminar permanentemente el área <strong id="modal-eliminar-nombre" style="color: #0f172a;"></strong>?
            </p>
            <p style="margin-top: 8px; font-size: 12px; color: #64748b;">
                El área será retirada permanentemente del catálogo siempre que no cuente con expedientes ni movimientos asociados en su bitácora.
            </p>
        </div>
        <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
            <form method="POST" action="index.php?c=admin&a=eliminarDepartamento" style="display: flex; gap: 10px; margin: 0; width: 100%; justify-content: flex-end;">
                <?= Seguridad::inputCsrf() ?>
                <input type="hidden" name="id" id="modal-eliminar-id">
                <button type="button" class="btn btn-secondary btn-sm" onclick="cerrarModalEliminar()">Cancelar</button>
                <button type="submit" class="btn btn-danger btn-sm">Sí, Eliminar Permanentemente</button>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes popIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<script>
let deptoEnEdicion = null;

function abrirModalNuevoDepto() {
    limpiarFormDepto();
    document.getElementById('modal-form-depto').style.display = 'flex';
    document.getElementById('depto_nombre').focus();
}

function cerrarModalFormDepto() {
    document.getElementById('modal-form-depto').style.display = 'none';
}

function editarDepto(d) {
    deptoEnEdicion = d;
    document.getElementById('depto_id').value = d.id;
    document.getElementById('depto_nombre').value = d.nombre;
    document.getElementById('depto_codigo').value = d.codigo;
    document.getElementById('depto_desc').value = d.descripcion || '';
    document.getElementById('depto_activo').checked = (d.activo == 1);

    const esSistema = (d.es_sistema == 1);
    const avisoSistema = document.getElementById('aviso-sistema-edicion');
    const inputNombre = document.getElementById('depto_nombre');
    const inputCodigo = document.getElementById('depto_codigo');
    const helperNombre = document.getElementById('helper-nombre');
    const helperCodigo = document.getElementById('helper-codigo');
    const formTitulo = document.getElementById('form-titulo');
    const btnSubmit = document.getElementById('btn-submit-depto');

    if (esSistema) {
        avisoSistema.style.display = 'block';
        formTitulo.innerText = 'Configurar Estado: ' + d.nombre;
        inputNombre.readOnly = true;
        inputCodigo.readOnly = true;
        inputNombre.style.backgroundColor = '#f1f5f9';
        inputCodigo.style.backgroundColor = '#f1f5f9';
        inputNombre.style.cursor = 'not-allowed';
        inputCodigo.style.cursor = 'not-allowed';
        helperNombre.style.display = 'block';
        helperCodigo.style.display = 'block';
        btnSubmit.innerText = 'Actualizar Estado Operativo';
    } else {
        avisoSistema.style.display = 'none';
        formTitulo.innerText = 'Editar Área: ' + d.nombre;
        inputNombre.readOnly = false;
        inputCodigo.readOnly = false;
        inputNombre.style.backgroundColor = '#ffffff';
        inputCodigo.style.backgroundColor = '#ffffff';
        inputNombre.style.cursor = 'text';
        inputCodigo.style.cursor = 'text';
        helperNombre.style.display = 'none';
        helperCodigo.style.display = 'none';
        btnSubmit.innerText = 'Guardar Cambios';
    }

    document.getElementById('modal-form-depto').style.display = 'flex';
    if (esSistema) {
        document.getElementById('depto_activo').focus();
    } else {
        document.getElementById('depto_nombre').focus();
    }
}

function limpiarFormDepto() {
    deptoEnEdicion = null;
    document.getElementById('depto_id').value = '';
    document.getElementById('depto_nombre').value = '';
    document.getElementById('depto_codigo').value = '';
    document.getElementById('depto_desc').value = '';
    document.getElementById('depto_activo').checked = true;

    document.getElementById('aviso-sistema-edicion').style.display = 'none';
    document.getElementById('form-titulo').innerText = 'Nueva Área / Departamento';

    const inputNombre = document.getElementById('depto_nombre');
    const inputCodigo = document.getElementById('depto_codigo');
    inputNombre.readOnly = false;
    inputCodigo.readOnly = false;
    inputNombre.style.backgroundColor = '#ffffff';
    inputCodigo.style.backgroundColor = '#ffffff';
    inputNombre.style.cursor = 'text';
    inputCodigo.style.cursor = 'text';

    document.getElementById('helper-nombre').style.display = 'none';
    document.getElementById('helper-codigo').style.display = 'none';
    document.getElementById('btn-submit-depto').innerText = 'Guardar Área';
}

function alertaDeptoSistema(d) {
    document.getElementById('modal-sistema-nombre').innerText = d.nombre;
    const btnDesactivar = document.getElementById('btn-ir-desactivar');
    btnDesactivar.onclick = function() {
        cerrarModalSistema();
        editarDepto(d);
    };
    const modal = document.getElementById('modal-sistema');
    modal.style.display = 'flex';
}

function cerrarModalSistema() {
    document.getElementById('modal-sistema').style.display = 'none';
}

function solicitarEliminarDepto(d) {
    document.getElementById('modal-eliminar-id').value = d.id;
    document.getElementById('modal-eliminar-nombre').innerText = d.nombre;
    const modal = document.getElementById('modal-eliminar');
    modal.style.display = 'flex';
}

function cerrarModalEliminar() {
    document.getElementById('modal-eliminar').style.display = 'none';
}

// Cerrar modales con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalFormDepto();
        cerrarModalSistema();
        cerrarModalEliminar();
    }
});

// Cerrar modal al hacer clic en el fondo oscuro exterior
window.addEventListener('click', function(e) {
    const modalForm = document.getElementById('modal-form-depto');
    const modalSistema = document.getElementById('modal-sistema');
    const modalEliminar = document.getElementById('modal-eliminar');

    if (e.target === modalForm) cerrarModalFormDepto();
    if (e.target === modalSistema) cerrarModalSistema();
    if (e.target === modalEliminar) cerrarModalEliminar();
});
</script>
