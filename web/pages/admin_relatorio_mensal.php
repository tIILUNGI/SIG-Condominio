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
        const [resResumo, resPagamentos, resMoradores, resCasas] = await Promise.all([
            fetch(`${API_URL}?acao=resumo`),
            fetch(`${API_URL}?acao=pagamentos`),
            fetch(`${API_URL}?acao=moradores`),
            fetch(`${API_URL}?acao=casas`)
        ]);
        const [dResumo, dPag, dMor, dCasas] = await Promise.all([resResumo.json(), resPagamentos.json(), resMoradores.json(), resCasas.json()]);
        const s = dResumo.dados;
        const pagamentos = dPag.dados || [];
        const moradores = dMor.dados || [];
        const casas = dCasas.dados || [];

        const fmt = (v) => new Intl.NumberFormat('pt-AO').format(v || 0);
        const fmtMoney = (v) => 'Kz ' + new Intl.NumberFormat('pt-AO', {minimumFractionDigits: 2}).format(v || 0);
        const now = new Date();
        const mesRef = now.toLocaleDateString('pt-AO', {month: 'long', year: 'numeric'});

        const totalConfirmado = pagamentos.filter(p => p.estado === 'confirmado').reduce((a, b) => a + parseFloat(b.valor_pago), 0);
        const totalPendente = pagamentos.filter(p => p.estado === 'pendente').reduce((a, b) => a + parseFloat(b.valor_pago), 0);
        const totalRejeitado = pagamentos.filter(p => p.estado === 'rejeitado').reduce((a, b) => a + parseFloat(b.valor_pago), 0);

        const metodos = {};
        pagamentos.forEach(p => { metodos[p.metodo] = (metodos[p.metodo] || 0) + 1; });

        const moradoresPendentes = moradores.filter(m => m.estado_conta !== 'Activo').length;

        const casasDispo = casas.filter(c => c.estado === 'Disponivel').length;
        const casasOcup = casas.filter(c => c.estado === 'Ocupado').length;
        const casasManut = casas.filter(c => c.estado === 'Manutencao').length;

        container.innerHTML = `
            <div class="rel-doc">
                <div class="rel-header">
                    <div class="rel-logo">🏛️</div>
                    <h1>Condomínio Nosso Zimbo</h1>
                    <p class="rel-subtitle">Relatório Mensal Consolidado</p>
                    <div class="rel-meta-top">
                        <span>Período: <strong>${mesRef}</strong></span>
                        <span>Gerado em: ${now.toLocaleDateString('pt-AO')} às ${now.toLocaleTimeString('pt-AO', {hour:'2-digit', minute:'2-digit'})}</span>
                    </div>
                </div>

                <div class="rel-section">
                    <h2 class="rel-section-title"><i class="fa-solid fa-chart-line"></i> Resumo Executivo</h2>
                    <div class="rel-kpi-grid">
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Receita do Mês (Confirmada)</div>
                            <div class="rel-kpi-value success">${fmtMoney(totalConfirmado)}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Receita Pendente</div>
                            <div class="rel-kpi-value warning">${fmtMoney(totalPendente)}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Receita Rejeitada</div>
                            <div class="rel-kpi-value danger">${fmtMoney(totalRejeitado)}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Total de Transações</div>
                            <div class="rel-kpi-value">${pagamentos.length}</div>
                        </div>
                    </div>
                </div>

                <div class="rel-section">
                    <h2 class="rel-section-title"><i class="fa-solid fa-house-chimney"></i> Gestão de Habitações</h2>
                    <div class="rel-kpi-grid">
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Total de Apartamentos</div>
                            <div class="rel-kpi-value">${s.total_apartamentos}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Ocupados</div>
                            <div class="rel-kpi-value success">${casasOcup}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Disponíveis</div>
                            <div class="rel-kpi-value info">${casasDispo}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Em Manutenção</div>
                            <div class="rel-kpi-value warning">${casasManut}</div>
                        </div>
                    </div>
                    ${casas.length > 0 ? `
                    <div class="rel-table-wrap">
                        <table class="rel-table">
                            <thead>
                                <tr><th>Código</th><th>Bloco</th><th>Número</th><th>Tipologia</th><th>Andar</th><th>Estado</th><th>Morador</th></tr>
                            </thead>
                            <tbody>
                                ${casas.map(c => `<tr>
                                    <td><strong>${c.codigo || '—'}</strong></td>
                                    <td>${c.bloco || '—'}</td>
                                    <td>${c.numero || '—'}</td>
                                    <td>${c.tipologia || '—'}</td>
                                    <td>${c.andar ?? '—'}</td>
                                    <td><span class="rel-badge ${c.estado}">${c.estado}</span></td>
                                    <td>${c.morador_nome || '<em style="color:var(--text-muted)">—</em>'}</td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>` : ''}
                </div>

                <div class="rel-section">
                    <h2 class="rel-section-title"><i class="fa-solid fa-users"></i> Comunidade</h2>
                    <div class="rel-kpi-grid">
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Administradores</div>
                            <div class="rel-kpi-value">${s.total_admins}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Moradores Registados</div>
                            <div class="rel-kpi-value">${s.total_moradores}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Com Pendências</div>
                            <div class="rel-kpi-value ${moradoresPendentes > 0 ? 'danger' : 'success'}">${moradoresPendentes}</div>
                        </div>
                    </div>
                </div>

                <div class="rel-section">
                    <h2 class="rel-section-title"><i class="fa-solid fa-coins"></i> Detalhamento Financeiro</h2>
                    <div class="rel-kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Mensalidades Pagas</div>
                            <div class="rel-kpi-value success">${s.mensalidades_pagas}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Mensalidades Pendentes</div>
                            <div class="rel-kpi-value warning">${s.mensalidades_pendentes}</div>
                        </div>
                        <div class="rel-kpi">
                            <div class="rel-kpi-label">Mensalidades Vencidas</div>
                            <div class="rel-kpi-value danger">${s.mensalidades_vencidas}</div>
                        </div>
                    </div>

                    ${pagamentos.length > 0 ? `
                    <div class="rel-table-wrap">
                        <table class="rel-table">
                            <thead>
                                <tr><th>Referência</th><th>Morador</th><th>Apartamento</th><th>Valor</th><th>Método</th><th>Data</th><th>Estado</th></tr>
                            </thead>
                            <tbody>
                                ${pagamentos.slice(0, 50).map(p => `<tr>
                                    <td><strong>${p.referencia || '—'}</strong></td>
                                    <td>${p.morador || '—'}</td>
                                    <td>${p.apartamento || '—'}</td>
                                    <td class="rel-val">${fmtMoney(p.valor_pago)}</td>
                                    <td>${p.metodo || '—'}</td>
                                    <td>${p.data_pagamento ? new Date(p.data_pagamento).toLocaleDateString('pt-AO') : '—'}</td>
                                    <td><span class="rel-badge ${p.estado}">${p.estado}</span></td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                        ${pagamentos.length > 50 ? `<p style="text-align:center; font-size:.8rem; color:var(--text-muted); margin-top:.5rem;">Mostrando 50 de ${pagamentos.length} registos.</p>` : ''}
                    </div>` : '<p style="text-align:center; color:var(--text-muted);">Sem pagamentos registados.</p>'}
                </div>

                <div class="rel-section">
                    <h2 class="rel-section-title"><i class="fa-solid fa-wallet"></i> Métodos de Pagamento</h2>
                    <div class="rel-methods-grid">
                        ${Object.entries(metodos).map(([metodo, qtd]) => `
                            <div class="rel-method-card">
                                <div class="rel-method-name">${metodo}</div>
                                <div class="rel-method-count">${qtd} transacção(ões)</div>
                            </div>
                        `).join('')}
                        ${Object.keys(metodos).length === 0 ? '<p style="color:var(--text-muted);">Sem dados.</p>' : ''}
                    </div>
                </div>

                <div class="rel-section">
                    <h2 class="rel-section-title"><i class="fa-solid fa-chart-area"></i> Receitas dos Últimos 6 Meses</h2>
                    <div class="rel-chart-bars">
                        ${s.receitas_6meses.map((val, i) => `
                            <div class="rel-bar-item">
                                <div class="rel-bar-label">${s.labels_6meses[i]}</div>
                                <div class="rel-bar-wrap">
                                    <div class="rel-bar" style="height: ${Math.max(((val / Math.max(...s.receitas_6meses)) * 100), 2)}%;"></div>
                                </div>
                                <div class="rel-bar-value">${fmt(val)} Kz</div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="rel-footer">
                    <div>
                        <strong>Documento gerado electronicamente</strong><br>
                        Condomínio Nosso Zimbo · ${now.toLocaleDateString('pt-AO')} ${now.toLocaleTimeString('pt-AO', {hour:'2-digit', minute:'2-digit'})}
                    </div>
                    <div class="rel-assinatura">
                        <div class="rel-assinatura-line">Assinatura Autorizada</div>
                    </div>
                </div>

                <div style="text-align:center; margin-top:2rem;" class="no-print">
                    <button class="btn-primary" onclick="window.print()" style="padding:.8rem 2rem; font-size:1rem;">
                        <i class="fa-solid fa-file-pdf"></i> Exportar / Imprimir PDF
                    </button>
                    <button class="btn-secondary" onclick="buildReport()" style="padding:.8rem 2rem; font-size:1rem; margin-left:.5rem;">
                        <i class="fa-solid fa-arrows-rotate"></i> Actualizar
                    </button>
                </div>
            </div>
        `;
    } catch(e) {
        container.innerHTML = '<p style="color:var(--danger);">Erro ao gerar relatório: ' + e.message + '</p>';
    }
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
    .rel-doc { padding: 0 !important; }
}

/* Estilos do Relatório PDF */
.rel-doc {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    padding: 2rem;
    font-family: Arial, sans-serif;
    color: #222;
}
.rel-header {
    text-align: center;
    border-bottom: 3px solid #1a252f;
    padding-bottom: 1.5rem;
    margin-bottom: 2rem;
}
.rel-logo {
    font-size: 2.5rem;
    margin-bottom: .5rem;
}
.rel-header h1 {
    font-size: 1.6rem;
    margin: .3rem 0;
    font-weight: 900;
}
.rel-subtitle {
    font-size: .9rem;
    color: #666;
    margin: .2rem 0;
}
.rel-meta-top {
    display: flex;
    justify-content: space-between;
    font-size: .8rem;
    color: #777;
    margin-top: .5rem;
    padding: .5rem 0;
    border-top: 1px solid #eee;
}
.rel-section {
    margin-bottom: 2.5rem;
    page-break-inside: avoid;
}
.rel-section-title {
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #1a252f;
    border-bottom: 2px solid #1a252f;
    padding-bottom: .5rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.rel-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.rel-kpi {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
}
.rel-kpi-label {
    font-size: .72rem;
    text-transform: uppercase;
    color: #666;
    margin-bottom: .3rem;
}
.rel-kpi-value {
    font-size: 1.3rem;
    font-weight: 800;
    color: #222;
}
.rel-kpi-value.success { color: #27ae60; }
.rel-kpi-value.warning { color: #f0a500; }
.rel-kpi-value.danger { color: #e74c3c; }
.rel-kpi-value.info { color: #3498db; }

.rel-table-wrap {
    overflow-x: auto;
    margin-top: 1rem;
}
.rel-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .82rem;
}
.rel-table th {
    background: #1a252f;
    color: #fff;
    padding: .5rem .7rem;
    text-align: left;
    font-size: .7rem;
    text-transform: uppercase;
}
.rel-table td {
    padding: .45rem .7rem;
    border-bottom: 1px solid #ddd;
}
.rel-table tr:nth-child(even) td {
    background: #f9f9f9;
}
.rel-val {
    font-weight: 700;
}
.rel-badge {
    display: inline-block;
    padding: .15rem .5rem;
    border-radius: 10px;
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
}
.rel-badge.confirmado, .rel-badge.pago { background: #d4edda; color: #155724; }
.rel-badge.pendente, .rel-badge.Pendente { background: #fff3cd; color: #856404; }
.rel-badge.rejeitado, .rel-badge.Rejeitado { background: #f8d7da; color: #721c24; }
.rel-badge.Disponivel { background: #d1ecf1; color: #0c5460; }
.rel-badge.Ocupado { background: #d4edda; color: #155724; }
.rel-badge.Manutencao { background: #fff3cd; color: #856404; }

.rel-methods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
}
.rel-method-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
}
.rel-method-name {
    font-weight: 700;
    font-size: .9rem;
    margin-bottom: .3rem;
}
.rel-method-count {
    font-size: .75rem;
    color: #666;
}

.rel-chart-bars {
    display: flex;
    align-items: flex-end;
    gap: .8rem;
    height: 200px;
    padding: 1rem 0;
    border-bottom: 2px solid #1a252f;
}
.rel-bar-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .3rem;
}
.rel-bar-label {
    font-size: .7rem;
    color: #666;
    text-align: center;
}
.rel-bar-wrap {
    width: 100%;
    height: 180px;
    background: #f1f3f5;
    border-radius: 4px 4px 0 0;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}
.rel-bar {
    width: 70%;
    background: linear-gradient(180deg, #1a252f 0%, #2c3e50 100%);
    border-radius: 4px 4px 0 0;
    min-height: 4px;
}
.rel-bar-value {
    font-size: .7rem;
    font-weight: 700;
    color: #1a252f;
}
.rel-footer {
    margin-top: 3rem;
    padding-top: 1.5rem;
    border-top: 2px solid #1a252f;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .8rem;
    color: #555;
    page-break-inside: avoid;
}
.rel-assinatura {
    text-align: right;
}
.rel-assinatura-line {
    display: inline-block;
    width: 200px;
    border-top: 1px solid #333;
    padding-top: .3rem;
    font-size: .75rem;
    color: #777;
}
</style>
</body>
</html>
