<?php
session_start();
// Protecção de acesso — apenas admins
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$nome_admin = htmlspecialchars($_SESSION['nome'] ?? 'Administrador');
$funcao_admin = htmlspecialchars($_SESSION['funcao'] ?? '');

// Dados do condomínio
$cond = null;
if ($conexao) {
    $res = mysqli_query($conexao, "SELECT * FROM condominio LIMIT 1");
    $cond = $res ? mysqli_fetch_assoc($res) : null;
}
$nome_cond = htmlspecialchars($cond['nome'] ?? 'Condomínio Nosso Zimbo');
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $nome_cond ?> — Painel de Administração</title>
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
    <div>
      <p class="brand-name">Nosso Zimbo</p>
      <p class="brand-sub">Painel Administrativo</p>
    </div>
  </div>
  <nav class="sidebar-nav">
    <p class="nav-section">Gestão</p>
    <button class="nav-item active" onclick="switchTab('dashboard', this)">
      <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
    </button>
    <button class="nav-item" onclick="switchTab('pedidos', this)">
      <i class="fa-solid fa-user-tie"></i><span>Funcionários</span>
      <span class="nav-badge" id="badge-pedidos">0</span>
    </button>
    <button class="nav-item" onclick="switchTab('registos', this)">
      <i class="fa-solid fa-users"></i><span>Moradores</span>
      <span class="nav-badge" id="badge-reg">0</span>
    </button>
    <button class="nav-item" onclick="switchTab('casas', this)">
      <i class="fa-solid fa-house-chimney"></i><span>Apartamentos</span>
    </button>
    <p class="nav-section">Finanças</p>
    <button class="nav-item" onclick="switchTab('moradores', this)">
      <i class="fa-solid fa-id-badge"></i><span>Mensalidades</span>
    </button>
    <p class="nav-section">Operações</p>
    <button class="nav-item" onclick="switchTab('visitas', this)">
      <i class="fa-solid fa-user-clock"></i><span>Visitas</span>
    </button>
    <button class="nav-item" onclick="switchTab('reservas', this)">
      <i class="fa-solid fa-calendar-alt"></i><span>Reservas Áreas</span>
    </button>
    <p class="nav-section">Relatórios</p>
    <button class="nav-item" onclick="switchTab('relatorio', this)">
      <i class="fa-solid fa-chart-pie"></i><span>Relatório Mensal</span>
    </button>
  </nav>
  <div class="sidebar-footer">
    <div class="avatar-admin"><?= strtoupper(substr($nome_admin, 0, 2)) ?></div>
    <div style="flex:1;">
      <p class="af-name"><?= $nome_admin ?></p>
      <p class="af-role"><?= $funcao_admin ?></p>
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
    <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> <?= $nome_cond ?> — Admin</span>
    <div class="topbar-right">
      <div class="clock-display" id="clock-display"></div>
      <div class="avatar-admin" style="width:34px;height:34px;"><?= strtoupper(substr($nome_admin, 0, 2)) ?></div>
    </div>
  </header>

  <!-- ── DASHBOARD ── -->
  <section class="tab-section active" id="tab-dashboard">
    <div class="page-header">
      <h1 class="page-title">Dashboard</h1>
      <p class="page-sub" id="dash-date"></p>
    </div>
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
        <p class="stat-label">Total Moradores</p>
        <p class="stat-value" id="ds-total-reg">—</p>
        <p class="stat-hint">Moradores registados</p>
      </div>
      <div class="stat-card green">
        <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
        <p class="stat-label">Receitas do Mês</p>
        <p class="stat-value" id="ds-receitas">— Kz</p>
        <p class="stat-hint">Pagamentos confirmados</p>
      </div>
      <div class="stat-card red">
        <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="stat-label">Mensalidades Pendentes</p>
        <p class="stat-value" id="ds-pendentes">—</p>
        <p class="stat-hint">A aguardar pagamento</p>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon"><i class="fa-solid fa-house"></i></div>
        <p class="stat-label">Apartamentos Disponíveis</p>
        <p class="stat-value" id="ds-casas">—</p>
        <p class="stat-hint">De total de apartamentos</p>
      </div>
    </div>
    <!-- Gráfico -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-top:1.25rem;">
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-chart-bar"></i> Pagamentos por Mês</p></div>
        <div class="card-body"><canvas id="chart-pagamentos" height="200"></canvas></div>
      </div>
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-chart-pie"></i> Estado dos Apartamentos</p></div>
        <div class="card-body"><canvas id="chart-apartamentos" height="200"></canvas></div>
      </div>
    </div>
  </section>

  <!-- ── CADASTRO DE FUNCIONÁRIOS ── -->
  <section class="tab-section" id="tab-pedidos">
    <div class="page-header">
      <h1 class="page-title">Cadastro de Funcionários</h1>
    </div>
    <div class="tab">
      <button class="filter-btn active" onclick="mostrarFormFunc()">Novo</button>
      <button class="filter-btn" onclick="carregarFuncionarios()">Todos</button>
    </div>

    <!-- Formulário novo funcionário -->
    <div id="areaNovo">
      <form action="../api/registar_admin.php" method="POST">
        <div class="card">
          <div class="card-head"><p class="card-title"><i class="fa-solid fa-plus"></i> Adicionar Funcionário</p></div>
          <h2 class="step-title">Dados Pessoais</h2>
          <div class="form-grid">
            <div class="form-group full"><label>Nome Completo *</label><input type="text" name="nome" placeholder="Maria da Silva Santos" maxlength="120" required /></div>
            <div class="form-group"><label>Senha *</label><input type="password" name="senha" minlength="6" required /></div>
            <div class="form-group"><label>Telefone *</label><input type="tel" name="telefone" placeholder="9XX XXX XXX" maxlength="20" required /></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="email@exemplo.com" required /></div>
            <div class="form-group"><label>Data de Nascimento *</label><input type="date" name="nasc" required /></div>
            <div class="form-group"><label>Nacionalidade *</label><input type="text" name="nacionalidade" value="Angolana" /></div>
            <div class="form-group full"><label>Morada *</label><input type="text" name="morada" placeholder="Rua, bairro, município, província" required /></div>
          </div>
          <h2 class="step-title">Documento de Identificação</h2>
          <div class="form-grid">
            <div class="form-group"><label>Nº BI *</label><input type="text" name="numbi" placeholder="000XXXXXX LA 000" required /></div>
            <div class="form-group"><label>Data de Emissão *</label><input type="date" name="emissao" required /></div>
            <div class="form-group"><label>Data de Validade *</label><input type="date" name="validade" required /></div>
            <div class="form-group"><label>Local de Emissão *</label><input type="text" name="locale" placeholder="Luanda, SAE Patriota" required /></div>
            <div class="form-group"><label>Função *</label>
              <select name="funcao" required>
                <option value="Administrador">Administrador</option>
                <option value="Recursos Humanos">Recursos Humanos</option>
                <option value="Seguranca">Segurança</option>
                <option value="Area Tecnica">Área Técnica</option>
              </select>
            </div>
            <div class="form-group"><label>IBAN</label><input type="text" name="iban" placeholder="AO06XXXXXXXXXXXXXXXXXXXXX"></div>
          </div>
          <div class="reg-consent">
            <input type="checkbox" id="reg-consent-func" required />
            <label for="reg-consent-func">Declaro que os dados fornecidos são verdadeiros.</label>
          </div>
          <br>
          <div style="display:flex;gap:.75rem;">
            <button type="reset" class="btn-secondary"><i class="fa-solid fa-arrow-left"></i> Cancelar</button>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-paper-plane"></i> Registar Funcionário</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Lista de funcionários -->
    <div id="areaTodos" style="display:none;">
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-list"></i> Funcionários Registados</p></div>
        <div style="overflow-x:auto;">
          <table class="data-table"><thead><tr><th>Nome</th><th>BI</th><th>Função</th><th>Email</th><th>Estado</th></tr></thead>
          <tbody id="func-tbody"><tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">A carregar...</td></tr></tbody></table>
        </div>
      </div>
    </div>
  </section>

  <!-- ── CADASTRO DE MORADORES ── -->
  <section class="tab-section" id="tab-registos">
    <div class="page-header"><h1 class="page-title">Cadastro de Moradores</h1></div>
    <div class="tab">
      <button class="filter-btn active" onclick="mostrarFormMorador()">Novo</button>
      <button class="filter-btn" onclick="carregarMoradores()">Todos</button>
    </div>

    <!-- Formulário novo morador -->
    <div id="areaNovMorador">
      <form action="../api/registar_morador.php" method="POST">
        <div class="card">
          <div class="card-head"><p class="card-title"><i class="fa-solid fa-plus"></i> Adicionar Morador</p></div>
          <h2 class="step-title">Dados Pessoais</h2>
          <div class="form-grid">
            <div class="form-group full"><label>Nome Completo *</label><input type="text" name="nome" placeholder="Maria da Silva Santos" maxlength="120" required /></div>
            <div class="form-group"><label>Senha *</label><input type="password" name="senha" minlength="6" required /></div>
            <div class="form-group"><label>Telefone *</label><input type="tel" name="telefone" placeholder="9XX XXX XXX" maxlength="20" required /></div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" placeholder="email@exemplo.com" required /></div>
            <div class="form-group"><label>Data de Nascimento *</label><input type="date" name="nasc" required /></div>
            <div class="form-group"><label>Nacionalidade *</label><input type="text" name="nacionalidade" value="Angolana" /></div>
            <div class="form-group full"><label>Morada Actual *</label><input type="text" name="morada" placeholder="Rua, bairro, município, província" required /></div>
          </div>
          <h2 class="step-title">Documento de Identificação</h2>
          <div class="form-grid">
            <div class="form-group"><label>Nº BI *</label><input type="text" name="numbi" placeholder="000XXXXXX LA 000" required /></div>
            <div class="form-group"><label>Data de Emissão *</label><input type="date" name="emissao" required /></div>
            <div class="form-group"><label>Data de Validade *</label><input type="date" name="validade" required /></div>
            <div class="form-group"><label>Local de Emissão *</label><input type="text" name="locale" placeholder="Luanda, SAE Patriota" required /></div>
          </div>
          <div class="reg-consent">
            <input type="checkbox" id="reg-consent-mor" required />
            <label for="reg-consent-mor">Declaro que os dados fornecidos são verdadeiros.</label>
          </div>
          <br>
          <div style="display:flex;gap:.75rem;">
            <button type="reset" class="btn-secondary"><i class="fa-solid fa-arrow-left"></i> Cancelar</button>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-paper-plane"></i> Registar Morador</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Lista de moradores -->
    <div id="areaTodosMoradores" style="display:none;">
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-list"></i> Moradores Registados</p></div>
        <div style="overflow-x:auto;">
          <table class="data-table"><thead><tr><th>Nome</th><th>BI</th><th>Apartamento</th><th>Telefone</th><th>Email</th><th>Estado</th></tr></thead>
          <tbody id="corpoTabela"><tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">A carregar...</td></tr></tbody></table>
        </div>
      </div>
    </div>
  </section>

  <!-- ── GESTÃO DE APARTAMENTOS ── -->
  <section class="tab-section" id="tab-casas">
    <div class="page-header">
      <h1 class="page-title">Gestão de Apartamentos</h1>
      <p class="page-sub">Adicione e gira as residências do condomínio</p>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
      <!-- Form -->
      <form action="../api/casa.php" method="POST">
        <div class="card">
          <div class="card-head"><p class="card-title"><i class="fa-solid fa-plus"></i> Adicionar Apartamento</p></div>
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group">
                <label>Bloco *</label>
                <select name="id_bloco" required id="sel-bloco">
                  <option value="">— Seleccione —</option>
                </select>
              </div>
              <div class="form-group"><label>Letra do Bloco</label><input type="text" name="bloco" placeholder="A,B,C..." maxlength="5" /></div>
              <div class="form-group"><label>Número do Apartamento *</label><input type="text" name="casanum" placeholder="101, 202B..." required /></div>
              <div class="form-group"><label>Andar</label><input type="number" name="andar" value="0" min="0" max="30" /></div>
              <div class="form-group"><label>Tipologia</label><input type="text" name="tipologia" value="V3" /></div>
              <div class="form-group"><label>Estado</label>
                <select name="estado">
                  <option value="Disponivel">Disponível</option>
                  <option value="Ocupado">Ocupado</option>
                  <option value="Manutencao">Em Manutenção</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn-primary" style="margin-top:1rem; width:100%;">
              <i class="fa-solid fa-house-circle-check"></i> Adicionar Apartamento
            </button>
          </div>
        </div>
      </form>

      <!-- Lista -->
      <div class="card">
        <div class="card-head">
          <p class="card-title"><i class="fa-solid fa-list"></i> Apartamentos Registados</p>
          <button class="btn-secondary btn-sm" onclick="carregarCasas()"><i class="fa-solid fa-rotate"></i></button>
        </div>
        <div style="overflow-x:auto; max-height:500px; overflow-y:auto;">
          <table class="data-table" id="houses-table">
            <thead><tr><th>Bloco</th><th>Nº</th><th>Andar</th><th>Tipo</th><th>Estado</th><th>Código</th></tr></thead>
            <tbody id="casas-tbody">
              <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">A carregar...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- ── PAGAMENTOS ── -->
  <section class="tab-section" id="tab-pagamentos">
    <div class="page-header">
      <h1 class="page-title">Pagamentos Submetidos</h1>
      <p class="page-sub">Comprovativos recebidos — confirme ou rejeite</p>
    </div>
    <div class="card">
      <div class="card-head">
        <p class="card-title"><i class="fa-solid fa-receipt"></i> Todos os Pagamentos</p>
        <button class="btn-secondary btn-sm" onclick="carregarPagamentos()"><i class="fa-solid fa-rotate"></i></button>
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr><th>Morador</th><th>Apartamento</th><th>Valor</th><th>Método</th><th>Data</th><th>Estado</th><th>Ação</th></tr></thead>
          <tbody id="pays-tbody"><tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">A carregar...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ── MENSALIDADES MORADORES ── -->
  <section class="tab-section" id="tab-moradores">
    <div class="page-header">
      <h1 class="page-title">Mensalidades dos Moradores</h1>
      <p class="page-sub">Quotas pendentes e histórico de pagamentos</p>
    </div>
    <div class="card">
      <div class="card-head">
        <p class="card-title"><i class="fa-solid fa-file-invoice"></i> Lista de Mensalidades</p>
        <button class="btn-secondary btn-sm" onclick="carregarMensalidades()"><i class="fa-solid fa-rotate"></i></button>
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr><th>Morador</th><th>Apartamento</th><th>Serviço</th><th>Mês/Ano</th><th>Valor</th><th>Vencimento</th><th>Estado</th></tr></thead>
          <tbody id="mor-tbody"><tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">A carregar...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ── VISITAS ── -->
  <section class="tab-section" id="tab-visitas">
    <div class="page-header">
      <h1 class="page-title">Gestão de Visitas</h1>
      <p class="page-sub">Acompanhe as entradas autorizadas pelos moradores</p>
    </div>
    <div class="card">
      <div class="card-head">
        <p class="card-title"><i class="fa-solid fa-user-clock"></i> Visitas Agendadas</p>
        <button class="btn-secondary btn-sm" onclick="carregarVisitas()"><i class="fa-solid fa-rotate"></i></button>
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr><th>Visitante</th><th>Morador</th><th>Apartamento</th><th>Data/Hora</th><th>Estado</th><th>Código</th><th>Acção</th></tr></thead>
          <tbody id="visitas-tbody"><tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">A carregar...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ── RESERVAS DE ÁREAS COMUNS ── -->
  <section class="tab-section" id="tab-reservas">
    <div class="page-header">
      <h1 class="page-title">Reservas de Áreas Comuns</h1>
      <p class="page-sub">Moderador de pedidos de piscina, salão, etc.</p>
    </div>
    <div class="card">
      <div class="card-head">
        <p class="card-title"><i class="fa-solid fa-calendar-check"></i> Pedidos de Reserva</p>
        <button class="btn-secondary btn-sm" onclick="carregarReservas()"><i class="fa-solid fa-rotate"></i></button>
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr><th>Área</th><th>Morador</th><th>Data</th><th>Horário</th><th>Estado</th><th>Acção</th></tr></thead>
          <tbody id="reservas-tbody"><tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">A carregar...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>
  <section class="tab-section" id="tab-relatorio">
    <div class="page-header" style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
      <div>
        <h1 class="page-title">Relatório Financeiro</h1>
        <p class="page-sub">Resumo financeiro e operacional mensal</p>
      </div>
      <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
        <select id="rel-mes" onchange="buildReport()" style="background:var(--dark3); border:1px solid var(--border); color:var(--text); padding:.5rem .75rem; border-radius:8px; font-family:'DM Sans',sans-serif;">
          <option value="1">Janeiro</option><option value="2">Fevereiro</option>
          <option value="3">Março</option><option value="4">Abril</option>
          <option value="5">Maio</option><option value="6">Junho</option>
          <option value="7">Julho</option><option value="8">Agosto</option>
          <option value="9">Setembro</option><option value="10">Outubro</option>
          <option value="11">Novembro</option><option value="12">Dezembro</option>
        </select>
        <select id="rel-ano" onchange="buildReport()" style="background:var(--dark3); border:1px solid var(--border); color:var(--text); padding:.5rem .75rem; border-radius:8px; font-family:'DM Sans',sans-serif;">
          <option>2025</option><option selected>2026</option>
        </select>
        <button class="btn-primary" onclick="window.print()"><i class="fa-solid fa-file-pdf"></i> Gerar PDF</button>
      </div>
    </div>
    <div id="relatorio-content">
      <div class="empty-state"><i class="fa-solid fa-chart-bar"></i><p>Seleccione mês e ano para gerar o relatório</p></div>
    </div>
  </section>
