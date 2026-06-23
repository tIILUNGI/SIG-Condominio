<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = $_SESSION['id'];
$morador_nome = $_SESSION['nome'];
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comunicação - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
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
            <p class="brand-sub">Comunicação</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu</p>
        <button class="nav-item" onclick="window.location.href='dashboard_morador.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item active">
            <i class="fa-solid fa-comments"></i><span>Comunicação</span>
        </button>
        <button class="nav-item" onclick="window.location.href='minhas_ocorrencias.php'">
            <i class="fa-solid fa-exclamation-triangle"></i><span>Ocorrências</span>
        </button>
        <button class="nav-item" onclick="window.location.href='minhas_mensalidades.php'">
            <i class="fa-solid fa-credit-card"></i><span>Mensalidades</span>
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
        <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo — Comunicação</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active">
        <div class="page-header">
            <h1 class="page-title">📢 Central de Comunicação</h1>
            <p class="page-sub">Mensagens, avisos e comunicados do condomínio</p>
        </div>

        <div class="card">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-bullhorn"></i> Avisos do Condomínio</p>
            </div>
            <div style="padding:20px;">
                <div style="background:var(--dark4);border-radius:10px;padding:20px;margin-bottom:15px;border-left:4px solid var(--gold);">
                    <h3 style="color:var(--gold);">📢 Reunião Geral - 30 de Junho</h3>
                    <p style="color:var(--text-muted);margin:10px 0;">Convocamos todos os moradores para a reunião geral do condomínio no dia 30/06 às 18h no salão de festas.</p>
                    <span style="font-size:12px;color:var(--text-muted);"><i class="fa-regular fa-clock"></i> Publicado: 19/06/2026</span>
                </div>
                <div style="background:var(--dark4);border-radius:10px;padding:20px;margin-bottom:15px;border-left:4px solid #3498db;">
                    <h3 style="color:#3498db;">🔧 Manutenção Programada</h3>
                    <p style="color:var(--text-muted);margin:10px 0;">No dia 25/06 haverá manutenção na bomba de água das 08h às 12h.</p>
                    <span style="font-size:12px;color:var(--text-muted);"><i class="fa-regular fa-clock"></i> Publicado: 18/06/2026</span>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-envelope"></i> Mensagens</p>
            </div>
            <div style="padding:20px;text-align:center;color:var(--text-muted);">
                <i class="fa-solid fa-inbox" style="font-size:48px;opacity:0.3;"></i>
                <p style="margin-top:10px;">Nenhuma mensagem nova</p>
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