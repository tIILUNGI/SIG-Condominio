<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$id_morador  = intval($_SESSION['id']);
$nome_morador = htmlspecialchars($_SESSION['nome'] ?? 'Morador');
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nosso Zimbo — Portal do Morador</title>
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="../js/theme-manager.js"></script>
  <script>
    const savedTheme = localStorage.getItem('nz-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
  </script>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
    <div>
      <p class="brand-name">Nosso Zimbo</p>
      <p class="brand-sub">Portal do Morador</p>
    </div>
  </div>
  <nav class="sidebar-nav">
    <p class="nav-section">Funcionalidades</p>
    <button class="nav-item active" onclick="switchTab('dashboard', this)">
      <i class="fa-solid fa-gauge-high"></i><span>Visão Geral</span>
    </button>
    <button class="nav-item" onclick="switchTab('pagamentos', this)">
      <i class="fa-solid fa-money-bill-wave"></i><span>Fazer Pagamentos</span>
    </button>
    <button class="nav-item" onclick="switchTab('historico', this)">
      <i class="fa-solid fa-clock-rotate-left"></i><span>Meu Histórico</span>
    </button>
    <button class="nav-item" onclick="switchTab('visitas', this)">
      <i class="fa-solid fa-user-plus"></i><span>Agendar Visita</span>
    </button>
    <button class="nav-item" onclick="switchTab('agendamento', this)">
      <i class="fa-solid fa-calendar-check"></i><span>Agendar Área Comum</span>
    </button>
    <button class="nav-item" onclick="switchTab('vizinhos', this)">
      <i class="fa-solid fa-users"></i><span>Vizinhos</span>
    </button>
    <p class="nav-section">Relatórios</p>
    <button class="nav-item" onclick="switchTab('relatorio', this)">
      <i class="fa-solid fa-chart-pie"></i><span>Relatório Mensal</span>
    </button>
    <p class="nav-section">Ajustes</p>
    <button class="nav-item" onclick="window.location.href='meu_perfil.php'">
      <i class="fa-solid fa-user-gear"></i><span>Meu Perfil</span>
    </button>
  </nav>
  <div class="sidebar-footer">
    <div class="avatar-admin"><?= strtoupper(substr($nome_morador, 0, 2)) ?></div>
    <div style="flex:1;">
      <p class="af-name"><?= $nome_morador ?></p>
      <p class="af-role">Morador</p>
    </div>
    <a href="../api/logout.php" title="Sair" style="color:var(--text-muted); font-size:1rem;">
      <i class="fa-solid fa-right-from-bracket"></i>
    </a>
  </div>
</aside>

<!-- MAIN -->
<main class="main-content">
  <header class="topbar">
    <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
    <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo</span>
    <div class="topbar-right">
      <div class="clock-display" id="clock-display"></div>
      <div class="avatar-admin" style="width:34px;height:34px;"><?= strtoupper(substr($nome_morador, 0, 2)) ?></div>
    </div>
  </header>

  <!-- ── VISÃO GERAL ── -->
  <section class="tab-section active" id="tab-dashboard">
    <div class="page-header">
      <h1 class="page-title">Olá, <?= $nome_morador ?>! 👋</h1>
      <p class="page-sub" id="dash-date"></p>
    </div>
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-money-bill"></i></div>
        <p class="stat-label">Total Mensalidades</p>
        <p class="stat-value" id="ds-mensalidade">— Kz</p>
        <p class="stat-hint">Valor acumulado</p>
      </div>
      <div class="stat-card red">
        <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="stat-label">Em Dívida</p>
        <p class="stat-value" id="ds-divida">— Kz</p>
        <p class="stat-hint">Mensalidades pendentes</p>
      </div>
      <div class="stat-card green">
        <div class="stat-icon"><i class="fa-solid fa-check"></i></div>
        <p class="stat-label">Meses Pagos</p>
        <p class="stat-value" id="ds-meses-pagos">—</p>
        <p class="stat-hint">Total de meses pagos</p>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon"><i class="fa-solid fa-house"></i></div>
        <p class="stat-label">Apartamento</p>
        <p class="stat-value" id="ds-apartamento">—</p>
        <p class="stat-hint">Residência actual</p>
      </div>
    </div>
  </section>

  <!-- ── RESUMO FINANCEIRO (INFORMATIVO) ── -->
  <section class="tab-section" id="tab-pagamentos">
    <div class="page-header">
      <h1 class="page-title">💳 Resumo Financeiro</h1>
      <p class="page-sub">Informações sobre taxas, rendas e condições de compra</p>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card">
            <div class="card-head"><p class="card-title">💵 Mensalidades e Taxas</p></div>
            <div class="card-body" style="padding:1.5rem;">
                <table class="data-table">
                    <thead><tr><th>Serviço</th><th>Valor Mensal</th></tr></thead>
                    <tbody>
                        <tr><td>Renda Mensal (V3)</td><td><strong>150.000,00 Kz</strong></td></tr>
                        <tr><td>Quota de Condomínio</td><td><strong>25.000,00 Kz</strong></td></tr>
                        <tr><td>Taxa de Limpeza/Manutenção</td><td><strong>10.000,00 Kz</strong></td></tr>
                        <tr><td>Segurança 24h</td><td><strong>Incluído</strong></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><p class="card-title">🏠 Compra de Residência</p></div>
            <div class="card-body" style="padding:1.5rem;">
                <table class="data-table">
                    <thead><tr><th>Descrição</th><th>Valor / Condição</th></tr></thead>
                    <tbody>
                        <tr><td>Preço Total Casa V3</td><td><strong>45.000.000,00 Kz</strong></td></tr>
                        <tr><td>Entrada Inicial (20%)</td><td><strong>9.000.000,00 Kz</strong></td></tr>
                        <tr><td>Prestação Mensal (15 anos)</td><td><strong>200.000,00 Kz</strong></td></tr>
                        <tr><td>Estado de Entrega</td><td><strong>Chave na mão</strong></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-circle-info"></i> Notas Importantes</p></div>
        <div class="card-body" style="padding:1.5rem; font-size:.9rem; line-height:1.6; color:var(--text-muted);">
            <p>1. Os pagamentos devem ser efectuados até ao dia 5 de cada mês para evitar multas.</p>
            <p>2. A prova de transferência deve ser enviada via portal ou entregue na administração.</p>
            <p>3. Para processos de compra, por favor agende uma reunião com o sector financeiro.</p>
            <p>4. Valores sujeitos a alteração anual conforme assembleia de moradores.</p>
        </div>
    </div>
  </section>

  <!-- ── HISTÓRICO ── -->
  <section class="tab-section" id="tab-historico">
    <div class="page-header">
      <h1 class="page-title">Histórico de Pagamentos</h1>
      <p class="page-sub">Visualize todos os seus pagamentos realizados</p>
    </div>
    <div class="card">
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr><th>Mês/Ref</th><th>Serviço</th><th>Valor</th><th>Data</th><th>Estado</th></tr></thead>
          <tbody id="hist-tbody"></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ── AGENDAR VISITA ── -->
  <section class="tab-section" id="tab-visitas">
    <div class="page-header">
      <h1 class="page-title">Agendar Visita</h1>
      <p class="page-sub">Autorize a entrada de convidados com antecedência</p>
    </div>
    <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:1.5rem;">
      <div class="card">
         <div class="card-head"><p class="card-title">Novo Agendamento</p></div>
         <form id="form-visita" onsubmit="prevenirPadrao(event); enviarAgendamentoVisita();">
            <div class="form-group">
              <label>Nome do Visitante</label>
              <input type="text" id="v-nome" required placeholder="Ex: Carlos Silva">
            </div>
            <div class="form-group">
              <label>Data da Visita</label>
              <input type="date" id="v-data" required>
            </div>
            <div class="form-group">
              <label>Hora Prevista (Opcional)</label>
              <input type="time" id="v-hora">
            </div>
            <button type="submit" class="btn-primary" style="width:100%; margin-top:1rem;">Gerar Autorização</button>
         </form>
      </div>
      <div class="card">
         <div class="card-head"><p class="card-title">Minhas Visitas Planeadas</p></div>
         <div style="overflow-x:auto;">
            <table class="data-table">
              <thead><tr><th>Convidado</th><th>Data</th><th>Estado</th><th>Código</th></tr></thead>
              <tbody id="visitas-tbody"></tbody>
            </table>
         </div>
      </div>
    </div>
  </section>

  <!-- ── AGENDAR ÁREA COMUM ── -->
  <section class="tab-section" id="tab-agendamento">
    <div class="page-header">
      <h1 class="page-title">Agendar Área Comum</h1>
      <p class="page-sub">Reserve espaços como piscina, salão de festas ou churrasqueira</p>
    </div>
    <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:1.5rem;">
      <div class="card">
         <div class="card-head"><p class="card-title">Solicitar Reserva</p></div>
         <form id="form-agendamento" onsubmit="prevenirPadrao(event); enviarAgendamentoArea();">
            <div class="form-group">
              <label>Área Pretendida</label>
              <select id="a-area" required>
                <option value="Pisina">Piscina</option>
                <option value="Salao de Festas">Salão de Festas</option>
                <option value="Churrasqueira">Churrasqueira</option>
                <option value="Campo Jogos">Campo de Jogos</option>
              </select>
            </div>
            <div class="form-group">
              <label>Data</label>
              <input type="date" id="a-data" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
              <div class="form-group">
                <label>Hora Início</label>
                <input type="time" id="a-inicio" required>
              </div>
              <div class="form-group">
                <label>Hora Fim</label>
                <input type="time" id="a-fim" required>
              </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; margin-top:1rem;">Solicitar Reserva</button>
         </form>
      </div>
      <div class="card">
         <div class="card-head"><p class="card-title">Estado das Minhas Reservas</p></div>
         <div style="overflow-x:auto;">
            <table class="data-table">
              <thead><tr><th>Local</th><th>Data</th><th>Horário</th><th>Estado</th></tr></thead>
              <tbody id="agenda-tbody"></tbody>
            </table>
         </div>
      </div>
    </div>
  </section>

  <!-- ── VIZINHOS ── -->
  <section class="tab-section" id="tab-vizinhos">
    <div class="page-header">
      <h1 class="page-title">Vizinhos do Bloco</h1>
      <p class="page-sub">Moradores do mesmo bloco</p>
    </div>
    <div class="card">
      <div class="card-head"><p class="card-title"><i class="fa-solid fa-users"></i> Vizinhos</p></div>
      <table class="data-table">
        <thead><tr><th>Nome</th><th>Apartamento</th><th>Tipologia</th></tr></thead>
        <tbody id="viz-tbody"><tr><td colspan="3" style="text-align:center;padding:2rem;color:var(--text-muted);">A carregar...</td></tr></tbody>
      </table>
    </div>
  </section>

  <!-- ── RELATÓRIO ── -->
  <section class="tab-section" id="tab-relatorio">
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
      <div>
        <h1 class="page-title">Relatório Pessoal</h1>
        <p class="page-sub">Seu histórico financeiro mensal</p>
      </div>
      <div style="display:flex;gap:.75rem;align-items:center;">
        <select id="rel-mes" style="background:var(--dark3);border:1px solid var(--border);color:var(--text);padding:.5rem .75rem;border-radius:8px;font-family:'DM Sans',sans-serif;">
          <option value="1">Janeiro</option><option value="2">Fevereiro</option><option value="3">Março</option>
          <option value="4">Abril</option><option value="5">Maio</option><option value="6">Junho</option>
          <option value="7">Julho</option><option value="8">Agosto</option><option value="9">Setembro</option>
          <option value="10">Outubro</option><option value="11">Novembro</option><option value="12">Dezembro</option>
        </select>
        <select id="rel-ano" style="background:var(--dark3);border:1px solid var(--border);color:var(--text);padding:.5rem .75rem;border-radius:8px;font-family:'DM Sans',sans-serif;">
          <option>2025</option><option selected>2026</option>
        </select>
        <button class="btn-primary" onclick="window.print()"><i class="fa-solid fa-file-pdf"></i> PDF</button>
      </div>
    </div>
    <div id="relatorio-morador"></div>
  </section>
</main>

<!-- TOAST -->
<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toast-msg"></span></div>

<script>
const API_M = '../api/api_morador.php';

function apiFetch(acao) {
  return fetch(`${API_M}?acao=${acao}`).then(r => r.json());
}

window.onload = () => {
  clock(); setInterval(clock, 1000);
  const now = new Date();
  document.getElementById('dash-date').textContent =
    now.toLocaleDateString('pt-AO', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
  document.getElementById('rel-mes').value = now.getMonth() + 1;

  carregarResumo();
  carregarMensalidades();
  carregarHistorico();
  carregarVizinhos();
};

async function loadVisitas() {
    const res = await fetch('../api/api_morador.php?acao=visitas');
    const json = await res.json();
    if (json.sucesso) {
        document.getElementById('visitas-tbody').innerHTML = json.dados.map(v => `
            <tr>
                <td>${v.nome_visitante}</td>
                <td>${v.data_prevista} ${v.hora_prevista || ''}</td>
                <td><span class="badge ${v.estado}">${v.estado}</span></td>
                <td><strong style="color:var(--gold)">${v.codigo_acesso || '-'}</strong></td>
            </tr>
        `).join('') || '<tr><td colspan="4" style="text-align:center;">Nenhuma visita agendada.</td></tr>';
    }
}

async function enviarAgendamentoVisita() {
    const fd = new FormData();
    fd.append('nome', document.getElementById('v-nome').value);
    fd.append('data', document.getElementById('v-data').value);
    fd.append('hora', document.getElementById('v-hora').value);
    
    const res = await fetch('../api/api_morador.php?acao=novo_agendamento_visita', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.sucesso) {
        alert('Visita agendada com sucesso!');
        document.getElementById('form-visita').reset();
        loadVisitas();
    } else {
        alert('Erro: ' + json.erro);
    }
}

async function loadAgendamentosArea() {
    const res = await fetch('../api/api_morador.php?acao=agendamentos_area');
    const json = await res.json();
    if (json.sucesso) {
        document.getElementById('agenda-tbody').innerHTML = json.dados.map(a => `
            <tr>
                <td>${a.area_comum}</td>
                <td>${a.data_evento}</td>
                <td>${a.hora_inicio.substring(0,5)} - ${a.hora_fim.substring(0,5)}</td>
                <td><span class="badge ${a.estado}">${a.estado}</span></td>
            </tr>
        `).join('') || '<tr><td colspan="4" style="text-align:center;">Nenhum agendamento realizado.</td></tr>';
    }
}

async function enviarAgendamentoArea() {
    const fd = new FormData();
    fd.append('area', document.getElementById('a-area').value);
    fd.append('data', document.getElementById('a-data').value);
    fd.append('inicio', document.getElementById('a-inicio').value);
    fd.append('fim', document.getElementById('a-fim').value);
    
    const res = await fetch('../api/api_morador.php?acao=novo_agendamento_area', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.sucesso) {
        alert('Solicitação de reserva enviada! Aguarde a confirmação da administração.');
        document.getElementById('form-agendamento').reset();
        loadAgendamentosArea();
    } else {
        alert('Erro: ' + json.erro);
    }
}

function switchTab(id, btn) {
    document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    if (btn) btn.classList.add('active');
    
    if (id === 'historico') carregarHistorico();
    if (id === 'visitas') loadVisitas();
    if (id === 'agendamento') loadAgendamentosArea();
    if (id === 'vizinhos') carregarVizinhos();
}

function prevenirPadrao(e) { e.preventDefault(); }
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }
function clock() { document.getElementById('clock-display').textContent = new Date().toLocaleTimeString('pt-AO'); }

// Resumo financeiro
function carregarResumo() {
  apiFetch('resumo_financeiro').then(d => {
    if (!d.sucesso) return;
    const r = d.dados;
    document.getElementById('ds-mensalidade').textContent = fmt(r.pendente) + ' Kz';
    document.getElementById('ds-divida').textContent = fmt(r.pendente) + ' Kz';
    document.getElementById('ds-meses-pagos').textContent = r.meses_pagos;
  }).catch(() => {});

  apiFetch('perfil').then(d => {
    if (!d.sucesso || !d.dados) return;
    document.getElementById('ds-apartamento').textContent = d.dados.apartamento || '—';
  }).catch(() => {});
}

// Mensalidades pendentes
const meses = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

function carregarMensalidades() {
  apiFetch('mensalidades').then(d => {
    const tb = document.getElementById('mens-tbody');
    if (!d.sucesso || !d.dados.length) {
      tb.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem mensalidades registadas</td></tr>';
      return;
    }
    const bc = { pago:'badge-ok', pendente:'badge-warn', atrasado:'badge-err', dispensado:'badge-info' };
    tb.innerHTML = d.dados.map(m => `
      <tr>
        <td><input type="radio" name="sel-mens" value="${m.id}" data-valor="${m.valor}" onchange="selecionarMens(this)"></td>
        <td>${m.servico}</td>
        <td>${meses[m.mes]||m.mes}/${m.ano}</td>
        <td>${fmt(m.valor)} Kz</td>
        <td>${m.vencimento}</td>
        <td><span class="${bc[m.estado]||'badge-info'}">${m.estado}</span></td>
        <td>${m.estado==='pendente' ? `<button class="btn-primary btn-sm" onclick="pagarDirecto(${m.id}, ${m.valor})"><i class="fa-solid fa-credit-card"></i> Pagar</button>` : '—'}</td>
      </tr>`).join('');
  }).catch(() => {});
}

function selecionarMens(radio) {
  document.getElementById('pag-mens-id').value = radio.value;
  document.getElementById('pag-valor').value = radio.dataset.valor;
  document.getElementById('form-pag-card').style.display = '';
}

function pagarDirecto(id, valor) {
  document.getElementById('pag-mens-id').value = id;
  document.getElementById('pag-valor').value = valor;
  document.getElementById('form-pag-card').style.display = '';
  switchTab('pagamentos');
  document.getElementById('form-pag-card').scrollIntoView({ behavior:'smooth' });
}

// Histórico
function carregarHistorico() {
  apiFetch('historico_pagamentos').then(d => {
    const tb = document.getElementById('hist-tbody');
    if (!d.sucesso || !d.dados.length) {
      tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem pagamentos registados</td></tr>';
      return;
    }
    const bc = { confirmado:'badge-ok', pendente:'badge-warn', rejeitado:'badge-err' };
    tb.innerHTML = d.dados.map(p => `
      <tr>
        <td>${p.servico}</td>
        <td>${meses[p.mes]||p.mes}/${p.ano}</td>
        <td>${fmt(p.valor_pago)} Kz</td>
        <td>${p.metodo}</td>
        <td>${new Date(p.data_pagamento).toLocaleDateString('pt-AO')}</td>
        <td><span class="${bc[p.estado]||'badge-info'}">${p.estado}</span></td>
      </tr>`).join('');
  }).catch(() => {});
}

// Vizinhos
function carregarVizinhos() {
  apiFetch('vizinhos').then(d => {
    const tb = document.getElementById('viz-tbody');
    if (!d.sucesso || !d.dados.length) {
      tb.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem vizinhos encontrados</td></tr>';
      return;
    }
    tb.innerHTML = d.dados.map(v => `<tr><td>${v.nome}</td><td>${v.apartamento}</td><td>${v.tipologia}</td></tr>`).join('');
  }).catch(() => {});
}

// Helpers
function fmt(n) { return new Intl.NumberFormat('pt-AO').format(n || 0); }
function showToast(msg, err) {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast' + (err ? ' error' : '') + ' show';
  setTimeout(() => t.classList.remove('show'), 3500);
}

// Verificar mensagem de redirecionamento
const params = new URLSearchParams(window.location.search);
if (params.get('ok') === 'pagamento_registado') showToast('Pagamento submetido com sucesso!');
if (params.get('erro')) showToast('Erro: ' + params.get('erro'), true);

// Badges inline
const style = document.createElement('style');
style.textContent = `
  .badge-ok   { background:rgba(76,175,125,.15); color:#4caf7d; border:1px solid rgba(76,175,125,.3); padding:2px 8px; border-radius:6px; font-size:.78rem; font-weight:600; }
  .badge-warn { background:rgba(255,167,38,.15);  color:#ffa726; border:1px solid rgba(255,167,38,.3); padding:2px 8px; border-radius:6px; font-size:.78rem; font-weight:600; }
  .badge-err  { background:rgba(224,82,82,.15);   color:#e05252; border:1px solid rgba(224,82,82,.3);  padding:2px 8px; border-radius:6px; font-size:.78rem; font-weight:600; }
  .badge-info { background:rgba(100,149,237,.15); color:#6495ed; border:1px solid rgba(100,149,237,.3);padding:2px 8px; border-radius:6px; font-size:.78rem; font-weight:600; }
  .btn-sm { padding:.3rem .65rem; font-size:.78rem; }
  #form-pag-card { display:none; }
`;
document.head.appendChild(style);
</script>
</body>
</html>
