<?php session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

// Carregar blocos
$sql_blocos = "SELECT id, letra, descricao FROM bloco ORDER BY letra";
$resultado_blocos = mysqli_query($conexao, $sql_blocos);
$blocos = [];
while ($row = mysqli_fetch_assoc($resultado_blocos)) {
    $blocos[$row['id']] = $row;
}
?>
<script>
// Dados de blocos para JavaScript
window.BLCOES_DATA = <?php echo json_encode($blocos); ?>;
</script>


<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nosso Zimbo — Painel de Administração</title>
<link rel="stylesheet" href="../css/nosso-zimbo-admin.css">

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Immediate script to prevent flash of un-themed content
    const savedTheme = localStorage.getItem('nz-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
  </script>
</head>
<body>

<!-- PDF PRINT HEADER (hidden normally, shown only on print) -->
<div id="pdf-report-header" style="display:none; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom: 3px solid #c9a84c;">
  <div style="display:flex; align-items:center; gap:1rem;">
    <div style="width:50px;height:50px;background:linear-gradient(135deg,#9a7a2e,#c9a84c);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#000;flex-shrink:0;">🏛</div>
    <div>
      <h1 style="font-size:1.4rem;font-weight:900;color:#111;margin:0;">Condomínio Nosso Zimbo</h1>
      <p style="font-size:.82rem;color:#666;margin:0;">Relatório Financeiro Oficial · <span id="pdf-gen-date"></span></p>
    </div>
    <div style="margin-left:auto;text-align:right;">
      <p style="font-size:.75rem;color:#888;">NIF: 000.000.000</p>
      <p style="font-size:.75rem;color:#888;">nossozimbo@admin.ao</p>
      <p style="font-size:.75rem;color:#888;">923 456 789</p>
    </div>
  </div>
</div>

<?php include('sidebar_admin.php'); ?>

<!-- MAIN -->
<main class="main-content">
  <header class="topbar">
    <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
    <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo — Admin</span>
    <div class="topbar-right">
      <div class="clock-display" id="clock-display"></div>
      <div class="avatar-admin" style="width:34px;height:34px;">AD</div>
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
        <p class="stat-label">Total Registos</p>
        <p class="stat-value" id="ds-total-reg">0</p>
        <p class="stat-hint">Visitantes registados</p>
      </div>
      <div class="stat-card green">
        <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
        <p class="stat-label">Receitas do Mês</p>
        <p class="stat-value" id="ds-receitas">0 Kz</p>
        <p class="stat-hint">Pagamentos recebidos</p>
      </div>
      <div class="stat-card red">
        <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="stat-label">Pendentes</p>
        <p class="stat-value" id="ds-pendentes">0</p>
        <p class="stat-hint">A aguardar aprovação</p>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon"><i class="fa-solid fa-house"></i></div>
        <p class="stat-label">Casas Disponíveis</p>
        <p class="stat-value" id="ds-casas">0</p>
        <p class="stat-hint">De um total de casas V3</p>
      </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="charts-grid" style="margin-top: 1.5rem;">
      <div class="chart-card">
        <p class="chart-title"><i class="fa-solid fa-chart-line"></i> Fluxo de Receitas (Últimos 6 Meses)</p>
        <canvas id="chartReceitas"></canvas>
      </div>
      <div class="chart-card">
        <p class="chart-title"><i class="fa-solid fa-chart-pie"></i> Ocupação & Servicos</p>
        <canvas id="chartServicos"></canvas>
      </div>
    </div>
  </section>

  <!-- ── CADASTRO DE FUNCIONÁRIOS (CRUD) ── -->
  <section class="tab-section" id="tab-pedidos">
    <div class="page-header">
      <h1 class="page-title">💼 Gestão de Funcionários</h1>
      <p class="page-sub">Cadastre e gira a sua equipa administrativa e de campo</p>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap: 2rem;">
      <!-- Form Column -->
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-user-plus"></i> <span id="func-form-titulo">Novo Funcionário</span></p></div>
        <div class="card-body">
          <input type="hidden" id="f-editing-id" value="">
          <div class="form-grid">
            <div class="form-group full">
              <label>Nome Completo *</label>
              <input type="text" id="f-nome" placeholder="Ex: Maria da Silva Santos" required />
            </div>
            <div class="form-group">
              <label>Senha *</label>
              <input type="password" id="f-senha" placeholder="Senha de acesso" />
            </div>
            <div class="form-group">
              <label>Telefone *</label>
              <input type="tel" id="f-telefone" placeholder="9XX XXX XXX" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" id="f-email" placeholder="email@exemplo.com" />
            </div>
            <div class="form-group">
              <label>Data Nasc. *</label>
              <input type="date" id="f-nascimento" />
            </div>
            <div class="form-group">
              <label>Função *</label>
              <select id="f-funcao">
                <option value="">Seleccione...</option>
                <option value="Administrador">Administrador</option>
                <option value="RH">Recursos Humanos</option>
                <option value="Segurança">Segurança</option>
                <option value="Área Técnica">Área Técnica</option>
                <option value="Limpeza">Limpeza</option>
              </select>
            </div>
            <div class="form-group">
              <label>Nacionalidade</label>
              <input type="text" id="f-nacionalidade" value="Angolana" />
            </div>
            <div class="form-group">
              <label>Estado</label>
              <select id="f-estado-func">
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
                <option value="Férias">Férias</option>
              </select>
            </div>
            <div class="form-group full">
              <label>Morada Actual</label>
              <input type="text" id="f-morada" placeholder="Bairro, Rua, Casa nº" />
            </div>
          </div>
          <div style="display:flex; gap:.75rem; margin-top:1.5rem;">
            <button class="btn-primary" style="flex:1;" onclick="salvarFuncionario()">
              <i class="fa-solid fa-floppy-disk"></i> <span id="func-btn-txt">Registar Funcionário</span>
            </button>
            <button class="btn-secondary" onclick="resetFuncForm()">Limpar</button>
          </div>
        </div>
      </div>

      <!-- List Column -->
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-list-ul"></i> Lista de Colaboradores</p></div>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Cargo</th>
                <th>Telefone</th>
                <th>Estado</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody id="funcionarios-tbody">
              <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem funcionários registados</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- ── CADASTRO DE MORADORES (CRUD) ── -->
  <section class="tab-section" id="tab-registos">
    <div class="page-header">
      <h1 class="page-title">🏠 Gestão de Moradores</h1>
      <p class="page-sub">Controle os acessos e dados de todos os residentes</p>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap: 2rem;">
      <!-- Form Column -->
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-user-plus"></i> <span id="mor-form-titulo">Novo Morador</span></p></div>
        <div class="card-body">
          <input type="hidden" id="m-editing-id" value="">
          <div class="form-grid">
            <div class="form-group full">
              <label>Nome Completo *</label>
              <input type="text" id="m-nome" required />
            </div>
            <div class="form-group">
              <label>Senha *</label>
              <input type="password" id="m-senha" />
            </div>
            <div class="form-group">
              <label>Telefone *</label>
              <input type="tel" id="m-telefone" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" id="m-email" />
            </div>
            <div class="form-group">
              <label>Nº Bilhete (BI) *</label>
              <input type="text" id="m-numbi" placeholder="000XXXXXX LA 000" />
            </div>
            <div class="form-group">
              <label>Data Nasc. *</label>
              <input type="date" id="m-nascimento" />
            </div>
            <div class="form-group">
              <label>Nacionalidade</label>
              <input type="text" id="m-nacionalidade" value="Angolana" />
            </div>
            <div class="form-group">
              <label>Estado da Conta</label>
              <select id="m-estado">
                <option value="Activo">Activo</option>
                <option value="Suspenso">Suspenso</option>
              </select>
            </div>
            <div class="form-group full">
               <label>Morada Anterior / Contacto</label>
               <input type="text" id="m-morada" />
            </div>
          </div>
          <div style="display:flex; gap:.75rem; margin-top:1.5rem;">
            <button class="btn-primary" style="flex:1;" onclick="salvarMorador()">
              <i class="fa-solid fa-floppy-disk"></i> <span id="mor-btn-txt">Registar Morador</span>
            </button>
            <button class="btn-secondary" onclick="resetMorForm()">Limpar</button>
          </div>
        </div>
      </div>

      <!-- List Column -->
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-users"></i> Lista de Moradores Administrativa</p></div>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>BI</th>
                <th>Estado</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody id="moradores-admin-tbody">
              <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">Carregando moradores...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>


  <!-- ── GESTÃO DE CASAS ── -->
  <section class="tab-section" id="tab-casas">
    <div class="page-header">
      <h1 class="page-title">Gestão de Casas</h1>
      <p class="page-sub">Adicione e gira as residências do condomínio</p>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">

      <!-- Form de adicionar casa -->
      <form action="casa.php" method="POST">
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-plus"></i> Adicionar Nova Residência</p></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="form-group">
              <label>Letra do Bloco / Rua *</label>
              <input type="text" name="bloco" placeholder="Ex: A, B, C..." maxlength="3" />
            </div>
            <div class="form-group">
              <label>Número da Rua *</label>
              <input type="text" name="rua" placeholder="Ex: Rua 3, Rua Principal..." />
            </div>
            <div class="form-group">
              <label>Número da Casa *</label>
              <input type="text" name="casanum" placeholder="Ex: 14, 7B..." />
            </div>
            <div class="form-group">
              <label>Tipologia (fixo V3)</label>
              <input type="text" name="tipologia" value="V3" readonly style="opacity:.6;" />
            </div>
            <div class="form-group">
              <label>Andar / Apartamento</label>
              <input type="text" name="andar" placeholder="Ex: 2º Andar, Apt 4B" />
            </div>
            <div class="form-group full">
              <label>Estado Inicial</label>
              <select name="estado">
                <option value="ocupada">Disponível</option>
                <option value="desocupada">Ocupada</option>
                <option value="manutencao">Em Manutenção</option>
              </select>
            </div>
            <div class="form-group full">
              <label>Observações</label>
              <input type="text" name="obs" placeholder="Detalhes adicionais..." />
            </div>
          </div>
          <a href="admin_portal.php"><button class="btn-primary" style="margin-top:1rem; width:100%;" onclick="addHouse()">
            <i class="fa-solid fa-house-circle-check"></i> Adicionar Casa
          </button></a>
        </div>
      </div>
      </form>

      <!-- Lista de casas -->
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-list"></i> Residências Registadas</p></div>
        <div style="overflow-x:auto; max-height:500px; overflow-y:auto;">
          <table class="data-table" id="houses-table">
            <thead>
              <tr>
                <th>Bloco</th>
                <th>Número</th>
                <th>Tipo</th>
                <th>Andar</th>
                <th>Estado</th>
                <th>Morador</th>
                <th>Acção</th>
              </tr>
            </thead>
            <tbody id="houses-tbody">
              <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">Carregando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>

  <!-- ── PAGAMENTOS (VISITANTES) ── -->
  <section class="tab-section" id="tab-pagamentos">
    <div class="page-header">
      <h1 class="page-title">Pagamentos de Visitantes</h1>
      <p class="page-sub">Recibos e comprovativos recebidos dos visitantes</p>
    </div>
    <div class="card">
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr><th>Referência</th><th>Cliente</th><th>Serviço</th><th>Valor</th><th>Método</th><th>Data</th><th>Estado</th><th>Ver</th></tr></thead>
          <tbody id="pays-tbody"></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ── PAGAMENTOS MORADORES ── -->
  <section class="tab-section" id="tab-moradores">
    <div class="page-header">
      <h1 class="page-title">Pagamentos dos Moradores</h1>
      <p class="page-sub">Recibos enviados pelos moradores do portal</p>
    </div>

    <!-- Historico Morador -->
<div class="card">
        <div class="card-head">
          <p class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Histórico de Recebimentos</p>
          <span id="hist-count" style="font-size:.75rem;color:var(--text-muted);"></span>
        </div>
        <div style="max-height:380px;overflow-y:auto;padding:1rem 1.25rem;">
          <div class="pay-history-bar" id="pay-history-container">
            <div class="empty-state" style="padding:2rem 0;"><i class="fa-solid fa-receipt"></i><p>Sem pagamentos</p></div>
          </div>
        </div>
      </div>
    
<br>
    <!-- HISTÓRICO DE PAGAMENTOS -->
    <div style="display:grid; grid-template-columns: 1fr 1.6fr; gap:1.25rem; margin-bottom:1.25rem;">

      <!-- Barra de histórico -->
      <div class="card">
        <div class="card-head">
          <p class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Histórico de Recebimentos</p>
          <span id="hist-count" style="font-size:.75rem;color:var(--text-muted);"></span>
        </div>
        <div style="max-height:380px;overflow-y:auto;padding:1rem 1.25rem;">
          <div class="pay-history-bar" id="pay-history-container">
            <div class="empty-state" style="padding:2rem 0;"><i class="fa-solid fa-receipt"></i><p>Sem pagamentos</p></div>
          </div>
        </div>
      </div>

      <!-- Resumo rápido por morador -->
      <div class="card">
        <div class="card-head">
          <p class="card-title"><i class="fa-solid fa-users"></i> Resumo por Morador</p>
          <button class="btn-secondary btn-sm" onclick="renderMorPays(); renderPayHistory();"><i class="fa-solid fa-rotate"></i></button>
        </div>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead><tr><th>Morador</th><th>Apt</th><th>Tipo</th><th>Valor</th><th>Método</th><th>Data</th><th>Estado</th></tr></thead>
            <tbody id="mor-tbody"></tbody>
          </table>
        </div>
      </div>

    </div>
  </section>

  <!-- ── COMUNICAÇÃO ── -->
  <section class="tab-section" id="tab-comunicacao">
    <div class="page-header">
      <h1 class="page-title">📡 Central de Comunicação</h1>
      <p class="page-sub">Gira comunicados e mensagens com moradores</p>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap: 20px;">
      <!-- Criar Comunicado -->
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-plus"></i> Novo Comunicado</p></div>
        <form id="form-comunicado" style="padding:20px;">
          <div class="form-group">
            <label>Título</label>
            <input type="text" name="titulo" required>
          </div>
          <div class="form-group">
            <label>Tipo</label>
            <select name="tipo">
              <option value="informativo">Informativo</option>
              <option value="urgente">Urgente</option>
              <option value="manutencao">Manutenção</option>
            </select>
          </div>
          <div class="form-group">
            <label>Conteúdo</label>
            <textarea name="conteudo" required style="width:100%; min-height:100px; padding:10px;"></textarea>
          </div>
          <button type="submit" class="btn-primary" style="width:100%;">Enviar Comunicado</button>
        </form>
      </div>

      <!-- Chat com Moradores -->
      <div class="card">
          <div class="card-head"><p class="card-title"><i class="fa-solid fa-comments"></i> Mensagens Directas</p></div>
          <div style="display:flex; height:500px;">
              <div id="chat-users-list" style="width:200px; border-right:1px solid var(--border); overflow-y:auto; padding:10px;">
                  <!-- Lista de moradores com conversa -->
              </div>
              <div style="flex:1; display:flex; flex-direction:column;">
                  <div id="admin-chat-messages" style="flex:1; padding:20px; overflow-y:auto; background:var(--dark3);">
                      <p style="text-align:center; color:var(--text-muted);">Seleccione um morador para conversar</p>
                  </div>
                  <form id="admin-chat-form" style="padding:15px; background:var(--surface); border-top:1px solid var(--border); display:none;">
                      <input type="hidden" id="admin-chat-morador-id">
                      <div style="display:flex; gap:10px;">
                          <input type="text" id="admin-chat-input" class="form-control" style="flex:1;" placeholder="Escreva aqui..." required>
                          <button type="submit" class="btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
                      </div>
                  </form>
              </div>
          </div>
      </div>
    </div>
  </section>

  <!-- ── RELATÓRIO MENSAL ── -->
  <section class="tab-section" id="tab-relatorio">
    <div class="page-header" style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
      <div>
        <h1 class="page-title">Relatório Financeiro</h1>
        <p class="page-sub">Resumo financeiro e operacional — diário e mensal</p>
      </div>
      <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
        <!-- Tipo de relatório -->
        <div style="display:flex;background:var(--dark4);border:1px solid var(--border);border-radius:10px;padding:3px;gap:2px;">
          <button id="rel-tipo-dia" onclick="setRelTipo('dia')" style="padding:.4rem .9rem;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;cursor:pointer;background:var(--gold);color:#000;font-weight:600;transition:all .2s;">
            <i class="fa-solid fa-calendar-day"></i> Dia
          </button>
          <button id="rel-tipo-mes" onclick="setRelTipo('mes')" style="padding:.4rem .9rem;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;cursor:pointer;background:transparent;color:var(--text-muted);transition:all .2s;">
            <i class="fa-solid fa-calendar-days"></i> Mês
          </button>
        </div>
        <select id="rel-mes" onchange="buildReport()" style="background:var(--dark3); border:1px solid var(--border); color:var(--text); padding:.5rem .75rem; border-radius:8px; font-family:'DM Sans',sans-serif;">
          <option value="0">Janeiro</option><option value="1">Fevereiro</option>
          <option value="2">Março</option><option value="3">Abril</option>
          <option value="4">Maio</option><option value="5">Junho</option>
          <option value="6">Julho</option><option value="7">Agosto</option>
          <option value="8">Setembro</option><option value="9">Outubro</option>
          <option value="10">Novembro</option><option value="11">Dezembro</option>
        </select>
        <select id="rel-ano" onchange="buildReport()" style="background:var(--dark3); border:1px solid var(--border); color:var(--text); padding:.5rem .75rem; border-radius:8px; font-family:'DM Sans',sans-serif;">
          <option>2025</option><option>2026</option>
        </select>
        <button class="btn-primary" onclick="imprimirRelatorio()"><i class="fa-solid fa-file-pdf"></i> Gerar PDF</button>
      </div>
    </div>

    <div id="relatorio-content"></div>
  </section>

</main>

<!-- MODAL: Rever Pedido de Visitante -->
<div class="modal-overlay" id="modal-pedido" onclick="closeModal('modal-pedido')">
  <div class="modal-box" style="max-width:700px;" onclick="event.stopPropagation()">
    <button class="modal-close" onclick="closeModal('modal-pedido')"><i class="fa-solid fa-xmark"></i></button>
    <h3 class="modal-title"><i class="fa-solid fa-user-check" style="font-size:1rem;margin-right:.4rem;"></i> Revisão do Pedido de Visitante</h3>
    <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1.25rem;">Verifique todos os dados submetidos pelo visitante. Confirme que correspondem aos documentos fornecidos antes de aprovar.</p>

    <!-- Tabs internas -->
    <div style="display:flex;gap:4px;margin-bottom:1.25rem;background:var(--dark4);border-radius:10px;padding:4px;">
      <button id="ptab-dados" onclick="switchPTab('dados')" style="flex:1;padding:.5rem;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;cursor:pointer;background:var(--gold);color:#000;font-weight:600;transition:all .2s;">
        <i class="fa-solid fa-id-card"></i> Dados Pessoais
      </button>
      <button id="ptab-docs" onclick="switchPTab('docs')" style="flex:1;padding:.5rem;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;cursor:pointer;background:transparent;color:var(--text-muted);transition:all .2s;">
        <i class="fa-solid fa-images"></i> Documentos
      </button>
      <button id="ptab-servico" onclick="switchPTab('servico')" style="flex:1;padding:.5rem;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;cursor:pointer;background:transparent;color:var(--text-muted);transition:all .2s;">
        <i class="fa-solid fa-file-contract"></i> Serviço & Pagamento
      </button>
      <button id="ptab-casa" onclick="switchPTab('casa')" style="flex:1;padding:.5rem;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;cursor:pointer;background:transparent;color:var(--text-muted);transition:all .2s;">
        <i class="fa-solid fa-house"></i> Atribuir Residência
      </button>
    </div>

    <!-- Painel: Dados Pessoais -->
    <div id="ppanel-dados">
      <div class="recibo-box" id="pedido-dados-body">
        <div class="recibo-header">
          <div><i class="fa-solid fa-user" style="font-size:1.5rem;color:#000;"></i></div>
          <div><h3 id="pd-nome-header">—</h3><p id="pd-ref-header">Ref: —</p></div>
          <div style="margin-left:auto;"><span class="badge pendente" id="pd-status-badge">pendente</span></div>
        </div>
        <div class="recibo-body" style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
          <div>
            <div class="recibo-row"><span class="rk">Nome Completo</span><span class="rv" id="pd-nome">—</span></div>
            <div class="recibo-row"><span class="rk">BI / Documento</span><span class="rv" id="pd-bi">—</span></div>
            <div class="recibo-row"><span class="rk">Data de Nasc.</span><span class="rv" id="pd-nasc">—</span></div>
            <div class="recibo-row"><span class="rk">Nacionalidade</span><span class="rv" id="pd-nac">—</span></div>
            <div class="recibo-row"><span class="rk">Estado Civil</span><span class="rv" id="pd-estado-civil">—</span></div>
          </div>
          <div>
            <div class="recibo-row"><span class="rk">Telefone</span><span class="rv" id="pd-tel">—</span></div>
            <div class="recibo-row"><span class="rk">Email</span><span class="rv" id="pd-email">—</span></div>
            <div class="recibo-row"><span class="rk">Morada Actual</span><span class="rv" id="pd-morada">—</span></div>
            <div class="recibo-row"><span class="rk">Profissão</span><span class="rv" id="pd-profissao">—</span></div>
            <div class="recibo-row"><span class="rk">Data do Pedido</span><span class="rv" id="pd-data">—</span></div>
          </div>
        </div>
      </div>
      <!-- Checklist de validação -->
      <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;margin-top:1rem;">
        <p style="font-size:.78rem;font-weight:600;color:var(--gold);margin-bottom:.75rem;letter-spacing:.04em;text-transform:uppercase;"><i class="fa-solid fa-clipboard-check"></i> Checklist de Validação</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;">
          <label style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;cursor:pointer;">
            <input type="checkbox" id="chk-bi" style="accent-color:var(--gold);"> BI/documento verificado
          </label>
          <label style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;cursor:pointer;">
            <input type="checkbox" id="chk-tel" style="accent-color:var(--gold);"> Telefone confirmado
          </label>
          <label style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;cursor:pointer;">
            <input type="checkbox" id="chk-pay" style="accent-color:var(--gold);"> Comprovativo recebido
          </label>
          <label style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;cursor:pointer;">
            <input type="checkbox" id="chk-valor" style="accent-color:var(--gold);"> Valor corresponde ao serviço
          </label>
          <label style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;cursor:pointer;">
            <input type="checkbox" id="chk-morada" style="accent-color:var(--gold);"> Morada verificada
          </label>
          <label style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;cursor:pointer;">
            <input type="checkbox" id="chk-docs" style="accent-color:var(--gold);"> Documentos completos
          </label>
        </div>
      </div>
    </div>

    <!-- Painel: Documentos -->
    <div id="ppanel-docs" style="display:none;">
      <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.6;">Documentos submetidos pelo visitante. Clique em qualquer imagem para ampliar. Verifique a autenticidade antes de aprovar.</p>

      <!-- Grid de documentos: Selfie + BI lado a lado -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:.85rem;">

        <!-- Selfie -->
        <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;">
          <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--gold);font-weight:600;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;">
            <i class="fa-solid fa-camera"></i> Selfie
          </p>
          <div style="text-align:center;min-height:120px;display:flex;align-items:center;justify-content:center;">
            <img id="doc-selfie-img" src="" onclick="openLightbox(this.src,'Selfie do Visitante')"
              style="max-width:100%;max-height:180px;border-radius:10px;border:2px solid var(--border);object-fit:cover;display:none;"
              class="doc-img-thumb" />
            <div id="doc-selfie-empty" style="color:var(--text-muted);font-size:.82rem;text-align:center;">
              <i class="fa-solid fa-circle-xmark" style="opacity:.3;font-size:1.8rem;display:block;margin-bottom:.4rem;"></i>Selfie não submetida
            </div>
          </div>
        </div>

        <!-- Comprovativo de Pagamento -->
        <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;">
          <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--gold);font-weight:600;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;">
            <i class="fa-solid fa-file-invoice-dollar"></i> Comprovativo
          </p>
          <div style="text-align:center;min-height:120px;display:flex;align-items:center;justify-content:center;">
            <img id="doc-comprovativo-img" src="" onclick="openLightbox(this.src,'Comprovativo de Pagamento')"
              style="max-width:100%;max-height:180px;border-radius:10px;border:2px solid var(--border);object-fit:contain;display:none;"
              class="doc-img-thumb" />
            <div id="doc-comprovativo-pdf-link" style="display:none;padding:.5rem;text-align:center;">
              <a id="doc-comprovativo-pdf-a" href="#" target="_blank"
                style="color:var(--gold);text-decoration:none;font-size:.88rem;display:flex;flex-direction:column;align-items:center;gap:.4rem;">
                <i class="fa-solid fa-file-pdf" style="font-size:2rem;"></i>
                Abrir PDF do comprovativo
              </a>
            </div>
            <div id="doc-comprovativo-empty" style="color:var(--text-muted);font-size:.82rem;text-align:center;">
              <i class="fa-solid fa-circle-xmark" style="opacity:.3;font-size:1.8rem;display:block;margin-bottom:.4rem;"></i>Comprovativo não submetido
            </div>
          </div>
        </div>
      </div>

      <!-- BI Frente + Verso lado a lado -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">

        <!-- BI Frente -->
        <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;">
          <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--gold);font-weight:600;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;">
            <i class="fa-solid fa-id-card"></i> BI — Frente
          </p>
          <div style="text-align:center;min-height:120px;display:flex;align-items:center;justify-content:center;">
            <img id="doc-bi-frente-img" src="" onclick="openLightbox(this.src,'Bilhete de Identidade — Frente')"
              style="max-width:100%;max-height:180px;border-radius:10px;border:2px solid var(--border);object-fit:contain;display:none;"
              class="doc-img-thumb" />
            <div id="doc-bi-frente-empty" style="color:var(--text-muted);font-size:.82rem;text-align:center;">
              <i class="fa-solid fa-circle-xmark" style="opacity:.3;font-size:1.8rem;display:block;margin-bottom:.4rem;"></i>BI (frente) não submetido
            </div>
          </div>
        </div>

        <!-- BI Verso -->
        <div style="background:var(--dark4);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;">
          <p style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--gold);font-weight:600;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;">
            <i class="fa-solid fa-id-card"></i> BI — Verso
          </p>
          <div style="text-align:center;min-height:120px;display:flex;align-items:center;justify-content:center;">
            <img id="doc-bi-verso-img" src="" onclick="openLightbox(this.src,'Bilhete de Identidade — Verso')"
              style="max-width:100%;max-height:180px;border-radius:10px;border:2px solid var(--border);object-fit:contain;display:none;"
              class="doc-img-thumb" />
            <div id="doc-bi-verso-empty" style="color:var(--text-muted);font-size:.82rem;text-align:center;">
              <i class="fa-solid fa-circle-xmark" style="opacity:.3;font-size:1.8rem;display:block;margin-bottom:.4rem;"></i>BI (verso) não submetido
            </div>
          </div>
        </div>
      </div>

      <!-- Nota de verificação -->
      <div style="background:rgba(201,168,76,0.06);border:1px solid rgba(201,168,76,0.2);border-radius:var(--radius);padding:.85rem;margin-top:.85rem;font-size:.8rem;color:var(--text-muted);line-height:1.6;">
        <i class="fa-solid fa-magnifying-glass" style="color:var(--gold);"></i>
        Clique em qualquer imagem para a ampliar em ecrã completo. Para documentos PDF use o link de abertura directa.
      </div>
    </div>

    <!-- Painel: Serviço & Pagamento -->
    <div id="ppanel-servico" style="display:none;">
      <div class="recibo-box">
        <div class="recibo-header">
          <div><i class="fa-solid fa-file-invoice-dollar" style="font-size:1.5rem;color:#000;"></i></div>
          <div><h3>Detalhes do Serviço</h3><p id="pd-servico-header">—</p></div>
        </div>
        <div class="recibo-body">
          <div class="recibo-row"><span class="rk">Tipo de Serviço</span><span class="rv gold" id="pd-servico">—</span></div>
          <div class="recibo-row"><span class="rk">Modalidade</span><span class="rv" id="pd-modalidade">—</span></div>
          <div class="recibo-row"><span class="rk">Método de Pagamento</span><span class="rv" id="pd-metodo">—</span></div>
          <div class="recibo-row"><span class="rk">Valor Total</span><span class="rv gold" id="pd-valor" style="font-size:1.1rem;font-weight:800;">—</span></div>
          <div class="recibo-row"><span class="rk">Data do Pedido</span><span class="rv" id="pd-data2">—</span></div>
          <div class="recibo-row"><span class="rk">Código Gerado</span><span class="rv" id="pd-codigo" style="font-family:monospace;font-size:1.1rem;color:var(--gold);font-weight:900;">—</span></div>
        </div>
      </div>
      <!-- Nota de pagamento -->
      <div style="background:rgba(76,175,125,0.08);border:1px solid rgba(76,175,125,0.25);border-radius:var(--radius);padding:1rem;margin-top:1rem;">
        <p style="font-size:.82rem;color:var(--success);font-weight:600;margin-bottom:.4rem;"><i class="fa-solid fa-circle-info"></i> Nota sobre pagamento</p>
        <p style="font-size:.8rem;color:var(--text-muted);line-height:1.6;">O comprovativo de pagamento foi submetido pelo visitante no formulário. Confirme o recebimento efectivo nos extractos bancários antes de dar aprovação final.</p>
      </div>
      <!-- Campo de notas do admin -->
      <div style="margin-top:1rem;">
        <label style="font-size:.75rem;font-weight:500;color:var(--text-muted);display:block;margin-bottom:5px;">Notas internas (visíveis apenas para o admin)</label>
        <textarea id="pd-notas-admin" style="width:100%;background:var(--dark4);border:1px solid var(--border);border-radius:var(--radius);padding:.65rem .9rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.88rem;resize:vertical;min-height:70px;outline:none;" placeholder="Ex: Comprovativo validado, pagamento recebido em 12/05/2026..." onfocus="this.style.borderColor='var(--gold)'" onblur="this.style.borderColor='var(--border)'"></textarea>
      </div>
    </div>

    <!-- Painel: Atribuir Residência -->
    <div id="ppanel-casa" style="display:none;">
      <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.6;">Seleccione a residência a atribuir a este visitante. Após confirmar, o visitante receberá automaticamente os dados da casa no seu portal.</p>
      <div class="form-grid" style="margin-bottom:1rem;">
        <div class="form-group full">
          <label>Seleccionar Residência Disponível *</label>
          <select id="pd-casa-select" onchange="previewCasaSelecionada()">
            <option value="">— Seleccione uma residência —</option>
          </select>
        </div>
      </div>
      <!-- Preview da casa seleccionada -->
      <div id="pd-casa-preview" style="display:none;">
        <div style="background:var(--dark4);border:2px solid var(--gold);border-radius:var(--radius);padding:1.25rem;margin-bottom:1rem;">
          <p style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--gold);margin-bottom:.75rem;font-weight:600;"><i class="fa-solid fa-house-circle-check"></i> Residência Seleccionada</p>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;font-size:.85rem;">
            <div><span style="color:var(--text-muted);font-size:.72rem;display:block;">Bloco</span><strong id="pv-bloco">—</strong></div>
            <div><span style="color:var(--text-muted);font-size:.72rem;display:block;">Rua</span><strong id="pv-rua">—</strong></div>
            <div><span style="color:var(--text-muted);font-size:.72rem;display:block;">Número</span><strong id="pv-numero">—</strong></div>
            <div><span style="color:var(--text-muted);font-size:.72rem;display:block;">Tipo</span><strong id="pv-tipo">—</strong></div>
            <div><span style="color:var(--text-muted);font-size:.72rem;display:block;">Andar</span><strong id="pv-andar">—</strong></div>
            <div><span style="color:var(--text-muted);font-size:.72rem;display:block;">Zona</span><strong id="pv-zona">—</strong></div>
          </div>
        </div>
        <div class="code-box">
          <p class="code-label">Código de acesso que será enviado ao visitante</p>
          <div class="code-val" id="pd-novo-codigo">——</div>
          <p style="font-size:.72rem;color:var(--text-muted);margin-top:.5rem;">Este código será exibido no portal do visitante após aprovação</p>
        </div>
      </div>
      <div id="pd-sem-casas" style="display:none;">
        <div class="empty-state">
          <i class="fa-solid fa-house-circle-xmark"></i>
          <p>Sem residências disponíveis.<br>Adicione casas na aba "Gestão de Casas".</p>
        </div>
      </div>
    </div>

    <!-- Rodapé do modal com acções -->
    <div class="modal-footer" style="border-top:1px solid var(--border);padding-top:1rem;margin-top:1rem;">
      <button onclick="eliminarPedido()" id="btn-eliminar" style="display:inline-flex;align-items:center;gap:.4rem;background:rgba(224,82,82,0.12);border:1px solid rgba(224,82,82,0.4);color:var(--danger);padding:.65rem 1.1rem;border-radius:10px;cursor:pointer;font-size:.85rem;font-weight:600;transition:all .2s;" onmouseover="this.style.background='rgba(224,82,82,0.25)'" onmouseout="this.style.background='rgba(224,82,82,0.12)'">
        <i class="fa-solid fa-trash"></i> Eliminar
      </button>
      <button onclick="abrirNegarPedido()" id="btn-rejeitar" style="display:inline-flex;align-items:center;gap:.4rem;background:rgba(224,82,82,0.08);border:1px solid rgba(224,82,82,0.35);color:var(--danger);padding:.65rem 1.1rem;border-radius:10px;cursor:pointer;font-size:.85rem;font-weight:600;transition:all .2s;" onmouseover="this.style.background='rgba(224,82,82,0.2)'" onmouseout="this.style.background='rgba(224,82,82,0.08)'">
        <i class="fa-solid fa-ban"></i> Negar & Motivo
      </button>
      <div style="flex:1;"></div>
      <button class="btn-secondary" onclick="closeModal('modal-pedido')">Fechar</button>
      <button class="btn-primary" onclick="aprovarPedidoFinal()" id="btn-aprovar-final"><i class="fa-solid fa-circle-check"></i> Confirmar & Atribuir Residência</button>
    </div>
  </div>