</main>

<!-- TOAST -->
<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toast-msg"></span></div>

<script>
// ═══════════════════════════════════════════════════════════
// API — fetch helper
// ═══════════════════════════════════════════════════════════
const API = '../api/api_dashboard.php';

function apiFetch(acao, params = '') {
  return fetch(`${API}?acao=${acao}${params}`).then(r => r.json());
}

function apiPost(acao, body = {}) {
  const fd = new FormData();
  Object.entries(body).forEach(([k,v]) => fd.append(k, v));
  return fetch(`${API}?acao=${acao}`, { method:'POST', body: fd }).then(r => r.json());
}

// ═══════════════════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════════════════
window.onload = () => {
  clock();
  setInterval(clock, 1000);

  const now = new Date();
  document.getElementById('dash-date').textContent =
    now.toLocaleDateString('pt-AO', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
  document.getElementById('rel-mes').value = now.getMonth() + 1;

  carregarDashboard();
  carregarCasas();
  carregarBlocos();
  carregarPagamentos();
  carregarMensalidades();
};

// ═══════════════════════════════════════════════════════════
// NAVEGAÇÃO
// ═══════════════════════════════════════════════════════════
function switchTab(id, btn) {
  document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  if (btn) btn.classList.add('active');
  if (id === 'relatorio') buildReport();
  if (id === 'visitas') carregarVisitas();
  if (id === 'reservas') carregarReservas();
}
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }

