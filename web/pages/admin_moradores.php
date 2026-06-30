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
    <title>Cadastro de Moradores - Nosso Zimbo</title>
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
        <button class="nav-item active" onclick="window.location.href='admin_moradores.php'">
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
        <span class="topbar-title">🏠 Gestão de Moradores</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
        </div>
    </header>

    <div style="padding: 2.5rem 3rem;">
        <div class="page-header">
            <h1 class="page-title">Cadastro de Moradores</h1>
            <p class="page-sub">Controle os acessos e dados de todos os residentes</p>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap: 2rem;">
            <div class="card">
                <div class="card-head"><p class="card-title"><i class="fa-solid fa-user-plus"></i> Novo Morador</p></div>
                <div class="card-body">
                    <form id="form-mor" class="form-grid">
                        <div class="form-group full">
                            <label>Nome Completo *</label>
                            <input type="text" id="m-nome" required pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" title="O nome deve conter apenas letras e espaços." />
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" id="m-email" />
                        </div>
                        <div class="form-group">
                            <label>Telefone *</label>
                            <input type="text" id="m-telefone" required placeholder="9XX-XXX-XXX" pattern="9[0-9]{2}-[0-9]{3}-[0-9]{3}" title="Formato esperado: 9xx-xxx-xxx" oninput="maskPhone(this)" />
                        </div>
<div class="form-group">
                             <label>Nº Bilhete (BI) *</label>
                             <input type="text" id="m-numbi" required placeholder="000XXXXXX LA 000" />
                         </div>
                         <div class="form-group">
                             <label>Local Emissão BI</label>
                             <select id="m-locale">
                                 <option value="Luanda">Luanda</option>
                                 <option value="Porto Alegre">Porto Alegre</option>
                                 <option value="Benguela">Benguela</option>
                             </select>
                         </div>
                         <div class="form-group">
                            <label>Senha *</label>
                            <input type="password" id="m-senha" value="123456" />
                        </div>
                        <div class="form-group">
                            <label>Data Nascimento</label>
                            <input type="date" id="m-nascimento" />
                        </div>
                        <div class="form-group full">
                           <button type="submit" class="btn-primary" style="width:100%; margin-top:1rem;">Registar Morador</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><p class="card-title"><i class="fa-solid fa-users"></i> Lista de Moradores</p></div>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Casa</th>
                                <th>Telefone</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="moradores-tbody">
                            <tr><td colspan="5" style="text-align:center;">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="toast" id="toast"></div>

<script>
function maskPhone(i) {
    let v = i.value.replace(/\D/g, "");
    if (v.length > 9) v = v.substring(0, 9);
    let r = "";
    if (v.length > 0) r += v.substring(0, 3);
    if (v.length > 3) r += "-" + v.substring(3, 6);
    if (v.length > 6) r += "-" + v.substring(6, 9);
    i.value = r;
}

const API_URL = '../api/api_dashboard.php';

function showToast(msg, isError = false) {
    const t = document.getElementById('toast');
    t.className = `toast ${isError ? 'error' : ''} show`;
    t.innerHTML = `<i class="fa-solid fa-${isError ? 'circle-xmark' : 'circle-check'}"></i> ${msg}`;
    setTimeout(() => t.classList.remove('show'), 3000);
}

async function loadMoradores() {
    try {
        const res = await fetch(`${API_URL}?acao=moradores`);
        const data = await res.json();
        const tbody = document.getElementById('moradores-tbody');
        tbody.innerHTML = data.dados.map(m => `
            <tr>
                <td><strong>${m.nome}</strong><br><small>${m.email}</small></td>
                <td><span class="house-tag">${m.apartamento || 'Pendente'}</span></td>
                <td>${m.telefone}</td>
                <td><span class="badge ${m.estado_conta === 'Activo' ? 'pago' : 'vencido'}">${m.estado_conta}</span></td>
                <td>
                    <button class="btn-success btn-sm" onclick="vincular(${m.id}, '${m.nome}')"><i class="fa-solid fa-house-user"></i></button>
                    <button class="btn-danger btn-sm" onclick="eliminar(${m.id})"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
    } catch(e) {}
}

document.getElementById('form-mor').onsubmit = async (e) => {
     e.preventDefault();
     const fd = new FormData();
     fd.append('nome', document.getElementById('m-nome').value);
     fd.append('email', document.getElementById('m-email').value);
     fd.append('senha', document.getElementById('m-senha').value);
     fd.append('telefone', document.getElementById('m-telefone').value);
     fd.append('numbi', document.getElementById('m-numbi').value);
     fd.append('nascimento', document.getElementById('m-nascimento').value);
     fd.append('locale', document.getElementById('m-locale').value);
    
    const r = await fetch(`${API_URL}?acao=cadastrar_morador`, { method:'POST', body:fd });
    const d = await r.json();
    if(d.sucesso) { showToast('Morador registado!'); e.target.reset(); loadMoradores(); }
    else showToast(d.erro, true);
};

function vincular(id, nome) {
    const idApt = prompt(`Vincular ${nome} a qual casa (ID)?`);
    if(!idApt) return;
    const fd = new FormData();
    fd.append('id_morador', id);
    fd.append('id_apartamento', idApt);
    fetch(`${API_URL}?acao=processar_morador`, { method:'POST', body:fd })
    .then(r => r.json())
    .then(d => { if(d.sucesso) { showToast('Vinculado!'); loadMoradores(); } });
}

async function eliminar(id) {
    if(!confirm('Eliminar morador?')) return;
    const fd = new FormData(); fd.append('id', id);
    const r = await fetch(`${API_URL}?acao=eliminar_morador`, { method:'POST', body:fd });
    if((await r.json()).sucesso) { showToast('Removido!'); loadMoradores(); }
}

function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }

window.onload = () => {
    loadMoradores();
    setInterval(() => {
        const el = document.getElementById('clock-display');
        if (el) el.textContent = new Date().toLocaleTimeString('pt-AO');
    }, 1000);
};
</script>
</body>
</html>