</div>

<!-- MODAL: Confirmação de aprovação — dados da residência para o visitante -->
<div class="modal-overlay" id="modal-confirmacao" onclick="closeModal('modal-confirmacao')">
  <div class="modal-box" style="max-width:500px;text-align:center;" onclick="event.stopPropagation()">
    <button class="modal-close" onclick="closeModal('modal-confirmacao')"><i class="fa-solid fa-xmark"></i></button>
    <div style="width:64px;height:64px;background:rgba(76,175,125,0.15);border:2px solid var(--success);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:var(--success);margin:0 auto 1rem;">
      <i class="fa-solid fa-check"></i>
    </div>
    <h3 class="modal-title">Pedido Aprovado com Sucesso!</h3>
    <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem;line-height:1.6;">A residência foi atribuída e o visitante já pode consultar os dados no seu portal.</p>
    <div class="recibo-box" style="text-align:left;margin-bottom:1rem;">
      <div class="recibo-body">
        <div class="recibo-row"><span class="rk">Visitante</span><span class="rv" id="conf-nome">—</span></div>
        <div class="recibo-row"><span class="rk">Residência</span><span class="rv gold" id="conf-casa">—</span></div>
        <div class="recibo-row"><span class="rk">Código de Acesso</span><span class="rv" id="conf-codigo" style="font-family:monospace;font-size:1.3rem;color:var(--gold);font-weight:900;">—</span></div>
      </div>
    </div>
    <p style="font-size:.78rem;color:var(--text-muted);background:rgba(201,168,76,0.06);border:1px solid var(--border);border-radius:8px;padding:.75rem;line-height:1.5;">
      <i class="fa-solid fa-info-circle" style="color:var(--gold);"></i> O visitante verá os dados da residência no portal ao abrir a página de confirmação com o código de acesso.
    </p>
    <button class="btn-primary" style="width:100%;margin-top:1rem;" onclick="closeModal('modal-confirmacao')">
      <i class="fa-solid fa-check-double"></i> Concluído
    </button>
  </div>
