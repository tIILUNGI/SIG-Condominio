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
    <title>Pagamentos Moradores - Nosso Zimbo</title>
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
        <button class="nav-item active" onclick="window.location.href='admin_pagamentos_moradores.php'">
            <i class="fa-solid fa-id-badge"></i><span>Pagamentos Moradores</span>
        </button>
        <button class="nav-item" onclick="window.location.href='admin-comunicacao.php'">
            <i class="fa-solid fa-comments"></i><span>Comunicação</span>
        </button>
        <p class="nav-section">Relatórios</p>
        <button class="nav-item" onclick="window.location.href='admin_relatorio_mensal.php'">
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
        <span class="topbar-title"><i class="fa-solid fa-id-badge"></i> Pagamentos Moradores</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <a href="relatorio_pagamentos.php" target="_blank" class="btn-secondary" style="font-size:.8rem; text-decoration:none; padding:.4rem .8rem; border-radius:8px; display:inline-flex; align-items:center; gap:.4rem;"><i class="fa-solid fa-file-lines"></i> Relatório</a>
        </div>
    </header>

    <div style="padding: 2.5rem 3rem;">
        <div class="page-header">
            <h1 class="page-title">Pagamentos de Moradores</h1>
            <p class="page-sub">Valide os comprovativos submetidos pelos residentes</p>
        </div>

        <div class="card">
            <div class="card-head"><p class="card-title"><i class="fa-solid fa-receipt"></i> Histórico de Transações</p></div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Morador</th>
                            <th>Casa</th>
                            <th>Serviço</th>
                            <th>Valor</th>
                            <th>Método</th>
                            <th>Data</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="pagamentos-tbody">
                        <tr><td colspan="8" style="text-align:center;">Carregando transações...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top:1.5rem; padding:0; overflow:hidden;">
            <div class="card-head"><p class="card-title"><i class="fa-solid fa-file-lines"></i> Validar Pagamento Presencial</p></div>
            <div style="padding:1.5rem;">
                <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:1rem;">Insira a referência do pagamento presencial para validar e emitir recibo.</p>
                <div style="display:flex; gap:.6rem; flex-wrap:wrap;">
                    <input type="text" id="ref-pres-input" placeholder="Ex: PRES260701A1B2C3D3" style="flex:1; min-width:260px; padding:.65rem 1rem; border-radius:10px; border:1.5px solid var(--border); background:var(--bg); color:var(--text);" />
                    <button class="btn-primary" onclick="validarPresencial()"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
                </div>
                <div id="pres-resultado" style="margin-top:1rem;"></div>
            </div>
        </div>
    </div>
</main>

<div class="toast" id="toast"></div>

<!-- MODAL COMPROVATIVO -->
<div class="modal-overlay" id="modal-pay">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('modal-pay')"><i class="fa-solid fa-xmark"></i></button>
        <h3 class="modal-title">Detalhes do Pagamento</h3>
        <div id="pay-details" style="margin-top:1.5rem;"></div>
        <div class="modal-footer">
            <button class="btn-danger" onclick="validarPay('rejeitado')">Rejeitar</button>
            <button class="btn-success" onclick="validarPay('confirmado')">Aprovar Pagamento</button>
        </div>
    </div>
</div>

<script>
const API_URL = '../api/api_dashboard.php';
let currentPayId = null;

function showToast(msg, isError = false) {
    const t = document.getElementById('toast');
    t.className = `toast ${isError ? 'error' : ''} show`;
    t.innerHTML = `<i class="fa-solid fa-${isError ? 'circle-xmark' : 'circle-check'}"></i> ${msg}`;
    setTimeout(() => t.classList.remove('show'), 3000);
}

async function carregarPagamentos() {
    try {
        const res = await fetch(`${API_URL}?acao=pagamentos`);
        const data = await res.json();
        const tbody = document.getElementById('pagamentos-tbody');
        tbody.innerHTML = data.dados.map(p => `
            <tr>
                <td><strong>${p.morador}</strong></td>
                <td><span class="house-tag">${p.apartamento}</span></td>
                <td>Mensalidade</td>
                <td><strong>${new Intl.NumberFormat('pt-AO').format(p.valor_pago)} Kz</strong></td>
                <td>${p.metodo}</td>
                <td>${new Date(p.data_pagamento).toLocaleDateString()}</td>
                <td><span class="badge ${p.estado === 'confirmado' ? 'pago' : 'pendente'}">${p.estado}</span></td>
                <td>
                    <button class="btn-secondary btn-sm" onclick="verDetalhes(${JSON.stringify(p).replace(/"/g, '&quot;')})"><i class="fa-solid fa-eye"></i> Ver</button>
                    ${p.estado === 'confirmado' ? `<a href="recibo.php?id=${p.id}" target="_blank" class="btn-secondary btn-sm" style="text-decoration:none;"><i class="fa-solid fa-print"></i> Recibo</a>` : ''}
                </td>
            </tr>
        `).join('');
    } catch(e) {}
}

