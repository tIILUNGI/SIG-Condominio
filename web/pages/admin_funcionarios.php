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
    <title>Cadastro de Funcionários - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <button class="nav-item active" onclick="window.location.href='admin_funcionarios.php'">
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
        <span class="topbar-title">💼 Gestão de Funcionários</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
        </div>
    </header>

    <div style="padding: 2.5rem 3rem;">
        <div class="page-header">
            <h1 class="page-title">Cadastro de Funcionários</h1>
            <p class="page-sub">Gira a sua equipa administrativa e de campo</p>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap: 2rem;">
            <div class="card">
                <div class="card-head"><p class="card-title"><i class="fa-solid fa-user-plus"></i> Novo Funcionário</p></div>
                <div class="card-body">
                    <form id="form-func" class="form-grid">
                        <div class="form-group full">
                            <label>Nome Completo *</label>
                            <input type="text" id="f-nome" required pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" title="O nome deve conter apenas letras e espaços." />
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" id="f-email" required />
                        </div>
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" id="f-telefone" placeholder="9XX-XXX-XXX" pattern="9[0-9]{2}-[0-9]{3}-[0-9]{3}" title="Formato esperado: 9xx-xxx-xxx" oninput="maskPhone(this)" />
                        </div>
                        <div class="form-group">
                            <label>Senha *</label>
                            <input type="password" id="f-senha" value="123456" />
                        </div>
                        <div class="form-group">
                            <label>Função *</label>
                            <select id="f-funcao">
                                <option value="Funcionário">Funcionário</option>
                                <option value="Administrador">Administrador</option>
                                <option value="Segurança">Segurança</option>
                                <option value="Limpeza">Limpeza</option>
                            </select>
                        </div>
                        <div class="form-group full">
                           <button type="submit" class="btn-primary" style="width:100%; margin-top:1rem;">Registar Funcionário</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><p class="card-title"><i class="fa-solid fa-list-ul"></i> Lista de Funcionários</p></div>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Função</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="funcionarios-tbody">
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

const API_URL = 'api/api_dashboard.php';

function showToast(msg, isError = false) {
    const t = document.getElementById('toast');
    t.className = `toast ${isError ? 'error' : ''} show`;
    t.innerHTML = `<i class="fa-solid fa-${isError ? 'circle-xmark' : 'circle-check'}"></i> ${msg}`;
    setTimeout(() => t.classList.remove('show'), 3000);
}

async function loadFuncs() {
    try {
        const res = await fetch(`../api/api_dashboard.php?acao=admins`);
        const data = await res.json();
        const tbody = document.getElementById('funcionarios-tbody');
        tbody.innerHTML = data.dados.map(f => `
            <tr>
                <td><strong>${f.nome}</strong></td>
                <td>${f.funcao}</td>
                <td>${f.email}</td>
                <td><span class="badge ${f.activo == 1 ? 'pago' : 'vencido'}">${f.activo == 1 ? 'Activo' : 'Inactivo'}</span></td>
                <td>
                    <button class="btn-danger btn-sm" onclick="eliminar(${f.id})"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
    } catch(e) {}
}

document.getElementById('form-func').onsubmit = async (e) => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('nome', document.getElementById('f-nome').value);
    fd.append('email', document.getElementById('f-email').value);
    fd.append('senha', document.getElementById('f-senha').value);
    fd.append('funcao', document.getElementById('f-funcao').value);
    fd.append('telefone', document.getElementById('f-telefone').value);
    
    const r = await fetch(`../api/api_dashboard.php?acao=cadastrar_admin`, { method:'POST', body:fd });
    const d = await r.json();
    if(d.sucesso) {
        showToast('Funcionário adicionado!');
        e.target.reset();
        loadFuncs();
    } else showToast(d.erro, true);
};

async function eliminar(id) {
    if(!confirm('Eliminar funcionário?')) return;
    const fd = new FormData(); fd.append('id', id);
    const r = await fetch(`../api/api_dashboard.php?acao=eliminar_admin`, { method:'POST', body:fd });
    if((await r.json()).sucesso) { showToast('Removido!'); loadFuncs(); }
}

function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }

window.onload = () => {
    loadFuncs();
    setInterval(() => {
        const el = document.getElementById('clock-display');
        if (el) el.textContent = new Date().toLocaleTimeString('pt-AO');
    }, 1000);
};
</script>
</body>
</html>