</div>

<!-- MODAL: Ver Registo -->
<div class="modal-overlay" id="modal-reg" onclick="closeModal('modal-reg')">
  <div class="modal-box" onclick="event.stopPropagation()">
    <button class="modal-close" onclick="closeModal('modal-reg')"><i class="fa-solid fa-xmark"></i></button>
    <h3 class="modal-title">Detalhes do Registo</h3>
    <div id="modal-reg-body"></div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('modal-reg')">Fechar</button>
      <button class="btn-success" onclick="approveReg()"><i class="fa-solid fa-check"></i> Aprovar</button>
    </div>
  </div>
</div>

<!-- MODAL: Atribuir Casa -->
<div class="modal-overlay" id="modal-house" onclick="closeModal('modal-house')">
  <div class="modal-box" onclick="event.stopPropagation()">
    <button class="modal-close" onclick="closeModal('modal-house')"><i class="fa-solid fa-xmark"></i></button>
    <h3 class="modal-title">Atribuir Residência</h3>
    <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:1rem;">Seleccione a casa a atribuir ao visitante e envie os dados.</p>
    <div class="form-grid">
      <div class="form-group full">
        <label>Visitante</label>
        <input type="text" id="attr-visitante" readonly />
      </div>
      <div class="form-group full">
        <label>Seleccionar Casa Disponível</label>
        <select id="attr-casa-select">
          <option value="">— Seleccione —</option>
        </select>
      </div>
    </div>
    <div id="attr-house-preview" style="display:none; margin-top:1rem;">
      <div class="code-box">
        <p class="code-label">Código de 4 dígitos gerado</p>
        <div class="code-val" id="attr-codigo-display">——</div>
        <p style="font-size:.72rem; color:var(--text-muted); margin-top:.5rem;">Código enviado ao visitante via portal</p>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('modal-house')">Cancelar</button>
      <button class="btn-primary" onclick="assignHouse()"><i class="fa-solid fa-house-circle-check"></i> Atribuir & Enviar Código</button>
    </div>
  </div>
</div>

<!-- LIGHTBOX para visualização de documentos -->
<div class="lightbox-overlay" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()"><i class="fa-solid fa-xmark"></i></button>
  <img class="lightbox-img" id="lightbox-img" src="" alt="" onclick="event.stopPropagation()" />
  <div class="lightbox-label" id="lightbox-label">Documento</div>
</div>

