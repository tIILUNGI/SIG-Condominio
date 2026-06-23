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
    <title>Áreas Comuns - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="../js/theme-manager.js"></script>
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Áreas Comuns</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu</p>
        <button class="nav-item" onclick="window.location.href='dashboard_morador.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item active">
            <i class="fa-solid fa-calendar-check"></i><span>Áreas Comuns</span>
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
        <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo — Áreas Comuns</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active">
        <div class="page-header">
            <h1 class="page-title">🏊 Áreas Comuns</h1>
            <p class="page-sub">Reserve espaços para eventos e lazer</p>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px,1fr)); gap:20px;">
            <div class="card">
                <div style="text-align:center;padding:10px;">
                    <i class="fa-solid fa-water-ladder" style="font-size:48px;color:#3498db;"></i>
                    <h3>Piscina</h3>
                    <p style="color:var(--text-muted);font-size:14px;">Disponível para reserva</p>
                    <button class="btn-primary" style="margin-top:10px;width:100%;" onclick="alert('Funcionalidade em desenvolvimento')">
                        <i class="fa-solid fa-calendar-plus"></i> Reservar
                    </button>
                </div>
            </div>
            <div class="card">
                <div style="text-align:center;padding:10px;">
                    <i class="fa-solid fa-utensils" style="font-size:48px;color:#f39c12;"></i>
                    <h3>Salão de Festas</h3>
                    <p style="color:var(--text-muted);font-size:14px;">Capacidade: 50 pessoas</p>
                    <button class="btn-primary" style="margin-top:10px;width:100%;" onclick="alert('Funcionalidade em desenvolvimento')">
                        <i class="fa-solid fa-calendar-plus"></i> Reservar
                    </button>
                </div>
            </div>
            <div class="card">
                <div style="text-align:center;padding:10px;">
                    <i class="fa-solid fa-fire" style="font-size:48px;color:#e74c3c;"></i>
                    <h3>Churrasqueira</h3>
                    <p style="color:var(--text-muted);font-size:14px;">Área coberta</p>
                    <button class="btn-primary" style="margin-top:10px;width:100%;" onclick="alert('Funcionalidade em desenvolvimento')">
                        <i class="fa-solid fa-calendar-plus"></i> Reservar
                    </button>
                </div>
            </div>
            <div class="card">
                <div style="text-align:center;padding:10px;">
                    <i class="fa-solid fa-futbol" style="font-size:48px;color:#27ae60;"></i>
                    <h3>Campo de Jogos</h3>
                    <p style="color:var(--text-muted);font-size:14px;">Futebol, basquete</p>
                    <button class="btn-primary" style="margin-top:10px;width:100%;" onclick="alert('Funcionalidade em desenvolvimento')">
                        <i class="fa-solid fa-calendar-plus"></i> Reservar
                    </button>
                </div>
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