function mostrarFormFunc() {
  document.getElementById('areaNovo').style.display = '';
  document.getElementById('areaTodos').style.display = 'none';
}
function mostrarFormMorador() {
  document.getElementById('areaNovMorador').style.display = '';
  document.getElementById('areaTodosMoradores').style.display = 'none';
}

// ═══════════════════════════════════════════════════════════
// CLOCK
// ═══════════════════════════════════════════════════════════
function clock() {
  document.getElementById('clock-display').textContent = new Date().toLocaleTimeString('pt-AO');
}

// ═══════════════════════════════════════════════════════════
// DASHBOARD KPIs
// ═══════════════════════════════════════════════════════════
let chartPag = null, chartApt = null;

function carregarDashboard() {
  apiFetch('resumo').then(d => {
    if (!d.sucesso) return;
    const r = d.dados;
    document.getElementById('ds-total-reg').textContent  = r.total_moradores;
    document.getElementById('ds-pendentes').textContent  = r.mensalidades_pendentes;
    document.getElementById('ds-casas').textContent      = r.apartamentos_disponiveis;
    document.getElementById('ds-receitas').textContent   = fmt(r.receitas_mes) + ' Kz';
    document.getElementById('badge-pay').textContent     = r.mensalidades_pendentes;

    // Gráficos simples
    renderCharts(r);
  });
}