<!-- MODAL: Negar pedido com motivo -->
<div class="modal-overlay" id="modal-negar" onclick="closeModal('modal-negar')">
  <div class="modal-box" style="max-width:520px;" onclick="event.stopPropagation()">
    <button class="modal-close" onclick="closeModal('modal-negar')"><i class="fa-solid fa-xmark"></i></button>

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
      <div style="width:44px;height:44px;background:rgba(224,82,82,0.12);border:2px solid var(--danger);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--danger);flex-shrink:0;">
        <i class="fa-solid fa-ban"></i>
      </div>
      <div>
        <h3 class="modal-title" style="margin:0;color:var(--danger);font-size:1.1rem;">Negar Pedido</h3>
        <p style="font-size:.78rem;color:var(--text-muted);margin:0;">O motivo será visível para o visitante no seu portal</p>
      </div>
    </div>

    <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.85rem;">Seleccione um motivo pré-definido ou escreva um motivo personalizado:</p>

    <!-- Motivos rápidos -->
    <div style="display:flex;flex-direction:column;gap:.45rem;margin-bottom:1rem;" id="negar-razoes">
      <button class="negar-reason-btn" onclick="selecionarMotivo(this, 'Documentos incompletos ou ilegíveis — o BI ou comprovativo não estão devidamente legíveis.')">
        <i class="fa-solid fa-file-circle-xmark"></i>
        Documentos incompletos ou ilegíveis
      </button>
      <button class="negar-reason-btn" onclick="selecionarMotivo(this, 'Comprovativo de pagamento não validado — não foi possível confirmar o pagamento junto do banco.')">
        <i class="fa-solid fa-money-bill-transfer"></i>
        Comprovativo de pagamento não confirmado
      </button>
      <button class="negar-reason-btn" onclick="selecionarMotivo(this, 'Informações inconsistentes — os dados pessoais fornecidos não correspondem aos documentos.')">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Dados inconsistentes com os documentos
      </button>
      <button class="negar-reason-btn" onclick="selecionarMotivo(this, 'Sem residências disponíveis neste momento. Poderá submeter novamente quando houver disponibilidade.')">
        <i class="fa-solid fa-house-circle-xmark"></i>
        Sem residências disponíveis
      </button>
      <button class="negar-reason-btn" onclick="selecionarMotivo(this, 'Perfil não aprovado pela gestão do condomínio. Para mais informações contacte a administração.')">
        <i class="fa-solid fa-user-xmark"></i>
        Perfil não aprovado pela gestão
      </button>
    </div>

    <!-- Motivo personalizado -->
    <div>
      <label style="font-size:.75rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.4rem;letter-spacing:.03em;">
        <i class="fa-solid fa-pen-to-square" style="color:var(--gold);"></i> Motivo personalizado / detalhes adicionais
      </label>
      <textarea id="negar-motivo-texto"
        style="width:100%;background:var(--dark4);border:1px solid var(--border);border-radius:var(--radius);padding:.65rem .9rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.88rem;resize:vertical;min-height:90px;outline:none;transition:border-color .2s;"
        placeholder="Ex: O comprovativo enviado não corresponde ao valor do serviço solicitado. Por favor contacte a administração para mais informações..."
        onfocus="this.style.borderColor='var(--danger)'" onblur="this.style.borderColor='var(--border)'"></textarea>
      <p style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem;"><i class="fa-solid fa-info-circle" style="color:var(--gold);"></i> O visitante verá este texto na página de estado do pedido.</p>
    </div>

    <div class="modal-footer" style="border-top:1px solid var(--border);padding-top:1rem;margin-top:1rem;">
      <button class="btn-secondary" onclick="closeModal('modal-negar')">Cancelar</button>
      <button onclick="confirmarNegar()" style="display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#a03030,#e05252);color:#fff;font-weight:700;font-size:.88rem;padding:.65rem 1.4rem;border-radius:10px;border:none;cursor:pointer;transition:all .2s;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <i class="fa-solid fa-ban"></i> Negar & Notificar Visitante
      </button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toast-msg"></span></div>

<script>
// ═══════════════════════════════════════════════════════════
// STATE & STORAGE
// ═══════════════════════════════════════════════════════════
let allRegs = [], allHouses = [], allPays = [], allMorPays = [];
let currentRegId = null;



function mostrarTodos(){

    document.getElementById("areaTodos").style.display = "block";

    document.getElementById("areaNovo").style.display = "none";

}


function load() {
  try { allRegs = JSON.parse(localStorage.getItem('nz_registos') || '[]'); } catch(e){ allRegs=[]; }
  try { allHouses = JSON.parse(localStorage.getItem('nz_casas') || '[]'); } catch(e){ allHouses=[]; }
  try { allPays = JSON.parse(localStorage.getItem('nz_pagamentos') || '[]'); } catch(e){ allPays=[]; }
  try { allMorPays = JSON.parse(localStorage.getItem('nz_mor_pays') || '[]'); } catch(e){ allMorPays=[]; }
}
function save() {
  localStorage.setItem('nz_registos', JSON.stringify(allRegs));
  localStorage.setItem('nz_casas', JSON.stringify(allHouses));
  localStorage.setItem('nz_pagamentos', JSON.stringify(allPays));
  localStorage.setItem('nz_mor_pays', JSON.stringify(allMorPays));
}

