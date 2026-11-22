<?php
/**
 * views/admin/usuarios.php - Gestión de Personal y Cuentas de Acceso (RBAC)
 */
$titulo = "Gestión de Personal y Usuarios";
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- FORMULARIO CREAR / EDITAR -->
    <div class="card">
        <div class="card-header">
            <h3>Registrar o Editar Personal</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?c=admin&a=guardarUsuario">
                <?= Seguridad::inputCsrf() ?>
                <input type="hidden" name="id" id="usr_id">

                <div class="form-group">
                    <label class="form-label">Nombre Completo: *</label>
                    <input type="text" name="nombre_completo" id="usr_nombre" class="form-control" required placeholder="Ej. Lic. Carlos Mendoza">
                </div>

                <div class="form-group">
                    <label class="form-label">Nombre de Usuario (Login): *</label>
                    <input type="text" name="usuario" id="usr_login" class="form-control" required placeholder="Ej. cmendoza">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico (opcional):</label>
                    <input type="email" name="email" id="usr_email" class="form-control" placeholder="usuario@institucion.gob">
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña: <span id="pwd_hint" style="font-weight: normal; font-size: 11px; color: var(--text-muted);">(Obligatoria para nuevos)</span></label>
                    <input type="password" name="password" id="usr_password" class="form-control" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label">Rol del Sistema: *</label>
                    <select name="rol" id="usr_rol" class="form-control" required>
                        <option value="usuario">Usuario Operador de Área</option>
                        <option value="admin">Administrador General</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Departamento Asignado:</label>
                    <select name="departamento_id" id="usr_depto" class="form-control">
                        <option value="">-- Sin departamento asignado --</option>
                        <?php foreach ($departamentos as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= e($d['nombre']) ?> (<?= e($d['codigo']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="activo" id="usr_activo" value="1" checked>
                        <span>Usuario activo con permiso de acceso</span>
                    </label>
                </div>

                <div style="display: flex; gap: 8px; margin-top: 16px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="limpiarFormUsr()">Limpiar</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="flex-grow: 1;">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTADO -->
    <div class="card">
        <div class="card-header">
            <h3>Personal Registrado en el Sistema</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre y Usuario</th>
                        <th>Rol</th>
                        <th>Departamento</th>
                        <th>Último Acceso</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>
                                <strong><?= e($u['nombre_completo']) ?></strong>
                                <span style="display: block; font-size: 11px; color: var(--text-muted); font-family: monospace;">@<?= e($u['usuario']) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $u['rol'] === 'admin' ? 'badge-purple' : 'badge-blue' ?>">
                                    <?= strtoupper($u['rol']) ?>
                                </span>
                            </td>
                            <td><?= e($u['departamento_nombre'] ?? 'Sin área asignada') ?></td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                <?= $u['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) : 'Nunca' ?>
                            </td>
                            <td>
                                <span class="badge <?= $u['activo'] ? 'badge-emerald' : 'badge-rose' ?>">
                                    <?= $u['activo'] ? 'Activo' : 'Bloqueado' ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="editarUsr(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">
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
function editarUsr(u) {
    document.getElementById('usr_id').value = u.id;
    document.getElementById('usr_nombre').value = u.nombre_completo;
    document.getElementById('usr_login').value = u.usuario;
    document.getElementById('usr_email').value = u.email || '';
    document.getElementById('usr_password').value = '';
    document.getElementById('usr_rol').value = u.rol;
    document.getElementById('usr_depto').value = u.departamento_id || '';
    document.getElementById('usr_activo').checked = (u.activo == 1);
    document.getElementById('pwd_hint').innerText = '(Dejar en blanco para mantener la actual)';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function limpiarFormUsr() {
    document.getElementById('usr_id').value = '';
    document.getElementById('usr_nombre').value = '';
    document.getElementById('usr_login').value = '';
    document.getElementById('usr_email').value = '';
    document.getElementById('usr_password').value = '';
    document.getElementById('usr_rol').value = 'usuario';
    document.getElementById('usr_depto').value = '';
    document.getElementById('usr_activo').checked = true;
    document.getElementById('pwd_hint').innerText = '(Obligatoria para nuevos)';
}
</script>

<?php
$vistaHija = __DIR__ . '/usuarios.php';
require_once __DIR__ . '/../layouts/main.php';
