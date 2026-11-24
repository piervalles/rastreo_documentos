<?php
/**
 * views/layouts/main.php - Plantilla Maestra del Sistema
 */

require_once __DIR__ . '/../../helpers/Seguridad.php';
$flash = Seguridad::getFlash();
$esAdmin = Seguridad::esAdmin();
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$deptoNombre = !empty($_SESSION['departamento_nombre']) ? $_SESSION['departamento_nombre'] : 'Sin área asignada';
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? e($titulo) . ' - ' : '' ?>Gestión Documental</title>
    <link rel="stylesheet" href="assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../assets/css/app.css') ?>">
</head>
<body>

<div class="app-container">
    <!-- BARRA LATERAL (SIDEBAR) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <div class="brand-text">
                    <h2>Gestión Documental</h2>
                    <span>Rastreo y Trazabilidad</span>
                </div>
            </div>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-label">Operaciones</div>
            <a href="index.php?c=documentos&a=index" class="menu-item <?= (($_GET['c'] ?? 'documentos') === 'documentos' && ($_GET['a'] ?? 'index') === 'index') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                Bandeja de Documentos
            </a>
            <a href="index.php?c=documentos&a=crear" class="menu-item <?= (($_GET['c'] ?? '') === 'documentos' && ($_GET['a'] ?? '') === 'crear') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                Registrar Documento
            </a>

            <?php if ($esAdmin): ?>
                <div class="menu-label">Administración</div>
                <a href="index.php?c=admin&a=departamentos" class="menu-item <?= (($_GET['c'] ?? '') === 'admin' && ($_GET['a'] ?? '') === 'departamentos') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-4"></path></svg>
                    Áreas / Departamentos
                </a>
                <a href="index.php?c=admin&a=estados" class="menu-item <?= (($_GET['c'] ?? '') === 'admin' && ($_GET['a'] ?? '') === 'estados') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Catálogos de Estados
                </a>
                <a href="index.php?c=admin&a=usuarios" class="menu-item <?= (($_GET['c'] ?? '') === 'admin' && ($_GET['a'] ?? '') === 'usuarios') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Personal y Usuarios
                </a>
                <a href="index.php?c=backup&a=index" class="menu-item <?= (($_GET['c'] ?? '') === 'backup') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Copia de Seguridad
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="user-badge">
                <div class="user-avatar"><?= strtoupper(substr($usuarioNombre, 0, 1)) ?></div>
                <div style="overflow: hidden;">
                    <strong class="user-name"><?= e($usuarioNombre) ?></strong>
                    <span class="user-role"><?= e($deptoNombre) ?> (<?= strtoupper($rol) ?>)</span>
                </div>
            </div>
            <a href="index.php?c=auth&a=logout" class="btn btn-secondary btn-sm" style="width: 100%;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- ÁREA PRINCIPAL -->
    <main class="main-content">
        <header class="topbar">
            <h1 class="page-title"><?= isset($titulo) ? e($titulo) : 'Gestión Documental' ?></h1>
            <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: var(--text-muted);">
                <span>Área: <strong><?= e($deptoNombre) ?></strong></span>
                <span>•</span>
                <span id="reloj-sistema"><?= date('d/m/Y H:i') ?></span>
            </div>
        </header>

        <div class="content-body">
            <!-- MENSAJES FLASH -->
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['tipo']) ?>">
                    <span><?= e($flash['mensaje']) ?></span>
                    <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; font-weight: bold; opacity: 0.7;">&times;</button>
                </div>
            <?php endif; ?>

            <!-- CONTENIDO DE LA VISTA DINÁMICA -->
            <?= $contenido ?? '' ?>
        </div>
    </main>
</div>

<!-- Sincronización Reactiva en Red Local (Fase 6: Revalidación al enfocar y Notificaciones Flotantes) -->
<div id="toast-notificacion" style="display: none; position: fixed; bottom: 24px; right: 24px; background: #0f172a; color: #ffffff; border-left: 4px solid #3b82f6; border-radius: 10px; padding: 16px 20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); z-index: 9999; max-width: 360px; font-size: 13px; animation: slideUp 0.3s ease;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
        <div>
            <strong style="color: #60a5fa; display: block; margin-bottom: 4px;">🔔 ¡Nuevo Documento en tu Área!</strong>
            <p id="toast-mensaje" style="margin: 0; color: #cbd5e1; font-size: 12px;"></p>
        </div>
        <button onclick="document.getElementById('toast-notificacion').style.display='none'" style="background:none; border:none; color:#94a3b8; cursor:pointer; font-size:16px;">&times;</button>
    </div>
    <div style="margin-top: 10px; text-align: right;">
        <button onclick="window.location.reload()" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 3px 8px;">Ver Documentos</button>
    </div>
</div>

<script>
    // 1. Revalidación al enfocar la pestaña
    let ultimaInteraccion = Date.now();
    window.addEventListener('focus', function() {
        if (Date.now() - ultimaInteraccion > 120000) {
            window.location.reload();
        }
        ultimaInteraccion = Date.now();
    });

    // 2. Sondeo en segundo plano cada 12 segundos para avisar pases entre oficinas
    let ultimoTimestamp = '<?= date("Y-m-d H:i:s") ?>';

    setInterval(function() {
        fetch('index.php?c=documentos&a=comprobarNovedades&desde=' + encodeURIComponent(ultimoTimestamp))
            .then(res => res.json())
            .then(data => {
                if (data.timestamp) {
                    ultimoTimestamp = data.timestamp;
                }
                if (data.nuevos > 0) {
                    const toast = document.getElementById('toast-notificacion');
                    const msg = document.getElementById('toast-mensaje');
                    msg.innerHTML = `Llegó <strong>${data.folio}</strong> desde <em>${data.origen}</em>:<br>${data.asunto}`;
                    toast.style.display = 'block';
                }
            })
            .catch(err => {
                // Silencioso en caso de pérdida momentánea de red
            });
    }, 12000);
</script>

</body>
</html>