function verDetalhes(p) {
    currentPayId = p.id;
    const box = document.getElementById('pay-details');
    box.innerHTML = `
        <p><strong>Referência:</strong> ${p.referencia || 'N/A'}</p>
        <p style="margin-bottom:1rem;"><strong>Data:</strong> ${new Date(p.data_pagamento).toLocaleString()}</p>
        <div style="background:#eee; border-radius:8px; padding:20px; text-align:center;">
            ${p.recibo_path ? 
                `<img src="../${p.recibo_path}" style="max-width:100%; cursor:pointer;" onclick="window.open(this.src)">` : 
                '<p>Nenhum comprovativo anexado</p>'
            }
        </div>
    `;
    document.getElementById('modal-pay').classList.add('open');
}

async function validarPay(estado) {
    const fd = new FormData();
    fd.append('id', currentPayId);
    fd.append('estado', estado);
    const r = await fetch(`${API_URL}?acao=confirmar_pagamento`, { method:'POST', body:fd });
    if((await r.json()).sucesso) {
        showToast(estado === 'confirmado' ? 'Pagamento Aprovado!' : 'Pagamento Rejeitado');
        closeModal('modal-pay');
        carregarPagamentos();
    }
}

function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }

async function validarPresencial() {
    const ref = document.getElementById('ref-pres-input').value.trim();
    if (!ref) { showToast('Insira a referência', true); return; }
    const res = await fetch(`${API_URL}?acao=buscar_por_referencia&ref=${encodeURIComponent(ref)}`);
    const data = await res.json();
    const el = document.getElementById('pres-resultado');
    if (!data.sucesso || !data.dados) {
        el.innerHTML = `<p style="color:var(--danger);"><i class="fa-solid fa-circle-xmark"></i> ${data.erro || 'Não encontrado'}</p>`;
        return;
    }
    const p = data.dados;
    el.innerHTML = `
        <div style="background:var(--bg); border-radius:14px; padding:1.25rem; border:1px solid var(--border);">
            <div class="recibo-row"><span class="key">Referência</span><span class="val">${p.referencia}</span></div>
            <div class="recibo-row"><span class="key">Morador</span><span class="val">${p.morador}</span></div>
            <div class="recibo-row"><span class="key">Apartamento</span><span class="val">${p.apartamento}</span></div>
            <div class="recibo-row"><span class="key">Serviço</span><span class="val">${p.servico}</span></div>
            <div class="recibo-row"><span class="key">Valor</span><span class="val" style="color:#27ae60;">Kz ${parseFloat(p.valor_pago).toLocaleString('pt-AO', {minimumFractionDigits:2})}</span></div>
            <div class="recibo-row"><span class="key">Estado</span><span class="val">${p.estado}</span></div>
            ${p.estado !== 'confirmado' ? `<button class="btn-validate" onclick="confirmarRef(${p.id})" style="margin-top:1rem;"><i class="fa-solid fa-check-double"></i> Confirmar Pagamento</button>` : '<p style="color:#27ae60; margin-top:1rem;"><i class="fa-solid fa-check-circle"></i> Já confirmado</p>'}
        </div>
    `;
}
async function confirmarRef(idPag) {
    if (!confirm('Confirmar este pagamento presencial?')) return;
    const fd = new FormData();
    fd.append('acao', 'confirmar_pagamento');
    fd.append('id', idPag);
    fd.append('estado', 'confirmado');
    fd.append('notas', 'Validado presencialmente');
    const r = await fetch(`${API_URL}?acao=confirmar_pagamento`, { method:'POST', body:fd });
    const d = await r.json();
    if (d.sucesso) {
        showToast('Pagamento confirmado!');
        document.getElementById('ref-pres-input').value = '';
        document.getElementById('pres-resultado').innerHTML = '';
        carregarPagamentos();
    } else {
        showToast(d.erro || 'Erro', true);
    }
}

window.onload = () => {
    carregarPagamentos();
    setInterval(() => {
        const el = document.getElementById('clock-display');
        if (el) el.textContent = new Date().toLocaleTimeString('pt-AO');
    }, 1000);
};
</script>
</body>
</html>