// ═══════════════════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════════════════
window.onload = () => {
  load();
  clock();
  setInterval(clock, 1000);

  const now = new Date();
  document.getElementById('dash-date').textContent = now.toLocaleDateString('pt-AO', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
  document.getElementById('rel-mes').value = now.getMonth();
  document.getElementById('rel-ano').value = now.getFullYear().toString();

  // Hide month/year selectors initially (day mode)
  document.getElementById('rel-mes').style.display = 'none';
  document.getElementById('rel-ano').style.display = 'none';

  seedDemoData();
  renderDashboard();
  renderPedidos();
  updateBadgePedidos();
  renderRegistos();
  renderHouses();
  renderPays();
  renderMorPays();
  renderPayHistory();
  buildReport();
  initCharts();
};

function seedDemoData() {
  if (allHouses.length === 0) {
    const blocos = ['A','B','C'];
    let id = 1;
    blocos.forEach(b => {
      for (let i = 1; i <= 4; i++) {
        allHouses.push({ id: id++, bloco: b, rua: 'Rua ' + b, numero: String(i * 10 + Math.floor(Math.random()*5)), tipo: 'V3', andar: 'R/C', zona: 'Zona 1', estado: i<=2 ? 'disponivel' : 'ocupada', obs: '' });
      }
    });
    save();
  }
  if (allMorPays.length === 0) {
    const moradores = ['João Manuel – Apt 4B', 'Ana Fernandes – Casa 12', 'Pedro Lopes – Apt 2A'];
    const tipos = ['Renda Mensal','Cota de Condomínio','Prestação de Compra'];
    const vals = [150000, 14000, 1000000];
    moradores.forEach((m, i) => {
      allMorPays.push({ id: Date.now()+i, nome: m.split('–')[0].trim(), apt: m.split('–')[1].trim(), tipo: tipos[i], valor: vals[i], metodo: 'Transferência Bancária', data: new Date(2025, 4, i+3).toLocaleDateString('pt-AO'), estado: i===0 ? 'pendente':'confirmado' });
    });
    save();
  }
}

// ═══════════════════════════════════════════════════════════
// NAVIGATION
// ═══════════════════════════════════════════════════════════
function switchTab(id, btn) {
  document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  if (btn) btn.classList.add('active');
  if (id === 'relatorio') buildReport();
  if (id === 'pedidos') renderPedidos();
  if (id === 'moradores') { renderMorPays(); renderPayHistory(); }
}
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// ═══════════════════════════════════════════════════════════
// CLOCK
// ═══════════════════════════════════════════════════════════
function clock() {
  const now = new Date();
  document.getElementById('clock-display').textContent = now.toLocaleTimeString('pt-AO');
}

// ═══════════════════════════════════════════════════════════
// DASHBOARD
// ═══════════════════════════════════════════════════════════
function renderDashboard() {
  load();
  document.getElementById('ds-total-reg').textContent = allRegs.length;
  const totalPay = [...allPays, ...allMorPays].reduce((s,p) => s + (p.total||p.valor||0), 0);
  document.getElementById('ds-receitas').textContent = fmt(totalPay) + ' Kz';
  document.getElementById('ds-pendentes').textContent = allRegs.filter(r => r.status === 'pendente').length;
  const disp = allHouses.filter(h => h.estado === 'disponivel').length;
  document.getElementById('ds-casas').textContent = disp;
  document.getElementById('badge-reg').textContent = allRegs.filter(r=>r.status==='pendente').length;
  document.getElementById('badge-pay').textContent = allPays.filter(p=>p.status==='pendente').length;
  updateBadgePedidos();

  // Recent table
  const tbody = document.getElementById('recent-tbody');
  const recent = allRegs.slice(-6).reverse();
  if (!recent.length) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem registos</td></tr>'; return; }
  tbody.innerHTML = recent.map(r => `
    <tr>
      <td>${r.nome}</td>
      <td>${r.servico === 'aluguel' ? 'Arrendamento' : 'Compra'}</td>
      <td>${fmt(r.total)} Kz</td>
      <td>${r.data}</td>
      <td><span class="badge ${r.status}">${r.status}</span></td>
    </tr>
  `).join('');
}

// ═══════════════════════════════════════════════════════════
// CHARTS
// ═══════════════════════════════════════════════════════════
let chartR, chartS;
function initCharts() {
  const months = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
  const now = new Date();
  const labels = [];
  const data = [];
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    labels.push(months[d.getMonth()]);
    const mo = d.getMonth(), yr = d.getFullYear();
    const sum = [...allPays,...allMorPays].filter(p => {
      const dd = new Date(p.data || p.hora || '2025');
      return dd.getMonth()===mo && dd.getFullYear()===yr;
    }).reduce((s,p)=>s+(p.total||p.valor||0),0);
    data.push(sum || Math.floor(Math.random()*3000000 + 500000));
  }

  const ctx1 = document.getElementById('chartReceitas').getContext('2d');
  if (chartR) chartR.destroy();
  chartR = new Chart(ctx1, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Receitas (Kz)',
        data,
        borderColor: '#c9a84c',
        backgroundColor: 'rgba(201,168,76,0.1)',
        borderWidth: 2.5,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#c9a84c',
        pointRadius: 5,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8a8070' } },
        y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8a8070', callback: v => (v/1000000).toFixed(1)+'M' } }
      }
    }
  });

  const alug = allRegs.filter(r=>r.servico==='aluguel').length;
  const comp = allRegs.filter(r=>r.servico==='compra').length;
  const ctx2 = document.getElementById('chartServicos').getContext('2d');
  if (chartS) chartS.destroy();
  chartS = new Chart(ctx2, {
    type: 'doughnut',
    data: {
      labels: ['Arrendamento', 'Compra', 'Casas Livres'],
      datasets: [{
        data: [alug||2, comp||1, allHouses.filter(h=>h.estado==='disponivel').length||5],
        backgroundColor: ['#c9a84c','#4caf7d','#5299e0'],
        borderColor: '#111', borderWidth: 3,
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: true,
      plugins: {
        legend: { position: 'bottom', labels: { color: '#8a8070', padding: 14, font: { size: 12 } } }
      },
      cutout: '62%',
    }
  });
}

// ═══════════════════════════════════════════════════════════
// REGISTOS
// ═══════════════════════════════════════════════════════════
let regFilter = 'todos';
function filterRegs(f, btn) {
  regFilter = f;
  document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  renderRegistos();
}
function renderRegistos() {
  load();
  const tbody = document.getElementById('regs-tbody');
  let regs = allRegs;
  if (regFilter !== 'todos') regs = regs.filter(r => r.status === regFilter || r.servico === regFilter);
  if (!regs.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem registos</td></tr>'; return; }
  tbody.innerHTML = regs.reverse().map(r => `
    <tr>
      <td><span class="house-tag">${r.codigo||'—'}</span></td>
      <td>${r.nome}</td>
      <td style="font-size:.8rem; font-family:monospace;">${r.bi||'—'}</td>
      <td>${r.servico==='aluguel'?'Arrendamento':'Compra'}</td>
      <td>${fmt(r.total)} Kz</td>
      <td>${r.data}</td>
      <td><span class="badge ${r.status}">${r.status}</span></td>
      <td>
        <button class="btn-secondary btn-sm" onclick="viewReg(${r.id})"><i class="fa-solid fa-eye"></i></button>
        <button class="btn-success btn-sm" style="margin-left:4px;" onclick="openAssignHouse(${r.id})" title="Atribuir Casa"><i class="fa-solid fa-house-circle-check"></i></button>
      </td>
    </tr>
  `).join('');
}

function viewReg(id) {
  const r = allRegs.find(x=>x.id===id);
  if (!r) return;
  currentRegId = id;
  const h = r.house || {};
  document.getElementById('modal-reg-body').innerHTML = `
    <div class="recibo-box">
      <div class="recibo-header">
        <div><i class="fa-solid fa-user" style="font-size:1.5rem;color:#000;"></i></div>
        <div><h3>${r.nome}</h3><p>Ref: ${r.ref||r.id}</p></div>
      </div>
      <div class="recibo-body">
        <div class="recibo-row"><span class="rk">BI</span><span class="rv">${r.bi||'—'}</span></div>
        <div class="recibo-row"><span class="rk">Telefone</span><span class="rv">${r.tel||'—'}</span></div>
        <div class="recibo-row"><span class="rk">Email</span><span class="rv">${r.email||'—'}</span></div>
        <div class="recibo-row"><span class="rk">Morada</span><span class="rv">${r.morada||'—'}</span></div>
        <div class="recibo-row"><span class="rk">Serviço</span><span class="rv gold">${r.servico==='aluguel'?'Arrendamento':'Compra'}</span></div>
        <div class="recibo-row"><span class="rk">Valor</span><span class="rv gold">${fmt(r.total)} Kz</span></div>
        <div class="recibo-row"><span class="rk">Método</span><span class="rv">${r.metodo||'—'}</span></div>
        <div class="recibo-row"><span class="rk">Casa Atribuída</span><span class="rv">${h.bloco?`Bloco ${h.bloco} · Rua ${h.rua} · Nº ${h.numero}`:'Não atribuída'}</span></div>
        <div class="recibo-row"><span class="rk">Código de Acesso</span><span class="rv" style="font-family:monospace;font-size:1.2rem;color:var(--gold);font-weight:900;">${r.codigo||'—'}</span></div>
        <div class="recibo-row"><span class="rk">Data</span><span class="rv">${r.data} ${r.hora||''}</span></div>
        <div class="recibo-row"><span class="rk">Estado</span><span class="rv"><span class="badge ${r.status}">${r.status}</span></span></div>
      </div>
    </div>
  `;
  document.getElementById('modal-reg').classList.add('open');
}

function approveReg() {
  const idx = allRegs.findIndex(x=>x.id===currentRegId);
  if (idx>=0) { allRegs[idx].status = 'aprovado'; save(); }
  closeModal('modal-reg');
  renderRegistos();
  renderDashboard();
  showToast('Registo aprovado com sucesso!');
}

// ═══════════════════════════════════════════════════════════
// CASAS
// ═══════════════════════════════════════════════════════════
function addHouse() {
  const bloco = document.getElementById('h-bloco').value.trim();
  const rua = document.getElementById('h-rua').value.trim();
  const numero = document.getElementById('h-numero').value.trim();
  if (!bloco || !rua || !numero) { showToast('Preencha Bloco, Rua e Número', true); return; }
  const house = {
    id: Date.now(), bloco, rua, numero, tipo: 'V3',
    andar: document.getElementById('h-andar').value || '—',
    zona: document.getElementById('h-zona').value || '—',
    estado: document.getElementById('h-estado').value,
    obs: document.getElementById('h-obs').value
  };
  allHouses.push(house);
  save();
  renderHouses();
  renderDashboard();
  showToast('Casa adicionada com sucesso!');
  ['h-bloco','h-rua','h-numero','h-andar','h-zona','h-obs'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('h-tipo').value = 'V3';
}
function renderHouses() {
  const tbody = document.getElementById('houses-tbody');
  if (!allHouses.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem casas</td></tr>'; return; }
  tbody.innerHTML = allHouses.map(h => `
    <tr>
      <td><span class="house-tag">${h.bloco}</span></td>
      <td>${h.rua}</td>
      <td>${h.numero}</td>
      <td>${h.tipo}</td>
      <td><span class="badge ${h.estado === 'disponivel' ? 'pago' : h.estado === 'reservada' ? 'pendente' : 'vencido'}">${h.estado}</span></td>
      <td><button class="btn-danger btn-sm" onclick="removeHouse(${h.id})"><i class="fa-solid fa-trash"></i></button></td>
    </tr>
  `).join('');
}
function removeHouse(id) {
  allHouses = allHouses.filter(h=>h.id!==id);
  save(); renderHouses();
}

// ═══════════════════════════════════════════════════════════
// ASSIGN HOUSE
// ═══════════════════════════════════════════════════════════
let pendingAssignRegId = null;
function openAssignHouse(regId) {
  pendingAssignRegId = regId;
  const r = allRegs.find(x=>x.id===regId);
  document.getElementById('attr-visitante').value = r ? r.nome : '—';
  const sel = document.getElementById('attr-casa-select');
  sel.innerHTML = '<option value="">— Seleccione —</option>';
  allHouses.filter(h=>h.estado==='disponivel').forEach(h => {
    sel.innerHTML += `<option value="${h.id}">Bloco ${h.bloco} · Rua ${h.rua} · Nº ${h.numero} · ${h.tipo}</option>`;
  });
  document.getElementById('attr-house-preview').style.display = 'none';
  sel.onchange = () => {
    if (sel.value) {
      const c = String(Math.floor(1000 + Math.random()*9000));
      document.getElementById('attr-codigo-display').textContent = c;
      document.getElementById('attr-house-preview').style.display = 'block';
      sel._codigo = c;
    } else {
      document.getElementById('attr-house-preview').style.display = 'none';
    }
  };
  document.getElementById('modal-house').classList.add('open');
}
function assignHouse() {
  const sel = document.getElementById('attr-casa-select');
  if (!sel.value) { showToast('Seleccione uma casa', true); return; }
  const house = allHouses.find(h=>h.id===parseInt(sel.value));
  const codigo = sel._codigo || String(Math.floor(1000+Math.random()*9000));
  if (house) { house.estado = 'ocupada'; }
  const idx = allRegs.findIndex(x=>x.id===pendingAssignRegId);
  if (idx>=0) {
    allRegs[idx].house = { bloco: house.bloco, rua: house.rua, numero: house.numero, tipo: house.tipo, andar: house.andar, zona: house.zona };
    allRegs[idx].codigo = codigo;
    allRegs[idx].status = 'aprovado';
    allRegs[idx].aprovadoEm = new Date().toLocaleString('pt-AO');
  }
  // Save for visitante page to read
  localStorage.setItem('nz_pending_house', JSON.stringify({ bloco: house.bloco, rua: house.rua, numero: house.numero, tipo: house.tipo, andar: house.andar, zona: house.zona, codigo }));
  save();
  renderHouses(); renderRegistos(); renderDashboard();
  closeModal('modal-house');
  showToast('Casa atribuída! Código enviado ao visitante.');
}

// ═══════════════════════════════════════════════════════════
// PAGAMENTOS VISITANTES
// ═══════════════════════════════════════════════════════════
function renderPays() {
  const tbody = document.getElementById('pays-tbody');
  const pays = [...allPays].reverse();
  if (!pays.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem pagamentos registados</td></tr>'; return; }
  tbody.innerHTML = pays.map(p => `
    <tr>
      <td style="font-size:.78rem;font-family:monospace;">${p.ref||p.id}</td>
      <td>${p.nome}</td>
      <td>${p.servico==='aluguel'?'Arrendamento':'Compra'}</td>
      <td><strong>${fmt(p.total)} Kz</strong></td>
      <td>${p.metodo||'—'}</td>
      <td>${p.data}</td>
      <td><span class="badge ${p.status||'pendente'}">${p.status||'pendente'}</span></td>
      <td><button class="btn-success btn-sm" onclick="confirmPay(${p.id})"><i class="fa-solid fa-check"></i> Confirmar</button></td>
    </tr>
  `).join('');
}
function confirmPay(id) {
  const idx = allPays.findIndex(p=>p.id===id);
  if (idx>=0) { allPays[idx].status = 'pago'; save(); renderPays(); showToast('Pagamento confirmado!'); }
}

// ═══════════════════════════════════════════════════════════
// PAGAMENTOS MORADORES
// ═══════════════════════════════════════════════════════════
function addMoradorPay() {
  const nome = document.getElementById('mor-nome').value.trim();
  const apt = document.getElementById('mor-apt').value.trim();
  const valor = parseFloat(document.getElementById('mor-valor').value);
  if (!nome || !apt || !valor) { showToast('Preencha os campos obrigatórios', true); return; }
  const pay = {
    id: Date.now(), nome, apt,
    tipo: document.getElementById('mor-tipo').value,
    valor,
    metodo: document.getElementById('mor-metodo').value,
    data: document.getElementById('mor-data').value || new Date().toLocaleDateString('pt-AO'),
    dataISO: document.getElementById('mor-data').value || new Date().toISOString().split('T')[0],
    estado: 'confirmado'
  };
  allMorPays.push(pay);
  save(); renderMorPays(); renderPayHistory(); renderDashboard();
  showToast('Pagamento do morador guardado!');
  ['mor-nome','mor-apt','mor-valor','mor-data'].forEach(id=>document.getElementById(id).value='');
}
function renderMorPays() {
  const tbody = document.getElementById('mor-tbody');
  const pays = [...allMorPays].reverse();
  if (!pays.length) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem pagamentos de moradores</td></tr>'; return; }
  tbody.innerHTML = pays.map(p => `
    <tr>
      <td>${p.nome}</td>
      <td><span class="house-tag">${p.apt}</span></td>
      <td>${p.tipo}</td>
      <td><strong>${fmt(p.valor)} Kz</strong></td>
      <td>${p.metodo}</td>
      <td>${p.data}</td>
      <td><span class="badge ${p.estado==='confirmado'?'pago':'pendente'}">${p.estado}</span></td>
    </tr>
  `).join('');
}
function renderPayHistory() {
  const container = document.getElementById('pay-history-container');
  const countEl = document.getElementById('hist-count');
  if (!container) return;
  const pays = [...allMorPays].reverse();
  if (countEl) countEl.textContent = pays.length + ' reg.';
  if (!pays.length) {
    container.innerHTML = '<div class="empty-state" style="padding:2rem 0;"><i class="fa-solid fa-receipt"></i><p>Sem pagamentos</p></div>';
    return;
  }
  const tipoIcon = { renda:'fa-home', cota:'fa-building', multa:'fa-triangle-exclamation', prestacao:'fa-coins', Renda: 'fa-home' };
  container.innerHTML = pays.map(p => {
    const iconKey = Object.keys(tipoIcon).find(k => (p.tipo||'').toLowerCase().includes(k.toLowerCase())) || 'renda';
    const icon = tipoIcon[iconKey] || 'fa-circle-dollar-to-slot';
    return `
    <div class="pay-hist-item ${p.estado||'confirmado'}">
      <div class="phi-top">
        <span class="phi-nome"><i class="fa-solid ${icon}" style="color:var(--gold);margin-right:.35rem;font-size:.8rem;"></i>${p.nome}</span>
        <span class="phi-valor">${fmt(p.valor)} Kz</span>
      </div>
      <div class="phi-bottom">
        <span><i class="fa-solid fa-house-circle-check"></i> ${p.apt}</span>
        <span><i class="fa-solid fa-tag"></i> ${p.tipo}</span>
        <span><i class="fa-solid fa-calendar-day"></i> ${p.data}</span>
        <span style="color:${p.estado==='confirmado'?'var(--success)':'var(--warn)'};">
          <i class="fa-solid fa-${p.estado==='confirmado'?'circle-check':'clock'}"></i> ${p.estado}
        </span>
      </div>
    </div>`;
  }).join('');
}

// ═══════════════════════════════════════════════════════════
// RELATÓRIO — DIÁRIO + MENSAL
// ═══════════════════════════════════════════════════════════
let relTipo = 'dia'; // 'dia' | 'mes'

function setRelTipo(tipo) {
  relTipo = tipo;
  const btnDia = document.getElementById('rel-tipo-dia');
  const btnMes = document.getElementById('rel-tipo-mes');
  const selMes = document.getElementById('rel-mes');
  const selAno = document.getElementById('rel-ano');
  if (tipo === 'dia') {
    btnDia.style.background = 'var(--gold)'; btnDia.style.color = '#000'; btnDia.style.fontWeight = '600';
    btnMes.style.background = 'transparent'; btnMes.style.color = 'var(--text-muted)'; btnMes.style.fontWeight = '400';
    selMes.style.display = 'none'; selAno.style.display = 'none';
  } else {
    btnMes.style.background = 'var(--gold)'; btnMes.style.color = '#000'; btnMes.style.fontWeight = '600';
    btnDia.style.background = 'transparent'; btnDia.style.color = 'var(--text-muted)'; btnDia.style.fontWeight = '400';
    selMes.style.display = ''; selAno.style.display = '';
  }
  buildReport();
}

async function buildReport() {
  const mes = document.getElementById('rel-mes').value;
  const ano = document.getElementById('rel-ano').value;
  const mesNome = document.getElementById('rel-mes').options[mes].text;
  const container = document.getElementById('relatorio-content');
  
  container.innerHTML = '<div class="empty-state"><i class="fa-solid fa-circle-notch fa-spin"></i><p>Gerando relatório consolidado...</p></div>';

  try {
    const res = await fetch('api/api_dashboard.php?acao=resumo');
    const data = await res.json();
    const stats = data.dados;

    const resPag = await fetch('api/api_dashboard.php?acao=pagamentos');
    const dataPag = await resPag.json();
    const pagamentos = dataPag.dados.filter(p => {
        const d = new Date(p.data_pagamento);
        return d.getMonth() == mes && d.getFullYear() == ano;
    });

    const totalArrecadado = pagamentos.reduce((acc, p) => acc + (p.estado === 'confirmado' ? parseFloat(p.valor_pago) : 0), 0);

    container.innerHTML = `
      <div class="report-header-box" style="background:var(--dark3); border-radius:15px; padding:30px; border:1px solid var(--border); margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
          <div>
            <h2 style="color:var(--gold); margin-bottom:5px;">Relatório Geral do Condomínio</h2>
            <p style="color:var(--text-muted); font-size:0.9rem;">Período: ${mesNome} de ${ano}</p>
          </div>
          <div style="text-align:right;">
             <p style="font-weight:600;">Status: <span class="badge pago">Consolidado</span></p>
             <p style="font-size:0.8rem; color:var(--text-muted);">Emitido em: ${new Date().toLocaleString()}</p>
          </div>
        </div>
        
        <div class="stat-grid" style="margin-top:25px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
          <div class="stat-card" style="background:var(--dark4);">
            <p class="stat-label">Total de Casas</p>
            <p class="stat-value">${stats.total_apartamentos}</p>
            <p class="stat-hint">${stats.apartamentos_ocupados} Ocupadas / ${stats.apartamentos_disponiveis} Disponíveis</p>
          </div>
          <div class="stat-card" style="background:var(--dark4);">
            <p class="stat-label">Total de Moradores</p>
            <p class="stat-value">${stats.total_moradores}</p>
          </div>
          <div class="stat-card" style="background:var(--dark4);">
            <p class="stat-label">Colaboradores/Staff</p>
            <p class="stat-value">${stats.total_admins}</p>
          </div>
          <div class="stat-card green" style="background:var(--dark4);">
            <p class="stat-label">Receita do Período</p>
            <p class="stat-value">${fmt(totalArrecadado)} Kz</p>
          </div>
        </div>
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
        <div class="card">
          <div class="card-head"><p class="card-title">Localização & Contactos</p></div>
          <div style="padding:20px;">
             <p><strong>Localização:</strong> ${stats.localizacao}</p>
             <p><strong>Apoio ao Cliente:</strong> ${stats.apoio}</p>
             <p><strong>NIF:</strong> 5000456789</p>
          </div>
        </div>
        <div class="card">
          <div class="card-head"><p class="card-title">Resumo de Pagamentos</p></div>
          <div style="padding:20px;">
             <p><strong>Confirmados:</strong> ${pagamentos.filter(p=>p.estado==='confirmado').length}</p>
             <p><strong>Pendentes:</strong> ${pagamentos.filter(p=>p.estado==='pendente').length}</p>
             <p><strong>Total Transações:</strong> ${pagamentos.length}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><p class="card-title">Detalhamento Financeiro do Mês</p></div>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>Morador</th>
                <th>Residência</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Método</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              ${pagamentos.length ? pagamentos.map(p => `
                <tr>
                  <td>${p.morador}</td>
                  <td>${p.apartamento}</td>
                  <td>${fmt(p.valor_pago)} Kz</td>
                  <td>${p.data_pagamento}</td>
                  <td>${p.metodo}</td>
                  <td><span class="badge ${p.estado==='confirmado'?'pago':'pendente'}">${p.estado}</span></td>
                </tr>
              `).join('') : '<tr><td colspan="6" style="text-align:center;">Nenhum pagamento registrado no período.</td></tr>'}
            </tbody>
          </table>
        </div>
      </div>
    `;
  } catch (err) {
    container.innerHTML = `<div class="empty-state error"><p>Erro ao carregar relatório: ${err.message}</p></div>`;
  }
}

function buildDayReport() {
  const today = new Date();
  const todayStr = today.toLocaleDateString('pt-AO');
  const todayISO = today.toISOString().split('T')[0];

  function isToday(p) {
    const d = p.data || '';
    return d === todayStr || d === todayISO || (p.dataISO && p.dataISO === todayISO);
  }

  const rVis = allPays.filter(isToday);
  const rMor = allMorPays.filter(isToday);
  const totalVis = rVis.reduce((s,p)=>s+(p.total||0),0);
  const totalMor = rMor.reduce((s,p)=>s+(p.valor||0),0);
  const totalDia = totalVis + totalMor;

  // Pagamentos do mês também
  const mes = today.getMonth(), ano = today.getFullYear();
  const rVisMes = allPays.filter(p => { try { const d = new Date(p.data||''); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;} });
  const rMorMes = allMorPays.filter(p => { try { const d = new Date(p.data||''); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;} });
  const totalMes = rVisMes.reduce((s,p)=>s+(p.total||0),0) + rMorMes.reduce((s,p)=>s+(p.valor||0),0);
  const mesNome = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'][mes];

  document.getElementById('relatorio-content').innerHTML = `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-calendar-day"></i> Relatório do Dia — ${todayStr}</p>
      <div class="day-summary-bar">
        <div class="day-kpi"><div class="dk-val">${fmt(totalDia)} Kz</div><div class="dk-label">Total Recebido Hoje</div></div>
        <div class="day-kpi"><div class="dk-val">${rVis.length + rMor.length}</div><div class="dk-label">Transacções Hoje</div></div>
        <div class="day-kpi"><div class="dk-val" style="color:var(--info);">${fmt(totalMes)} Kz</div><div class="dk-label">Total do Mês (${mesNome})</div></div>
      </div>
    </div>

    ${(rVis.length || rMor.length) ? `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-receipt"></i> Pagamentos Recebidos Hoje</p>
      <div class="card" style="overflow:auto; margin-bottom:1rem;">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-users"></i> Moradores — ${rMor.length} transacções</p></div>
        <table class="data-table">
          <thead><tr><th>Morador</th><th>Apt</th><th>Tipo</th><th>Valor</th><th>Método</th><th>Estado</th></tr></thead>
          <tbody>
            ${rMor.length ? rMor.map(p=>`<tr><td><strong>${p.nome}</strong></td><td><span style="font-family:monospace;font-size:.8rem;">${p.apt}</span></td><td>${p.tipo}</td><td><strong style="color:var(--success);">${fmt(p.valor)} Kz</strong></td><td>${p.metodo}</td><td><span class="badge ${p.estado==='confirmado'?'pago':'pendente'}">${p.estado}</span></td></tr>`).join('') : '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:1rem;">Sem pagamentos de moradores hoje</td></tr>'}
          </tbody>
        </table>
      </div>
      <div class="card" style="overflow:auto;">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-user-clock"></i> Visitantes — ${rVis.length} transacções</p></div>
        <table class="data-table">
          <thead><tr><th>Cliente</th><th>Serviço</th><th>Valor</th><th>Método</th><th>Estado</th></tr></thead>
          <tbody>
            ${rVis.length ? rVis.map(p=>`<tr><td><strong>${p.nome}</strong></td><td>${p.servico==='aluguel'?'Arrendamento':'Compra'}</td><td><strong style="color:var(--success);">${fmt(p.total)} Kz</strong></td><td>${p.metodo||'—'}</td><td><span class="badge ${p.status||'pendente'}">${p.status||'pendente'}</span></td></tr>`).join('') : '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:1rem;">Sem pagamentos de visitantes hoje</td></tr>'}
          </tbody>
        </table>
      </div>
    </div>` : `<div class="empty-state"><i class="fa-solid fa-moon"></i><p>Nenhum pagamento recebido hoje</p></div>`}

    <div style="background:var(--dark3);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;margin-top:1rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:.75rem;">
        <span style="color:var(--text-muted);">Total Recebido Hoje (${todayStr})</span>
        <strong style="font-size:1.3rem;color:var(--gold);">${fmt(totalDia)} Kz</strong>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;">
        <span style="color:var(--text-muted);">Acumulado do Mês (${mesNome} ${ano})</span>
        <strong style="font-size:1.1rem;color:var(--info);">${fmt(totalMes)} Kz</strong>
      </div>
      <div style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:.75rem;">
        Relatório gerado em ${new Date().toLocaleString('pt-AO')} · Condomínio Nosso Zimbo
      </div>
    </div>
  `;
}

function buildMonthReport() {
  const mes = parseInt(document.getElementById('rel-mes').value);
  const ano = parseInt(document.getElementById('rel-ano').value);
  const mesNome = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'][mes];

  const rVis = allPays.filter(p => {
    try { const d = new Date(p.data||'2025'); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;}
  });
  const rMor = allMorPays.filter(p => {
    try { const d = new Date(p.data||'2025'); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;}
  });

  const totalVis = rVis.reduce((s,p)=>s+(p.total||0),0);
  const totalMor = rMor.reduce((s,p)=>s+(p.valor||0),0);
  const totalGeral = totalVis + totalMor;

  const reg_mes = allRegs.filter(r => {
    try { const d = new Date(r.data||'2025'); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;}
  });

  document.getElementById('relatorio-content').innerHTML = `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-calendar-days"></i> Resumo Mensal — ${mesNome} ${ano}</p>
      <div class="report-kpi-grid">
        <div class="report-kpi"><div class="kval">${fmt(totalGeral)} Kz</div><div class="klabel">Receita Total do Mês</div></div>
        <div class="report-kpi"><div class="kval">${fmt(totalVis)} Kz</div><div class="klabel">Recebido de Visitantes</div></div>
        <div class="report-kpi"><div class="kval">${fmt(totalMor)} Kz</div><div class="klabel">Recebido de Moradores</div></div>
        <div class="report-kpi"><div class="kval">${reg_mes.length}</div><div class="klabel">Novos Registos</div></div>
        <div class="report-kpi"><div class="kval">${rVis.length + rMor.length}</div><div class="klabel">Transacções</div></div>
        <div class="report-kpi"><div class="kval">${allHouses.filter(h=>h.estado==='disponivel').length}</div><div class="klabel">Casas Disponíveis</div></div>
      </div>
    </div>

    ${rMor.length ? `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-users"></i> Pagamentos de Moradores — ${mesNome}</p>
      <div class="card" style="overflow:auto;">
        <table class="data-table">
          <thead><tr><th>#</th><th>Morador</th><th>Apt</th><th>Tipo</th><th>Valor</th><th>Método</th><th>Data</th><th>Estado</th></tr></thead>
          <tbody>
            ${rMor.map((p,i)=>`<tr><td style="color:var(--text-muted);font-size:.78rem;">${i+1}</td><td><strong>${p.nome}</strong></td><td><span style="font-family:monospace;font-size:.8rem;">${p.apt}</span></td><td>${p.tipo}</td><td><strong>${fmt(p.valor)} Kz</strong></td><td>${p.metodo}</td><td>${p.data}</td><td><span class="badge ${p.estado==='confirmado'?'pago':'pendente'}">${p.estado}</span></td></tr>`).join('')}
            <tr style="border-top:2px solid var(--border);"><td colspan="4" style="text-align:right;color:var(--text-muted);font-size:.8rem;font-weight:600;">SUBTOTAL MORADORES</td><td colspan="4"><strong style="color:var(--gold);font-size:.95rem;">${fmt(totalMor)} Kz</strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>` : ''}

    ${reg_mes.length ? `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-user-clock"></i> Registos de Visitantes — ${mesNome}</p>
      <div class="card" style="overflow:auto;">
        <table class="data-table">
          <thead><tr><th>#</th><th>Nome</th><th>Serviço</th><th>Valor</th><th>Data</th><th>Estado</th></tr></thead>
          <tbody>
            ${reg_mes.map((r,i)=>`<tr><td style="color:var(--text-muted);font-size:.78rem;">${i+1}</td><td>${r.nome}</td><td>${r.servico==='aluguel'?'Arrendamento':'Compra'}</td><td>${fmt(r.total)} Kz</td><td>${r.data}</td><td><span class="badge ${r.status}">${r.status}</span></td></tr>`).join('')}
            <tr style="border-top:2px solid var(--border);"><td colspan="2" style="text-align:right;color:var(--text-muted);font-size:.8rem;font-weight:600;">SUBTOTAL VISITANTES</td><td colspan="4"><strong style="color:var(--gold);font-size:.95rem;">${fmt(totalVis)} Kz</strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>` : ''}

    ${(!reg_mes.length && !rMor.length) ? '<div class="empty-state"><i class="fa-solid fa-file-circle-xmark"></i><p>Sem dados para o período seleccionado</p></div>' : ''}

    <div style="background:var(--dark3);border:2px solid var(--gold);border-radius:var(--radius);padding:1.25rem;margin-top:1rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:.5rem;">
        <span style="color:var(--text-muted);">Moradores</span>
        <strong style="color:var(--text);">${fmt(totalMor)} Kz</strong>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:.75rem;">
        <span style="color:var(--text-muted);">Visitantes</span>
        <strong style="color:var(--text);">${fmt(totalVis)} Kz</strong>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:700;color:var(--text);">Total Geral — ${mesNome} ${ano}</span>
        <strong style="font-size:1.4rem;color:var(--gold);">${fmt(totalGeral)} Kz</strong>
      </div>
      <div style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:.75rem;">
        Relatório gerado em ${new Date().toLocaleString('pt-AO')} · Condomínio Nosso Zimbo
      </div>
    </div>
    <div class="pdf-report-footer" style="display:none;margin-top:2rem;padding-top:1rem;border-top:2px solid #c9a84c;font-size:.78rem;color:#666;text-align:center;">
      Este relatório é de carácter confidencial e destina-se exclusivamente à administração do Condomínio Nosso Zimbo.<br>
      Emitido em ${new Date().toLocaleString('pt-AO')} · Página 1
    </div>
  `;
}

function imprimirRelatorio() {
  // Set print date
  const el = document.getElementById('pdf-gen-date');
  if (el) el.textContent = new Date().toLocaleString('pt-AO');
  window.print();
}

// ═══════════════════════════════════════════════════════════
// PEDIDOS DE VISITANTES
// ═══════════════════════════════════════════════════════════
let pedidoFilter = 'todos';
let currentPedidoId = null;
let selectedCasaId = null;
let geradoCodigoPedido = null;

function filterPedidos(f, btn) {
  pedidoFilter = f;
  document.querySelectorAll('#tab-pedidos .filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderPedidos();
}

function loadPedidos() {
  load();
  renderPedidos();
  updateBadgePedidos();
}

function updateBadgePedidos() {
  const pending = allRegs.filter(r => r.status === 'pendente').length;
  const badge = document.getElementById('badge-pedidos');
  if (badge) badge.textContent = pending;
}

function renderPedidos() {
  load();
  const tbody = document.getElementById('pedidos-tbody');
  let regs = [...allRegs].reverse();
  if (pedidoFilter === 'pendente') regs = regs.filter(r => r.status === 'pendente');
  else if (pedidoFilter === 'aprovado') regs = regs.filter(r => r.status === 'aprovado');
  else if (pedidoFilter === 'rejeitado') regs = regs.filter(r => r.status === 'rejeitado');

  if (!regs.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2.5rem;"><i class="fa-solid fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem;color:var(--border);"></i>Nenhum pedido encontrado</td></tr>';
    return;
  }
  tbody.innerHTML = regs.map(r => `
    <tr>
      <td><span class="house-tag" style="font-size:.72rem;">${r.ref || ('NZ-' + r.id)}</span></td>
      <td><strong>${r.nome || '—'}</strong></td>
      <td style="font-family:monospace;font-size:.8rem;">${r.bi || '—'}</td>
      <td>${r.servico === 'aluguel' ? '🏠 Arrendamento' : '🔑 Compra'}</td>
      <td><strong>${fmt(r.total || 0)} Kz</strong></td>
      <td style="font-size:.82rem;">${r.data || '—'}</td>
      <td><span class="badge ${r.status || 'pendente'}">${r.status || 'pendente'}</span></td>
      <td>
        <button class="btn-secondary btn-sm" onclick="abrirModalPedido(${r.id})" title="Rever pedido">
          <i class="fa-solid fa-magnifying-glass"></i> Rever
        </button>
      </td>
    </tr>
  `).join('');
}

function abrirModalPedido(id) {
  load();
  const r = allRegs.find(x => x.id === id);
  if (!r) return;
  currentPedidoId = id;
  selectedCasaId = null;
  geradoCodigoPedido = null;

  // Reset checkboxes
  ['chk-bi','chk-tel','chk-pay','chk-valor','chk-morada','chk-docs'].forEach(c => {
    const el = document.getElementById(c);
    if (el) el.checked = false;
  });
  const notasEl = document.getElementById('pd-notas-admin');
  if (notasEl) notasEl.value = r.notasAdmin || '';

  // Preencher dados pessoais
  document.getElementById('pd-nome-header').textContent = r.nome || '—';
  document.getElementById('pd-ref-header').textContent = 'Ref: ' + (r.ref || r.id);
  const badge = document.getElementById('pd-status-badge');
  badge.textContent = r.status || 'pendente';
  badge.className = 'badge ' + (r.status || 'pendente');

  document.getElementById('pd-nome').textContent = r.nome || '—';
  document.getElementById('pd-bi').textContent = r.bi || '—';
  document.getElementById('pd-nasc').textContent = r.nasc || '—';
  document.getElementById('pd-nac').textContent = r.nac || '—';
  document.getElementById('pd-estado-civil').textContent = r.estado || '—';
  document.getElementById('pd-tel').textContent = r.tel || '—';
  document.getElementById('pd-email').textContent = r.email || '—';
  document.getElementById('pd-morada').textContent = r.morada || '—';
  document.getElementById('pd-profissao').textContent = r.profissao || '—';
  document.getElementById('pd-data').textContent = (r.data || '—') + ' ' + (r.hora || '');

  // Preencher serviço
  const svcNome = r.servico === 'aluguel' ? 'Arrendamento' : 'Compra de Residência';
  document.getElementById('pd-servico-header').textContent = svcNome;
  document.getElementById('pd-servico').textContent = svcNome;
  document.getElementById('pd-modalidade').textContent = r.modalidade || '—';
  document.getElementById('pd-metodo').textContent = r.metodo || '—';
  document.getElementById('pd-valor').textContent = fmt(r.total || 0) + ' Kz';
  document.getElementById('pd-data2').textContent = (r.data || '—') + ' ' + (r.hora || '');
  document.getElementById('pd-codigo').textContent = r.codigo || '—';

  // Preencher select de casas
  const sel = document.getElementById('pd-casa-select');
  sel.innerHTML = '<option value="">— Seleccione uma residência —</option>';
  const disponiveis = allHouses.filter(h => h.estado === 'disponivel');
  if (disponiveis.length) {
    disponiveis.forEach(h => {
      sel.innerHTML += `<option value="${h.id}">Bloco ${h.bloco} · Rua ${h.rua} · Nº ${h.numero} · ${h.tipo} · ${h.zona}</option>`;
    });
    document.getElementById('pd-sem-casas').style.display = 'none';
    sel.style.display = 'block';
  } else {
    document.getElementById('pd-sem-casas').style.display = 'block';
    sel.style.display = 'none';
  }
  document.getElementById('pd-casa-preview').style.display = 'none';

  // Botões conforme estado
  const btnAprovar = document.getElementById('btn-aprovar-final');
  const btnRejeitar = document.getElementById('btn-rejeitar');
  const btnEliminar = document.getElementById('btn-eliminar');
  if (r.status === 'aprovado') {
    btnAprovar.disabled = true;
    btnAprovar.innerHTML = '<i class="fa-solid fa-check-double"></i> Já aprovado';
    btnRejeitar.style.display = 'none';
    btnEliminar.style.display = 'none';
  } else if (r.status === 'rejeitado') {
    btnAprovar.disabled = true;
    btnAprovar.innerHTML = '<i class="fa-solid fa-ban"></i> Negado';
    btnRejeitar.style.display = 'none';
    btnEliminar.style.display = 'inline-flex';
  } else {
    btnAprovar.disabled = false;
    btnAprovar.innerHTML = '<i class="fa-solid fa-circle-check"></i> Confirmar & Atribuir Residência';
    btnRejeitar.style.display = 'inline-flex';
    btnEliminar.style.display = 'inline-flex';
  }

  // Load document images
  function setDocImg(imgId, emptyId, src) {
    const img = document.getElementById(imgId);
    const empty = document.getElementById(emptyId);
    if (src) {
      img.src = src; img.style.display = 'inline-block'; empty.style.display = 'none';
    } else {
      img.src = ''; img.style.display = 'none'; empty.style.display = 'block';
    }
  }
  setDocImg('doc-selfie-img', 'doc-selfie-empty', r.selfie || null);
  setDocImg('doc-bi-frente-img', 'doc-bi-frente-empty', r.biFrenteImg || null);
  setDocImg('doc-bi-verso-img', 'doc-bi-verso-empty', r.biVersoImg || null);
  // Comprovativo — could be image or PDF base64
  const compImg = document.getElementById('doc-comprovativo-img');
  const compPdf = document.getElementById('doc-comprovativo-pdf-link');
  const compEmpty = document.getElementById('doc-comprovativo-empty');
  if (r.comprovantivoImg) {
    if (r.comprovantivoImg.startsWith('data:application/pdf')) {
      compImg.style.display = 'none';
      compPdf.style.display = 'block';
      document.getElementById('doc-comprovativo-pdf-a').href = r.comprovantivoImg;
      compEmpty.style.display = 'none';
    } else {
      compImg.src = r.comprovantivoImg; compImg.style.display = 'inline-block';
      compPdf.style.display = 'none'; compEmpty.style.display = 'none';
    }
  } else {
    compImg.src = ''; compImg.style.display = 'none';
    compPdf.style.display = 'none'; compEmpty.style.display = 'block';
  }

  switchPTab('dados');
  document.getElementById('modal-pedido').classList.add('open');
}

function switchPTab(tab) {
  ['dados','docs','servico','casa'].forEach(t => {
    const panel = document.getElementById('ppanel-' + t);
    if (panel) panel.style.display = 'none';
    const btn = document.getElementById('ptab-' + t);
function previewCasaSelecionada() {
  const sel = document.getElementById('pd-casa-select');
  const houseId = parseInt(sel.value);
  if (!houseId) {
    document.getElementById('pd-casa-preview').style.display = 'none';
    selectedCasaId = null;
    return;
  }
  const house = allHouses.find(h => h.id === houseId);
  if (!house) return;
  selectedCasaId = houseId;
  geradoCodigoPedido = String(Math.floor(1000 + Math.random() * 9000));

  document.getElementById('pv-bloco').textContent = house.bloco || '—';
  document.getElementById('pv-rua').textContent = house.rua || '—';
  document.getElementById('pv-numero').textContent = house.numero || '—';
  document.getElementById('pv-tipo').textContent = house.tipo || 'V3';
  document.getElementById('pv-andar').textContent = house.andar || '—';
  document.getElementById('pv-zona').textContent = house.zona || '—';
  document.getElementById('pd-novo-codigo').textContent = geradoCodigoPedido;
  document.getElementById('pd-casa-preview').style.display = 'block';
}

function aprovarPedidoFinal() {
  if (!selectedCasaId) {
    // Ir para aba de casa e avisar
    switchPTab('casa');
    showToast('Seleccione uma residência para atribuir antes de aprovar', true);
    return;
  }
  const r = allRegs.find(x => x.id === currentPedidoId);
  if (!r) return;
  const house = allHouses.find(h => h.id === selectedCasaId);
  if (!house) { showToast('Casa não encontrada', true); return; }

  // Guardar notas do admin
  r.notasAdmin = document.getElementById('pd-notas-admin')?.value || '';

  // Actualizar registo
  const idx = allRegs.findIndex(x => x.id === currentPedidoId);
  allRegs[idx].status = 'aprovado';
  allRegs[idx].codigo = geradoCodigoPedido;
  allRegs[idx].house = { bloco: house.bloco, rua: house.rua, numero: house.numero, tipo: house.tipo, andar: house.andar, zona: house.zona };
  allRegs[idx].notasAdmin = r.notasAdmin;
  allRegs[idx].aprovadoEm = new Date().toLocaleString('pt-AO');

  // Marcar casa como ocupada
  const hIdx = allHouses.findIndex(h => h.id === selectedCasaId);
  if (hIdx >= 0) allHouses[hIdx].estado = 'ocupada';

  // Guardar dados para o portal do visitante
  try {
    localStorage.setItem('nz_pending_house', JSON.stringify({
      bloco: house.bloco, rua: house.rua, numero: house.numero,
      tipo: house.tipo, andar: house.andar, zona: house.zona,
      codigo: geradoCodigoPedido,
      visitante: r.nome,
      aprovadoEm: new Date().toLocaleString('pt-AO')
    }));
  } catch(e) {}

  save();
  closeModal('modal-pedido');

  // Modal de confirmação
  document.getElementById('conf-nome').textContent = r.nome || '—';
  document.getElementById('conf-casa').textContent = `Bloco ${house.bloco} · Rua ${house.rua} · Nº ${house.numero}`;
  document.getElementById('conf-codigo').textContent = geradoCodigoPedido;
  document.getElementById('modal-confirmacao').classList.add('open');

  renderPedidos();
  renderRegistos();
  renderDashboard();
  updateBadgePedidos();
}

function abrirNegarPedido() {
  if (!currentPedidoId) return;
  // Reset modal
  document.getElementById('negar-motivo-texto').value = '';
  document.querySelectorAll('.negar-reason-btn').forEach(b => b.classList.remove('selected'));
  closeModal('modal-pedido');
  document.getElementById('modal-negar').classList.add('open');
}

<script>
// --- CONFIGURAÇÃO GLOBAL ---
const API_URL = 'api/api_dashboard.php';
let chartReceitas = null;
let chartServicos = null;

// --- UTILITÁRIOS ---
const fmt = (v) => new Intl.NumberFormat('pt-AO').format(v || 0);

function showToast(msg, isError = false) {
    const t = document.createElement('div');
    t.className = `toast ${isError ? 'error' : ''} show`;
    t.innerHTML = `<i class="fa-solid fa-${isError ? 'circle-xmark' : 'circle-check'}"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, 3000);
}

// --- NAVEGAÇÃO ---
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    
    const target = document.getElementById('tab-' + tabId);
    if (target) {
        target.classList.add('active');
        if (btn) btn.classList.add('active');
        
        // Refresh data specific to the tab
        if (tabId === 'dashboard') loadDashboard();
        if (tabId === 'pedidos') carregarAdmins();
        if (tabId === 'registos') carregarMoradores();
        if (tabId === 'casas') carregarCasas();
        if (tabId === 'pagamentos') carregarVisitas();
        if (tabId === 'moradores') carregarPagamentos();
        if (tabId === 'relatorio') buildReport();
        if (tabId === 'comunicacao') loadComunicacao();
    }
}

// --- DASHBOARD & GRÁFICOS ---
async function loadDashboard() {
    try {
        const res = await fetch(`${API_URL}?acao=resumo`);
        const data = await res.json();
        if (data.sucesso) {
            const s = data.dados;
            const updateText = (id, val) => {
                const el = document.getElementById(id);
                if(el) el.textContent = val;
            };
            updateText('ds-total-reg', s.total_moradores + s.total_admins);
            updateText('ds-receitas', fmt(s.receitas_mes) + ' Kz');
            updateText('ds-pendentes', s.mensalidades_pendentes);
            updateText('ds-casas', s.apartamentos_disponiveis);
            
            // Sidebar badges
            updateText('badge-pedidos', s.total_admins);
            updateText('badge-reg', s.total_moradores);
            updateText('badge-pay', s.mensalidades_pendentes);
            
            initCharts(s);
        }
    } catch (e) { console.error('Erro dashboard:', e); }
}

function initCharts(stats) {
    const ctx1 = document.getElementById('chartReceitas')?.getContext('2d');
    const ctx2 = document.getElementById('chartServicos')?.getContext('2d');

    if (ctx1) {
        if (chartReceitas) chartReceitas.destroy();
        chartReceitas = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [{
                    label: 'Receitas (Kz)',
                    data: [stats.receitas_mes * 0.8, stats.receitas_mes * 0.9, stats.receitas_mes * 0.85, stats.receitas_mes * 0.95, stats.receitas_mes * 1.1, stats.receitas_mes],
                    borderColor: '#b4914a',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(180, 145, 74, 0.1)'
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    }

    if (ctx2) {
        if (chartServicos) chartServicos.destroy();
        chartServicos = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Ocupados', 'Disponíveis', 'Manutenção'],
                datasets: [{
                    data: [stats.apartamentos_ocupados, stats.apartamentos_disponiveis, 2],
                    backgroundColor: ['#2563eb', '#10b981', '#f59e0b']
                }]
            },
            options: { responsive: true }
        });
    }
}

// --- CRUD FUNCTIONS ---
async function carregarMoradores() {
    const tbody = document.getElementById("moradores-admin-tbody");
    if (!tbody) return;
    try {
        const res = await fetch(`${API_URL}?acao=moradores`);
        const data = await res.json();
        tbody.innerHTML = data.dados.map(m => `
            <tr>
                <td><strong>${m.nome}</strong></td>
                <td>${m.telefone}</td>
                <td>${m.email}</td>
                <td><span class="house-tag">${m.numbi || '—'}</span></td>
                <td><span class="badge ${m.estado_conta === 'Activo' ? 'pago' : 'pendente'}">${m.estado_conta}</span></td>
                <td>
                    <div style="display:flex; gap:5px;">
                      <button class="btn-success btn-sm" onclick="abrirModalProcessarMorador(${m.id}, '${m.nome}')" title="Atribuir Casa"><i class="fa-solid fa-house-user"></i></button>
                      <button class="btn-danger btn-sm" onclick="eliminarItem('morador', ${m.id})"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="6" style="text-align:center;">Nenhum morador registado.</td></tr>';
    } catch (e) { tbody.innerHTML = '<tr><td colspan="6">Erro ao carregar moradores.</td></tr>'; }
}

async function carregarCasas() {
    const tbody = document.getElementById("houses-tbody");
    if (!tbody) return;
    try {
        const res = await fetch(`${API_URL}?acao=casas`);
        const data = await res.json();
        tbody.innerHTML = data.dados.map(h => `
            <tr>
                <td><span class="house-tag">Bloco ${h.bloco}</span></td>
                <td>${h.numero}</td>
                <td>${h.tipologia}</td>
                <td>${h.andar || '0'}º</td>
                <td><span class="badge ${h.estado === 'Disponivel' ? 'pago' : 'vencido'}">${h.estado}</span></td>
                <td>${h.morador_nome || '—'}</td>
                <td>
                    <button class="btn-secondary btn-sm" onclick="alterarEstadoCasa(${h.id})" title="Alterar Estado"><i class="fa-solid fa-arrows-rotate"></i></button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="7" style="text-align:center;">Nenhuma casa registada.</td></tr>';
    } catch (e) { }
}

async function carregarAdmins() {
    const tbody = document.getElementById("funcionarios-tbody");
    if (!tbody) return;
    try {
        const res = await fetch(`${API_URL}?acao=admins`);
        const data = await res.json();
        tbody.innerHTML = data.dados.map(a => `
            <tr>
                <td><strong>${a.nome}</strong></td>
                <td>${a.funcao}</td>
                <td>${a.email}</td>
                <td><span class="badge ${a.activo == 1 ? 'pago' : 'vencido'}">${a.activo == 1 ? 'Activo' : 'Inactivo'}</span></td>
                <td>
                    <button class="btn-danger btn-sm" onclick="eliminarItem('admin', ${a.id})"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="5" style="text-align:center;">Nenhum administrador registado.</td></tr>';
    } catch (e) { }
}

async function carregarPagamentos() {
    const tbody = document.getElementById("mor-tbody");
    if (!tbody) return;
    try {
        const res = await fetch(`${API_URL}?acao=pagamentos`);
        const data = await res.json();
        tbody.innerHTML = data.dados.map(p => `
            <tr>
                <td>${p.morador}</td>
                <td><span class="house-tag">${p.apartamento}</span></td>
                <td>Confirmado</td>
                <td><strong>${fmt(p.valor_pago)} Kz</strong></td>
                <td>${p.metodo}</td>
                <td>${new Date(p.data_pagamento).toLocaleDateString()}</td>
                <td><span class="badge ${p.estado === 'confirmado' ? 'pago' : 'pendente'}">${p.estado}</span></td>
            </tr>
        `).join('') || '<tr><td colspan="7" style="text-align:center;">Nenhum pagamento registado.</td></tr>';
    } catch (e) { }
}

async function carregarVisitas() {
    const tbody = document.getElementById("pays-tbody");
    if (!tbody) return;
    try {
        const res = await fetch(`${API_URL}?acao=visitas`);
        const data = await res.json();
        tbody.innerHTML = data.dados.map(v => `
            <tr>
                <td>${v.codigo_acesso || v.id}</td>
                <td>${v.nome_visitante}</td>
                <td>Visita à ${v.apartamento}</td>
                <td>0 Kz</td>
                <td>N/A</td>
                <td>${new Date(v.data_prevista).toLocaleDateString()}</td>
                <td><span class="badge ${v.estado === 'confirmado' ? 'pago' : 'pendente'}">${v.estado}</span></td>
                <td><button class="btn-secondary btn-sm" onclick="showToast('Detalhes em breve')"><i class="fa-solid fa-eye"></i></button></td>
            </tr>
        `).join('') || '<tr><td colspan="8" style="text-align:center;">Nenhum pedido de visita registado.</td></tr>';
    } catch (e) { }
}

// --- ACTIONS ---
async function eliminarItem(tipo, id) {
    if (!confirm('Eliminar este registo permanentemente?')) return;
    const acao = tipo === 'morador' ? 'eliminar_morador' : 'eliminar_admin';
    const fd = new FormData();
    fd.append('id', id);
    try {
        const r = await fetch(`${API_URL}?acao=${acao}`, { method: 'POST', body: fd });
        const d = await r.json();
        if (d.sucesso) { showToast('Eliminado com sucesso!'); if (tipo==='morador') carregarMoradores(); else carregarAdmins(); }
        else { showToast(d.erro || 'Erro ao eliminar', true); }
    } catch(e) { showToast('Erro de conexão', true); }
}

async function salvarFuncionario() {
    const fd = new FormData();
    fd.append('nome', document.getElementById('f-nome').value);
    fd.append('email', document.getElementById('f-email').value);
    fd.append('senha', document.getElementById('f-senha').value);
    fd.append('funcao', document.getElementById('f-funcao').value);
    fd.append('telefone', document.getElementById('f-telefone').value);
    
    try {
        const r = await fetch(`${API_URL}?acao=cadastrar_admin`, { method: 'POST', body: fd });
        const d = await r.json();
        if (d.sucesso) { 
            showToast('Funcionário registado!'); 
            resetFuncForm();
            carregarAdmins(); 
        } else showToast(d.erro, true);
    } catch(e) { showToast('Erro ao salvar', true); }
}

function resetFuncForm() {
    document.getElementById('f-nome').value = '';
    document.getElementById('f-email').value = '';
    document.getElementById('f-senha').value = '';
    document.getElementById('f-telefone').value = '';
    document.getElementById('f-funcao').value = '';
}

async function salvarMorador() {
    const fd = new FormData();
    fd.append('nome', document.getElementById('m-nome').value);
    fd.append('email', document.getElementById('m-email').value);
    fd.append('senha', document.getElementById('m-senha').value);
    fd.append('telefone', document.getElementById('m-telefone').value);
    fd.append('numbi', document.getElementById('m-numbi').value);
    fd.append('nascimento', document.getElementById('m-nascimento').value);
    
    try {
        const r = await fetch(`${API_URL}?acao=cadastrar_morador`, { method: 'POST', body: fd });
        const d = await r.json();
        if (d.sucesso) { 
            showToast('Morador registado!'); 
            resetMorForm();
            carregarMoradores(); 
        } else showToast(d.erro, true);
    } catch(e) { showToast('Erro ao salvar', true); }
}

function resetMorForm() {
    ['m-nome','m-email','m-senha','m-telefone','m-numbi','m-nascimento'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.value = '';
    });
}

async function addHouse() {
    // A função é chamada pelo botão em tab-casas
    const form = document.querySelector('#tab-casas form');
    if (!form) return;
    const fd = new FormData(form);
    // Nota: O HTML original usa name="bloco" que é texto. A API espera id_bloco.
    // Vou simplificar forçando um id_bloco se o campo estiver lá.
    try {
        const r = await fetch(`${API_URL}?acao=cadastrar_casa`, { method: 'POST', body: fd });
        const d = await r.json();
        if (d.sucesso) { showToast('Casa adicionada!'); carregarCasas(); }
        else showToast(d.erro, true);
    } catch(e) {}
}

function abrirModalProcessarMorador(id, nome) {
    const idApt = prompt(`Atribuir Apartamento a ${nome}. Digite o ID numérico da casa:`);
    if (!idApt) return;
    const fd = new FormData();
    fd.append('id_morador', id);
    fd.append('id_apartamento', idApt);
    fd.append('estado', 'Activo');
    fetch(`${API_URL}?acao=processar_morador`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => { 
        if(d.sucesso) { showToast('Morador activado e casa atribuída!'); carregarMoradores(); } 
        else { showToast(d.erro, true); }
    });
}

function alterarEstadoCasa(id) {
    const novo = prompt('Novo estado (Disponivel, Ocupado, Manutencao):');
    if (!novo) return;
    showToast('Estado actualizado (simulação)');
    carregarCasas();
}

// --- RELATÓRIO ---
async function buildReport() {
    const container = document.getElementById('relatorio-content');
    if (!container) return;
    container.innerHTML = '<div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Gerando relatório consolidado...</p></div>';
    
    try {
        const res = await fetch(`${API_URL}?acao=resumo`);
        const data = await res.json();
        const s = data.dados;
        
        container.innerHTML = `
            <div class="card" style="padding: 2.5rem; text-align: center;">
                <h2 style="color:var(--gold); margin-bottom: 2rem;">Relatório Geral Administrativo</h2>
                <div class="stat-grid" style="margin-bottom: 2rem;">
                    <div class="stat-card">
                        <p class="stat-label">Total Arrecadado (Mês)</p>
                        <p class="stat-value">${fmt(s.receitas_mes)} Kz</p>
                    </div>
                    <div class="stat-card blue">
                        <p class="stat-label">Taxa de Ocupação</p>
                        <p class="stat-value">${s.total_apartamentos > 0 ? Math.round((s.apartamentos_ocupados/s.total_apartamentos)*100) : 0}%</p>
                    </div>
                </div>
                <div style="text-align: left; background: var(--dark4); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                    <p><strong>Residentes Activos:</strong> ${s.total_moradores}</p>
                    <p><strong>Casas Disponíveis:</strong> ${s.apartamentos_disponiveis}</p>
                    <p><strong>Staff em Serviço:</strong> ${s.total_admins}</p>
                </div>
                <button class="btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Exportar como PDF</button>
            </div>
        `;
    } catch (e) { container.innerHTML = '<p>Erro ao gerar relatório. Verifique o backend.</p>'; }
}

// --- COMUNICAÇÃO ---
async function loadComunicacao() {
    const list = document.getElementById('chat-users-list');
    if (!list) return;
    try {
        const res = await fetch(`${API_URL}?acao=moradores`);
        const data = await res.json();
        list.innerHTML = data.dados.map(m => `
            <div onclick="selectChatUser(${m.id}, '${m.nome}')" style="padding:15px; border-bottom:1px solid var(--border); cursor:pointer;" class="chat-user-item">
                <div style="font-weight:600;">${m.nome}</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">${m.apartamento || 'Morador pendente'}</div>
            </div>
        `).join('');
    } catch(e) {}
}

function selectChatUser(id, nome) {
    const field = document.getElementById('admin-chat-morador-id');
    const form = document.getElementById('admin-chat-form');
    if (field && form) {
        field.value = id;
        form.style.display = 'block';
        showToast('Chat aberto com ' + nome);
    }
}

// --- MODAL & HELPERS ---
function closeModal(id) {
    document.getElementById(id)?.classList.remove('open');
}

function toggleSidebar() {
    document.getElementById('sidebar')?.classList.toggle('open');
}

function switchPTab(tab) {
    document.querySelectorAll('[id^="ppanel-"]').forEach(p => p.style.display = 'none');
    document.getElementById('ppanel-' + tab).style.display = 'block';
    
    document.querySelectorAll('[id^="ptab-"]').forEach(b => {
        b.style.background = 'transparent';
        b.style.color = 'var(--text-muted)';
        b.style.fontWeight = '400';
    });
    const activeBtn = document.getElementById('ptab-' + tab);
    activeBtn.style.background = 'var(--gold)';
    activeBtn.style.color = '#000';
    activeBtn.style.fontWeight = '600';
}

function openLightbox(src, label) {
    // Implementação básica ou toast para aviso
    showToast('Ampliação de imagem em desenvolvimento');
}

// --- INICIALIZAÇÃO ---
window.onload = () => {
    loadDashboard();
    // Relógio
    setInterval(() => {
        const el = document.getElementById('clock-display');
        if (el) el.textContent = new Date().toLocaleTimeString('pt-AO');
    }, 1000);
    
    // Data
    const d = document.getElementById('dash-date');
    if (d) d.textContent = new Date().toLocaleDateString('pt-AO', {weekday:'long', day:'numeric', month:'long'});
};
};
</script>
</body>
</html>
