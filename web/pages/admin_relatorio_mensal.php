<?php
session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Relatório Mensal - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
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
            <p class="brand-sub">Administrativo</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Gestão</p>
        <button class="nav-item" onclick="window.location.href='admin_portal.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item" onclick="window.location.href='admin_funcionarios.php'">
            <i class="fa-solid fa-inbox"></i><span>Cadastro de Funcionários</span>
        </button>
        <button class="nav-item" onclick="window.location.href='admin_moradores.php'">
            <i class="fa-solid fa-users"></i><span>Cadastro de Moradores</span>
        </button>
        <button class="nav-item" onclick="window.location.href='admin_casas.php'">
            <i class="fa-solid fa-house-chimney"></i><span>Gestão de Casas</span>
        </button>
        <p class="nav-section">Finanças</p>
        <button class="nav-item" onclick="window.location.href='admin_pagamentos_visitantes.php'">
            <i class="fa-solid fa-money-bill-transfer"></i><span>Pagamentos</span>
        </button>
        <button class="nav-item" onclick="window.location.href='admin_pagamentos_moradores.php'">
            <i class="fa-solid fa-id-badge"></i><span>Pagamentos Moradores</span>
        </button>
        <button class="nav-item" onclick="window.location.href='admin-comunicacao.php'">
            <i class="fa-solid fa-comments"></i><span>Comunicação</span>
        </button>
        <p class="nav-section">Relatórios</p>
        <button class="nav-item active" onclick="window.location.href='admin_relatorio_mensal.php'">
            <i class="fa-solid fa-chart-pie"></i><span>Relatório Mensal</span>
        </button>
        <p class="nav-section">Utilizador</p>
        <button class="nav-item" onclick="window.location.href='perfil_admin.php'">
            <i class="fa-solid fa-user-gear"></i><span>Meu Perfil</span>
        </button>
    </nav>
    <div class="sidebar-footer">
        <div class="avatar-admin"><?php echo strtoupper(substr($_SESSION['nome'], 0, 2)); ?></div>
        <div style="flex:1;">
            <p class="af-name"><?php echo htmlspecialchars($_SESSION['nome']); ?></p>
            <p class="af-role"><?php echo ucfirst($_SESSION['tipo']); ?></p>
        </div>
        <a href="../api/logout.php" title="Sair" style="color:var(--text-muted); font-size:1rem;"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="topbar-title">📊 Relatórios Administrativos</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
        </div>
    </header>

    <div style="padding: 2.5rem 3rem;">
        <div class="page-header no-print">
            <h1 class="page-title">Relatório Consolidado</h1>
            <p class="page-sub">Visão geral do desempenho mensal do condomínio</p>
        </div>

        <div id="relatorio-content">
             <div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Gerando relatório...</p></div>
        </div>
    </div>
</main>

<script>
const API_URL = '../api/api_dashboard.php';

async function buildReport() {
    const container = document.getElementById('relatorio-content');
    try {
        const res = await fetch(`${API_URL}?acao=resumo`);
        const data = await res.json();
        const s = data.dados;
        const fmt = (v) => new Intl.NumberFormat('pt-AO').format(v || 0);

        container.innerHTML = `
            <div class="card" style="padding: 3rem; max-width: 900px; margin: 0 auto; border: 2px solid var(--border);">
                <div style="text-align: center; margin-bottom: 3rem;">
                    <i class="fa-solid fa-building-columns" style="font-size: 3rem; color: var(--gold); margin-bottom: 1rem;"></i>
                    <h2 style="font-family: 'DM Sans', sans-serif; font-weight: 700;">NOSSO ZIMBO - CONDOMÍNIO LUXO</h2>
                    <p style="color:var(--text-muted);">Relatório Executivo Administrativo</p>
                    <p style="margin-top:0.5rem; font-weight:600;">Mês de Referência: ${new Date().toLocaleDateString('pt-AO', {month:'long', year:'numeric'})}</p>
                </div>

                <div class="stat-grid" style="margin-bottom: 3rem;">
                    <div class="stat-card" style="background:var(--dark3);">
                        <p class="stat-label">Receita Operacional</p>
                        <p class="stat-value" style="color:var(--gold);">${fmt(s.receitas_mes)} Kz</p>
                    </div>
                    <div class="stat-card" style="background:var(--dark3);">
                        <p class="stat-label">Taxa de Ocupação</p>
                        <p class="stat-value">${s.total_apartamentos > 0 ? Math.round((s.apartamentos_ocupados/s.total_apartamentos)*100) : 0}%</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
                    <div style="background: var(--bg); padding: 2rem; border-radius: 12px; border: 1px solid var(--border);">
                        <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Unidades Habitacionais</h4>
                        <p style="margin: 0.5rem 0;">Total de Casas: <strong>${s.total_apartamentos}</strong></p>
                        <p style="margin: 0.5rem 0;">Casas Ocupadas: <strong>${s.apartamentos_ocupados}</strong></p>
                        <p style="margin: 0.5rem 0;">Casas Disponíveis: <strong style="color:var(--success);">${s.apartamentos_disponiveis}</strong></p>
                    </div>
                    <div style="background: var(--bg); padding: 2rem; border-radius: 12px; border: 1px solid var(--border);">
                        <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Recursos Humanos</h4>
                        <p style="margin: 0.5rem 0;">Equipa Administrativa: <strong>${s.total_admins}</strong></p>
                        <p style="margin: 0.5rem 0;">Residentes Activos: <strong>${s.total_moradores}</strong></p>
                        <p style="margin: 0.5rem 0;">Pagamentos Pendentes: <strong style="color:var(--danger);">${s.mensalidades_pendentes}</strong></p>
                    </div>
                </div>

                <div style="border-top: 2px solid var(--border); padding-top: 2rem; text-align: center;" class="no-print">
                    <button class="btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir / Exportar PDF</button>
                </div>
                
                <div style="margin-top: 4rem; text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                    Gerado eletronicamente em ${new Date().toLocaleString()} por Nosso Zimbo Admin System
                </div>
            </div>
        `;
    } catch(e) { container.innerHTML = '<p>Erro ao gerar relatório.</p>'; }
}

function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }

window.onload = () => {
    buildReport();
    setInterval(() => {
        const el = document.getElementById('clock-display');
        if (el) el.textContent = new Date().toLocaleTimeString('pt-AO');
    }, 1000);
};
</script>
<style>
@media print {
    .sidebar, .topbar, .no-print, .page-header { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    body { background: white !important; color: black !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</script>
</body>
</html>