function renderCharts(r) {
  const ctx1 = document.getElementById('chart-pagamentos');
  const ctx2 = document.getElementById('chart-apartamentos');
  if (!ctx1 || !ctx2) return;

  if (chartPag) chartPag.destroy();
  chartPag = new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
      datasets: [{ label: 'Receitas (Kz)', data: Array(12).fill(0), backgroundColor: 'rgba(201,168,76,0.7)', borderRadius: 6 }]
    },
    options: { responsive: true, plugins: { legend: { labels: { color: '#ccc' } } }, scales: { y: { ticks: { color:'#ccc' } }, x: { ticks: { color:'#ccc' } } } }
  });

  if (chartApt) chartApt.destroy();
  const disp = parseInt(r.apartamentos_disponiveis) || 0;
  const total = parseInt(r.total_apartamentos) || 0;
  const ocup = total - disp;
  chartApt = new Chart(ctx2, {
    type: 'doughnut',
    data: {
      labels: ['Disponíveis','Ocupados'],
      datasets: [{ data: [disp, ocup], backgroundColor: ['rgba(76,175,125,0.8)', 'rgba(201,168,76,0.8)'] }]
    },
    options: { responsive: true, plugins: { legend: { labels: { color: '#ccc' } } } }
  });
}

// ═══════════════════════════════════════════════════════════
// CASAS / APARTAMENTOS
// ═══════════════════════════════════════════════════════════
function carregarCasas() {
  apiFetch('casas').then(d => {
    const tb = document.getElementById('casas-tbody');
    if (!d.sucesso || !d.dados.length) { tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem apartamentos registados</td></tr>'; return; }
    const badges = { 'Disponivel': 'badge-ok', 'Ocupado': 'badge-warn', 'Manutencao': 'badge-err', 'Reservado': 'badge-info' };
    tb.innerHTML = d.dados.map(a => `
      <tr>
        <td>${a.bloco}</td>
        <td>${a.numero}</td>
        <td>${a.andar}º</td>
        <td>${a.tipologia}</td>
        <td><span class="${badges[a.estado]||'badge-info'}">${a.estado}</span></td>
        <td><code>${a.codigo}</code></td>
      </tr>`).join('');
  }).catch(() => {});
}

function carregarBlocos() {
  apiFetch('blocos').then(d => {
    const sel = document.getElementById('sel-bloco');
    if (!sel || !d.sucesso) return;
    d.dados.forEach(b => {
      const opt = document.createElement('option');
      opt.value = b.id; opt.textContent = `Bloco ${b.letra}`;
      sel.appendChild(opt);
    });
  }).catch(() => {});
}

// ═══════════════════════════════════════════════════════════
// MORADORES
// ═══════════════════════════════════════════════════════════
function carregarMoradores() {
  document.getElementById('areaNovMorador').style.display = 'none';
  document.getElementById('areaTodosMoradores').style.display = '';
  apiFetch('moradores').then(d => {
    const tb = document.getElementById('corpoTabela');
    if (!d.sucesso || !d.dados.length) { tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem moradores registados</td></tr>'; return; }
    tb.innerHTML = d.dados.map(m => `
      <tr>
        <td>${m.nome}</td>
        <td>${m.numbi}</td>
        <td>${m.apartamento || '—'}</td>
        <td>${m.telefone}</td>
        <td>${m.email}</td>
        <td><span class="${m.estado_conta==='Activo'?'badge-ok':'badge-err'}">${m.estado_conta}</span></td>
      </tr>`).join('');
    document.getElementById('badge-reg').textContent = d.dados.length;
  }).catch(() => {});
}

function carregarFuncionarios() {
  document.getElementById('areaNovo').style.display = 'none';
  document.getElementById('areaTodos').style.display = '';
  apiFetch('admins').then(d => {
    const tb = document.getElementById('func-tbody');
    if (!d.sucesso || !d.dados.length) { tb.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem funcionários registados</td></tr>'; return; }
    tb.innerHTML = d.dados.map(a => `
      <tr>
        <td>${a.nome}</td>
        <td>—</td>
        <td>${a.funcao}</td>
        <td>${a.email}</td>
        <td><span class="${a.activo?'badge-ok':'badge-err'}">${a.activo?'Activo':'Inactivo'}</span></td>
      </tr>`).join('');
    document.getElementById('badge-pedidos').textContent = d.dados.length;
  }).catch(() => {});
}

// ═══════════════════════════════════════════════════════════
// PAGAMENTOS
// ═══════════════════════════════════════════════════════════
function carregarPagamentos() {
  apiFetch('pagamentos').then(d => {
    const tb = document.getElementById('pays-tbody');
    if (!d.sucesso || !d.dados.length) { tb.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem pagamentos submetidos</td></tr>'; return; }
    const bc = { confirmado:'badge-ok', pendente:'badge-warn', rejeitado:'badge-err' };
    tb.innerHTML = d.dados.map(p => `
      <tr>
        <td>${p.morador}</td>
        <td>${p.apartamento||'—'}</td>
        <td>${fmt(p.valor_pago)} Kz</td>
        <td>${p.metodo}</td>
        <td>${new Date(p.data_pagamento).toLocaleDateString('pt-AO')}</td>
        <td><span class="${bc[p.estado]||'badge-info'}">${p.estado}</span></td>
        <td>
          ${p.estado==='pendente' ? `
          <button class="btn-primary btn-sm" onclick="confirmarPag(${p.id},'confirmado')"><i class="fa-solid fa-check"></i></button>
          <button class="btn-secondary btn-sm" onclick="confirmarPag(${p.id},'rejeitado')"><i class="fa-solid fa-xmark"></i></button>` : '—'}
        </td>
      </tr>`).join('');
  }).catch(() => {});
}

function confirmarPag(id, estado) {
  apiPost('confirmar_pagamento', { id, estado }).then(d => {
    if (d.sucesso) { showToast('Pagamento ' + estado + ' com sucesso!'); carregarPagamentos(); carregarDashboard(); }
    else showToast('Erro: ' + d.erro, true);
  }).catch(() => showToast('Erro de rede', true));
}

function carregarVisitas() {
  apiFetch('visitas').then(d => {
    const tb = document.getElementById('visitas-tbody');
    if (!d.sucesso || !d.dados.length) { tb.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem visitas registadas</td></tr>'; return; }
    tb.innerHTML = d.dados.map(v => `
      <tr>
        <td>${v.nome_visitante}</td>
        <td>${v.morador}</td>
        <td>${v.apartamento}</td>
        <td>${new Date(v.data_prevista).toLocaleDateString('pt-AO')} ${v.hora_prevista || ''}</td>
        <td><span class="badge-${v.estado==='autorizado'?'ok':'warn'}">${v.estado}</span></td>
        <td><strong style="color:var(--gold)">${v.codigo_acesso || '-'}</strong></td>
        <td>
           <button class="btn-secondary btn-sm" onclick="validarAgendamento(${v.id}, 'visita', 'negado')"><i class="fa-solid fa-ban"></i></button>
        </td>
      </tr>`).join('');
  });
}

function carregarReservas() {
  apiFetch('agendamentos_area').then(d => {
    const tb = document.getElementById('reservas-tbody');
    if (!d.sucesso || !d.dados.length) { tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem reservas registadas</td></tr>'; return; }
    tb.innerHTML = d.dados.map(a => `
      <tr>
        <td>${a.area_comum}</td>
        <td>${a.morador}</td>
        <td>${new Date(a.data_evento).toLocaleDateString('pt-AO')}</td>
        <td>${a.hora_inicio.substring(0,5)} - ${a.hora_fim.substring(0,5)}</td>
        <td><span class="badge-${a.estado==='confirmado'?'ok':'warn'}">${a.estado}</span></td>
        <td>
          ${a.estado === 'pendente' ? `
          <button class="btn-primary btn-sm" onclick="validarAgendamento(${a.id}, 'area', 'confirmado')"><i class="fa-solid fa-check"></i></button>
          <button class="btn-secondary btn-sm" onclick="validarAgendamento(${a.id}, 'area', 'cancelado')"><i class="fa-solid fa-xmark"></i></button>
          ` : '—'}
        </td>
      </tr>`).join('');
  });
}

function validarAgendamento(id, tipo, estado) {
  apiPost('validar_agendamento', { id, tipo, estado }).then(d => {
    if (d.sucesso) { 
      showToast('Estado actualizado!'); 
      if (tipo === 'area') carregarReservas(); else carregarVisitas();
    } else showToast('Erro: ' + d.erro, true);
  });
}

// ═══════════════════════════════════════════════════════════
// MENSALIDADES
// ═══════════════════════════════════════════════════════════
function carregarMensalidades() {
  apiFetch('mensalidades').then(d => {
    const tb = document.getElementById('mor-tbody');
    if (!d.sucesso || !d.dados.length) { tb.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem mensalidades registadas</td></tr>'; return; }
    const meses = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    const bc = { pago:'badge-ok', pendente:'badge-warn', atrasado:'badge-err', dispensado:'badge-info' };
    tb.innerHTML = d.dados.map(m => `
      <tr>
        <td>${m.nome}</td>
        <td>${m.apartamento}</td>
        <td>${m.servico}</td>
        <td>${meses[m.mes]||m.mes}/${m.ano}</td>
        <td>${fmt(m.valor)} Kz</td>
        <td>${m.vencimento}</td>
        <td><span class="${bc[m.estado]||'badge-info'}">${m.estado}</span></td>
      </tr>`).join('');
  }).catch(() => {});
}

// ═══════════════════════════════════════════════════════════
// RELATÓRIO
// ═══════════════════════════════════════════════════════════
function buildReport() {
  const mes = document.getElementById('rel-mes').value;
  const ano = document.getElementById('rel-ano').value;
  const container = document.getElementById('relatorio-content');
  const meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

  apiFetch('pagamentos').then(d => {
    const pags = (d.dados || []).filter(p => {
      const dt = new Date(p.data_pagamento);
      return dt.getMonth()+1 == mes && dt.getFullYear() == ano && p.estado === 'confirmado';
    });
    const total = pags.reduce((s, p) => s + parseFloat(p.valor_pago || 0), 0);

    container.innerHTML = `
    <div class="card" style="padding:1.5rem;">
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid var(--gold);">
        <div style="font-size:2rem;">🏛️</div>
        <div>
          <h2 style="margin:0;font-size:1.2rem;">Relatório Financeiro — ${meses[mes]} ${ano}</h2>
          <p style="color:var(--text-muted);font-size:.8rem;margin:0;">Gerado em ${new Date().toLocaleDateString('pt-AO')}</p>
        </div>
        <div style="margin-left:auto;text-align:right;">
          <p style="font-size:.75rem;color:var(--text-muted);">Total do Mês</p>
          <p style="font-size:1.5rem;font-weight:900;color:var(--gold);">${fmt(total)} Kz</p>
        </div>
      </div>
      ${pags.length ? `
      <table class="data-table">
        <thead><tr><th>Morador</th><th>Apartamento</th><th>Valor</th><th>Método</th><th>Data</th></tr></thead>
        <tbody>${pags.map(p => `<tr><td>${p.morador}</td><td>${p.apartamento||'—'}</td><td>${fmt(p.valor_pago)} Kz</td><td>${p.metodo}</td><td>${new Date(p.data_pagamento).toLocaleDateString('pt-AO')}</td></tr>`).join('')}</tbody>
      </table>` :
      `<div class="empty-state"><i class="fa-solid fa-receipt"></i><p>Sem pagamentos confirmados em ${meses[mes]} ${ano}</p></div>`}
    </div>`;
  }).catch(() => {
    container.innerHTML = '<div class="empty-state"><i class="fa-solid fa-exclamation-circle"></i><p>Erro ao carregar relatório</p></div>';
  });
}

// ═══════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════
function fmt(n) { return new Intl.NumberFormat('pt-AO').format(n || 0); }

function showToast(msg, err) {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast' + (err ? ' error' : '') + ' show';
  setTimeout(() => t.classList.remove('show'), 3500);
}

// Estilo inline para badges
const style = document.createElement('style');
style.textContent = `
  .badge-ok   { background:rgba(76,175,125,.15); color:#4caf7d; border:1px solid rgba(76,175,125,.3); padding:2px 8px; border-radius:6px; font-size:.78rem; font-weight:600; }
  .badge-warn { background:rgba(255,167,38,.15);  color:#ffa726; border:1px solid rgba(255,167,38,.3); padding:2px 8px; border-radius:6px; font-size:.78rem; font-weight:600; }
  .badge-err  { background:rgba(224,82,82,.15);   color:#e05252; border:1px solid rgba(224,82,82,.3);  padding:2px 8px; border-radius:6px; font-size:.78rem; font-weight:600; }
  .badge-info { background:rgba(100,149,237,.15); color:#6495ed; border:1px solid rgba(100,149,237,.3);padding:2px 8px; border-radius:6px; font-size:.78rem; font-weight:600; }
  .btn-sm { padding:.3rem .65rem; font-size:.78rem; }
`;
document.head.appendChild(style);
</script>
</body>
</html>
