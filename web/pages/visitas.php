<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
$morador_nome = $_SESSION['nome'];
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Visitas - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Visitas</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu</p>
        <button class="nav-item" onclick="window.location.href='dashboard_morador.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item active">
            <i class="fa-solid fa-user-plus"></i><span>Visitas</span>
        </button>
        <button class="nav-item" onclick="window.location.href='minhas_ocorrencias.php'">
            <i class="fa-solid fa-exclamation-triangle"></i><span>Ocorrências</span>
        </button>
    </nav>
    <div class="sidebar-footer">
        <div class="avatar-admin"><?php echo strtoupper(substr($morador_nome, 0, 2)); ?></div>
        <div style="flex:1;">
            <p class="af-name"><?php echo htmlspecialchars($morador_nome); ?></p>
            <p class="af-role">Morador</p>
        </div>
        <a href="../api/logout.php" title="Sair" style="color:var(--text-muted); font-size:1rem;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo — Visitas</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active">
        <div class="page-header">
            <h1 class="page-title">🚪 Gestão de Visitas</h1>
            <p class="page-sub">Registe e acompanhe visitantes</p>
        </div>

        <div class="card">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-plus"></i> Nova Visita</p>
            </div>
            <form style="padding:20px;">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nome do Visitante</label>
                        <input type="text" placeholder="Nome completo" required>
                    </div>
                    <div class="form-group">
                        <label>BI do Visitante</label>
                        <input type="text" placeholder="Nº do Bilhete de Identidade">
                    </div>
                    <div class="form-group">
                        <label>Data</label>
                        <input type="date" required>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <input type="time" required>
                    </div>
                </div>
                <button class="btn-primary" onclick="alert('Funcionalidade em desenvolvimento')">
                    <i class="fa-solid fa-plus"></i> Registrar Visita
                </button>
            </form>
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-list"></i> Visitas Agendadas</p>
            </div>
            <div style="padding:20px;text-align:center;color:var(--text-muted);">
                <i class="fa-solid fa-calendar" style="font-size:48px;opacity:0.3;"></i>
                <p style="margin-top:10px;">Nenhuma visita agendada</p>
            </div>
        </div>
    </section>
</main>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
function clock() {
    const now = new Date();
    const el = document.getElementById('clock-display');
    if (el) el.textContent = now.toLocaleTimeString('pt-AO');
}
window.onload = function() { clock(); setInterval(clock, 1000); };
</script>
</body>
</html>