<?php
session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    header("Location: ../login.html?erro=acesso");
    exit;
}
$admin_nome = $_SESSION['nome'] ?? 'Admin';
$admin_id   = $_SESSION['id']  ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prospectos & Registo Presencial — Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="../js/theme-manager.js"></script>
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .filters-bar { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .filter-chip {
            padding: .5rem 1rem;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            background: var(--surface);
            cursor: pointer;
            font-size: .82rem;
            font-weight: 600;
            transition: all .2s;
        }
        .filter-chip:hover, .filter-chip.active {
            border-color: var(--primary);
            background: var(--primary-light, rgba(74,111,165,.08));
            color: var(--primary);
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .75rem 1rem; text-align: left; font-size: .85rem; border-bottom: 1px solid var(--border); }
        th { color: var(--text-muted); font-weight: 700; font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; }
        tr:hover td { background: var(--bg); }
        .badge {
            display: inline-block;
            padding: .25rem .65rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .badge-pending { background: rgba(240,165,0,.12); color: #c78500; }
        .badge-validated { background: rgba(52,152,219,.12); color: #2980b9; }
        .badge-assigned { background: rgba(39,174,96,.12); color: #27ae60; }
        .badge-approved { background: rgba(39,174,96,.12); color: #27ae60; }
        .badge-rejected { background: rgba(231,76,60,.12); color: #c0392b; }
        .btn-sm {
            padding: .4rem .8rem;
            border-radius: 8px;
            font-size: .75rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            margin: .15rem;
            transition: all .15s;
        }
        .btn-validate { background: #3498db; color: #fff; }
        .btn-validate:hover { background: #2980b9; }
        .btn-assign { background: #27ae60; color: #fff; }
        .btn-assign:hover { background: #219a52; }
        .btn-reject { background: #e74c3c; color: #fff; }
        .btn-reject:hover { background: #c0392b; }
        .btn-approve { background: #f0a500; color: #fff; }
        .btn-approve:hover { background: #d49000; }
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.55);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--surface);
            border-radius: 18px;
            padding: 2rem 1.75rem;
            max-width: 520px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }
        .modal-title { margin: 0 0 1rem; font-size: 1.1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: .8rem; font-weight: 700; margin-bottom: .3rem; color: var(--text); }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: .6rem .8rem;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: var(--bg);
            color: var(--text);
            font-size: .88rem;
            font-family: inherit;
        }
        .modal-footer { display: flex; gap: .6rem; justify-content: flex-end; margin-top: 1.25rem; }
        .empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }
        .empty-state i { font-size: 2.5rem; opacity: .15; display: block; margin-bottom: .75rem; }
    </style>
</head>
<body>
<?php include('sidebar_admin.php'); ?>

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="topbar-title"><i class="fa-solid fa-user-plus"></i> Prospectos & Registo Presencial</span>
        <div class="topbar-right">
            <button class="btn-primary" onclick="loadProspectos()" style="font-size:.8rem;">
                <i class="fa-solid fa-arrows-rotate"></i> Actualizar
            </button>
        </div>
    </header>

    <div style="padding: 24px;">
        <div class="page-header">
            <div>
                <h1 class="page-title">Gestão de Prospectos</h1>
                <p class="page-sub">Valide pagamentos presenciais e atribua casas a novos moradores.</p>
            </div>
        </div>

        <div class="filters-bar">
            <div class="filter-chip active" data-filter="todos" onclick="setFilter('todos', this)">Todos</div>
            <div class="filter-chip" data-filter="AguardandoValidacaoPagamento" onclick="setFilter('AguardandoValidacaoPagamento', this)">Aguardando Pagamento</div>
            <div class="filter-chip" data-filter="AguardandoAtribuicaoCasa" onclick="setFilter('AguardandoAtribuicaoCasa', this)">Pagamento OK — Atribuir Casa</div>
            <div class="filter-chip" data-filter="Aprovado" onclick="setFilter('Aprovado', this)">Aprovados</div>
            <div class="filter-chip" data-filter="Pendente" onclick="setFilter('Pendente', this)">Pendentes</div>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <table id="prospectos-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email / Telefone</th>
                        <th>Interesse</th>
                        <th>Preferências</th>
                        <th>Data Registo</th>
                        <th>Estado</th>
                        <th style="text-align:right;">Acções</th>
                    </tr>
                </thead>
                <tbody id="prospectos-body">
                    <tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">
                        <i class="fa-solid fa-spinner fa-spin"></i> Carregando prospectos...
                    </td></tr>
                </tbody>
            </table>
            <div id="empty-state" class="empty-state" style="display:none;">
                <i class="fa-solid fa-user-plus"></i>
                <p>Nenhum prospecto encontrado para este filtro.</p>
            </div>
        </div>
    </div>
</main>

<!-- Modal: Validar Pagamento / Aprovar -->
<div class="modal-overlay" id="modal-validar">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('modal-validar')"><i class="fa-solid fa-xmark"></i></button>
        <h3 class="modal-title"><i class="fa-solid fa-circle-check" style="color:#27ae60;"></i> Validar Pagamento Presencial</h3>
        <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:1rem;" id="modal-validar-info"></p>
        <input type="hidden" id="validar-id-morador">
        <div class="form-group">
            <label>Notas internas (opcional)</label>
            <textarea id="validar-notas" rows="3" placeholder="Ex: Pagamento confirmado em Kz 140.000 via Multicaixa"></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modal-validar')">Cancelar</button>
            <button class="btn-assign" onclick="processarProspecto('validar_pagamento')"><i class="fa-solid fa-check"></i> Confirmar Pagamento</button>
        </div>
    </div>
</div>

<!-- Modal: Aprovar sem pagamento (admin directo) -->
<div class="modal-overlay" id="modal-aprovar">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('modal-aprovar')"><i class="fa-solid fa-xmark"></i></button>
        <h3 class="modal-title"><i class="fa-solid fa-stamp" style="color:#f0a500;"></i> Aprovar Registo</h3>
        <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:1rem;" id="modal-aprovar-info"></p>
        <input type="hidden" id="aprovar-id-morador">
        <div class="form-group">
            <label>Notas (opcional)</label>
            <textarea id="aprovar-notas" rows="3" placeholder="Motivo da aprovação directa..."></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modal-aprovar')">Cancelar</button>
            <button class="btn-approve" onclick="processarProspecto('aprovar')"><i class="fa-solid fa-check"></i> Aprovar</button>
        </div>
    </div>
</div>

<!-- Modal: Atribuir Casa -->
<div class="modal-overlay" id="modal-atribuir">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('modal-atribuir')"><i class="fa-solid fa-xmark"></i></button>
        <h3 class="modal-title"><i class="fa-solid fa-house" style="color:#27ae60;"></i> Atribuir Casa</h3>
        <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:1rem;" id="modal-atribuir-info"></p>
        <input type="hidden" id="atribuir-id-morador">
        <div class="form-group">
            <label>Apartamento Disponível</label>
            <select id="atribuir-apartamento">
                <option value="">— Seleccione —</option>
            </select>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modal-atribuir')">Cancelar</button>
            <button class="btn-assign" onclick="processarProspecto('atribuir_casa')"><i class="fa-solid fa-key"></i> Atribuir Casa</button>
        </div>
    </div>
</div>

<!-- Modal: Rejeitar -->
<div class="modal-overlay" id="modal-rejeitar">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('modal-rejeitar')"><i class="fa-solid fa-xmark"></i></button>
        <h3 class="modal-title"><i class="fa-solid fa-circle-xmark" style="color:#e74c3c;"></i> Rejeitar Registo</h3>
        <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:1rem;" id="modal-rejeitar-info"></p>
        <input type="hidden" id="rejeitar-id-morador">
        <div class="form-group">
            <label>Motivo da rejeição</label>
            <textarea id="rejeitar-notas" rows="3" placeholder="Ex: Documentação incompleta, não cumpre requisitos..."></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('modal-rejeitar')">Cancelar</button>
            <button class="btn-reject" onclick="processarProspecto('rejeitar')"><i class="fa-solid fa-ban"></i> Rejeitar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API = '../api/api_dashboard.php';
let currentFilter = 'todos';
let allProspectos = [];

function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }

function showToast(msg, isErr = false) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show' + (isErr ? ' error' : '');
    setTimeout(() => t.classList.remove('show'), 3500);
}

function setFilter(f, el) {
    currentFilter = f;
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');
    renderProspectos();
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function escHtml(str) {
    if (!str) return '';
    return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function statusBadge(estado) {
    const map = {
        'AguardandoValidacaoPagamento': { cls: 'badge-pending', label: 'Aguarda Pagamento' },
        'AguardandoAtribuicaoCasa':    { cls: 'badge-validated', label: 'Pagamento OK' },
        'Aprovado':                    { cls: 'badge-approved', label: 'Aprovado' },
        'Pendente':                    { cls: 'badge-pending', label: 'Pendente' },
        'Activo':                      { cls: 'badge-assigned', label: 'Activo' },
        'Suspenso':                    { cls: 'badge-rejected', label: 'Suspenso' },
        'Inactivo':                    { cls: 'badge-rejected', label: 'Inactivo' },
    };
    const s = map[estado] || { cls: 'badge-pending', label: estado };
    return `<span class="badge ${s.cls}">${s.label}</span>`;
}

function renderProspectos() {
    const tbody = document.getElementById('prospectos-body');
    const empty = document.getElementById('empty-state');
    const filtered = currentFilter === 'todos' ? allProspectos : allProspectos.filter(p => p.estado_conta === currentFilter || p.estado_processo === currentFilter);

    if (filtered.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
        return;
    }
    empty.style.display = 'none';

    tbody.innerHTML = filtered.map(p => {
        const pref = [p.preferencia_bloco, p.preferencia_tipologia, p.preferencia_andar].filter(Boolean).join(', ') || '—';
        const data = p.criado_em ? new Date(p.criado_em).toLocaleDateString('pt-AO') : '—';
        let acoes = '';
        if (p.estado_conta === 'AguardandoValidacaoPagamento') {
            acoes += `<button class="btn-sm btn-validate" onclick="openValidarModal(${p.id}, '${escHtml(p.nome)}')"><i class="fa-solid fa-check-circle"></i> Validar Pagamento</button>`;
            acoes += `<button class="btn-sm btn-approve" onclick="openAprovarModal(${p.id}, '${escHtml(p.nome)}')"><i class="fa-solid fa-stamp"></i> Aprovar</button>`;
            acoes += `<button class="btn-sm btn-reject" onclick="openRejeitarModal(${p.id}, '${escHtml(p.nome)}')"><i class="fa-solid fa-ban"></i></button>`;
        } elseif (p.estado_conta === 'AguardandoAtribuicaoCasa') {
            acoes += `<button class="btn-sm btn-assign" onclick="openAtribuirModal(${p.id}, '${escHtml(p.nome)}')"><i class="fa-solid fa-key"></i> Atribuir Casa</button>`;
            acoes += `<button class="btn-sm btn-reject" onclick="openRejeitarModal(${p.id}, '${escHtml(p.nome)}')"><i class="fa-solid fa-ban"></i></button>`;
        } elseif (p.estado_conta === 'Pendente') {
            acoes += `<button class="btn-sm btn-validate" onclick="openValidarModal(${p.id}, '${escHtml(p.nome)}')"><i class="fa-solid fa-check-circle"></i> Validar</button>`;
            acoes += `<button class="btn-sm btn-reject" onclick="openRejeitarModal(${p.id}, '${escHtml(p.nome)}')"><i class="fa-solid fa-ban"></i></button>`;
        }
        return `<tr>
            <td><strong>${escHtml(p.nome)}</strong></td>
            <td>
                <div style="font-size:.82rem;">${escHtml(p.email)}</div>
                <div style="font-size:.75rem; color:var(--text-muted);">${escHtml(p.telefone)}</div>
            </td>
            <td>${escHtml(p.tipo_interesse || '—')}</td>
            <td style="font-size:.8rem;">${escHtml(pref)}</td>
            <td>${data}</td>
            <td>${statusBadge(p.estado_conta)}</td>
            <td style="text-align:right;">${acoes}</td>
        </tr>`;
    }).join('');
}

function openValidarModal(id, nome) {
    document.getElementById('validar-id-morador').value = id;
    document.getElementById('modal-validar-info').textContent = 'Confirmar pagamento presencial de: ' + nome;
    document.getElementById('validar-notas').value = '';
    openModal('modal-validar');
}
function openAprovarModal(id, nome) {
    document.getElementById('aprovar-id-morador').value = id;
    document.getElementById('modal-aprovar-info').textContent = 'Aprovar directamente para espera de atribuição de casa: ' + nome;
    document.getElementById('aprovar-notas').value = '';
    openModal('modal-aprovar');
}
function openAtribuirModal(id, nome) {
    document.getElementById('atribuir-id-morador').value = id;
    document.getElementById('modal-atribuir-info').textContent = 'Atribuir casa a: ' + nome;
    document.getElementById('atribuir-apartamento').innerHTML = '<option value="">— A carregar —</option>';
    openModal('modal-atribuir');
    carregarApartamentosDisponiveis();
}
function openRejeitarModal(id, nome) {
    document.getElementById('rejeitar-id-morador').value = id;
    document.getElementById('modal-rejeitar-info').textContent = 'Rejeitar registo de: ' + nome;
    document.getElementById('rejeitar-notas').value = '';
    openModal('modal-rejeitar');
}

async function carregarApartamentosDisponiveis() {
    try {
        const res = await fetch(API + '?acao=casas_disponiveis');
        const data = await res.json();
        const sel = document.getElementById('atribuir-apartamento');
        if (data.sucesso && data.dados.length) {
            sel.innerHTML = '<option value="">— Seleccione —</option>' +
                data.dados.map(a => `<option value="${a.id}">${escHtml(a.label)}</option>`).join('');
        } else {
            sel.innerHTML = '<option value="">— Nenhum apartamento disponível —</option>';
        }
    } catch (e) {
        document.getElementById('atribuir-apartamento').innerHTML = '<option value="">— Erro ao carregar —</option>';
    }
}

async function processarProspecto(acao) {
    let id, notas, idApt = '';
    if (acao === 'validar_pagamento') {
        id = document.getElementById('validar-id-morador').value;
        notas = document.getElementById('validar-notas').value;
    } else if (acao === 'aprovar') {
        id = document.getElementById('aprovar-id-morador').value;
        notas = document.getElementById('aprovar-notas').value;
    } else if (acao === 'atribuir_casa') {
        id = document.getElementById('atribuir-id-morador').value;
        idApt = document.getElementById('atribuir-apartamento').value;
        notas = '';
        if (!idApt) { showToast('Seleccione um apartamento', true); return; }
    } else if (acao === 'rejeitar') {
        id = document.getElementById('rejeitar-id-morador').value;
        notas = document.getElementById('rejeitar-notas').value;
    }

    const fd = new FormData();
    fd.append('acao', acao);
    fd.append('id_morador', id);
    if (notas) fd.append('notas', notas);
    if (idApt) fd.append('id_apartamento', idApt);

    try {
        const res = await fetch(API + '?acao=processar_prospecto', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.sucesso) {
            showToast('Operação realizada com sucesso!');
            closeModal('modal-validar');
            closeModal('modal-aprovar');
            closeModal('modal-atribuir');
            closeModal('modal-rejeitar');
            loadProspectos();
        } else {
            showToast(data.erro || 'Erro ao processar', true);
        }
    } catch (e) {
        showToast('Erro de rede', true);
    }
}

async function loadProspectos() {
    try {
        const res = await fetch(API + '?acao=listar_prospectos');
        const data = await res.json();
        if (data.sucesso) {
            allProspectos = data.dados;
            renderProspectos();
        }
    } catch (e) {
        showToast('Erro ao carregar prospectos', true);
    }
}

window.onload = () => { loadProspectos(); };
</script>
</body>
</html>
