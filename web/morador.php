<?php
    session_start();
    include("conexao.php");

    $sqlm = "select mensalidades.servico, mensalidades.mes, mensalidades.valor, mensalidades.estado
          from mensalidades";
    $resultadom = mysqli_query($conexao, $sqlm);

    

    $sqlnomemorador = "select moradores.nome from moradores";
    $resnomemorador = mysqli_query($conexao, $sqlnomemorador);
    $nomemorador = mysqli_fetch_assoc($resnomemorador);

    //Total de Mensalidades
    $sqlmensalidade = "select SUM(valor) AS total from mensalidades";
    $resmensalidade = mysqli_query($conexao, $sqlmensalidade);
    $totalmensalidade = mysqli_fetch_assoc($resmensalidade)['total'];

    //Total de Dívidas
    $sqldivida = "select SUM(valor) AS total from dividas";
    $resdivida = mysqli_query($conexao, $sqldivida);
    $totaldivida = mysqli_fetch_assoc($resdivida)['total'];

        //Total de Mensalidades
    $sqlmensalidademes = "select COUNT(mes) AS total from mensalidades";
    $resmensalidademes = mysqli_query($conexao, $sqlmensalidademes);
    $totalmensalidademes = mysqli_fetch_assoc($resmensalidademes)['total'];

        //Total de Mensalidades
    $sqlmulta = "select SUM(valor) AS total from multas";
    $resmulta = mysqli_query($conexao, $sqlmulta);
    $totalmulta = mysqli_fetch_assoc($resmulta)['total'];

    $sqlf = "select ano, mes, valor, servico, estado from mensalidades";
    $resultadof = mysqli_query($conexao, $sqlf);

    if(isset($_POST['pagar'])){
      $id = $_POST['id'];
      $sqlpagar = "UPDATE mensalidades SET estado ='pago' where id='$id'";

      mysqli_query($conexao, $sqlpagar);
    }

?>

<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nosso Zimbo — Painel de Administração</title>
<link rel="stylesheet" href="Css/nosso-zimbo-admin.css">

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <script src="js/theme-manager.js"></script>
  <script>
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

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
    <div>
      <p class="brand-name">Nosso Zimbo</p>
      <p class="brand-sub">Dashboard - Morador</p>
    </div>
  </div>
  <nav class="sidebar-nav">
    <p class="nav-section">Fincionalidades</p>
    <button class="nav-item active" onclick="switchTab('dashboard', this)">
        <i class="fa-solid fa-gauge-high"></i> 
        <span>Visão Geral</span>
    </button>

    <button class="nav-item" onclick="switchTab('pagamentos', this)">
        <i class="fa-solid fa-money-bill-wave"></i>
        <span>Fazer Pagamentos</span>
    </button>

    <button class="nav-item" onclick="switchTab('historico', this)">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Histórico</span>
    </button>

    <button class="nav-item" onclick="switchTab('vizinhos', this)">
        <i class="fa-solid fa-users"></i>
        <span>Vizinhos</span>
    </button>
    <p class="nav-section">Relatórios</p>
    <button class="nav-item" onclick="switchTab('relatorio', this)">
      <i class="fa-solid fa-chart-pie"></i><span>Relatório Mensal</span>
    </button>
    <p class="nav-section">Ajustes</p>
    <button class="nav-item" onclick="window.location.href='pages/meu_perfil.php'">
      <i class="fa-solid fa-user-gear"></i><span>Meu Perfil</span>
    </button>
  </nav>
  <div class="sidebar-footer">
    <div class="avatar-admin">MO</div>
    <div style="flex:1;">
      <form action=""></form>
    </div>
    <a href="index.html" title="Sair" style="color:var(--text-muted); font-size:1rem;"><i class="fa-solid fa-right-from-bracket"></i></a>
  </div>
</aside>

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

<h1 class="page-title">
       <p>Olá, <?= $nomemorador['nome'] ?? 'Visitante' ?></p>
     </h1>
    <br>

    <div class="stat-grid">

<div class="stat-card">
    <div class="stat-icon">
        <i class="fa-solid fa-money-bill"></i>
    </div>

    <p class="stat-label">
        Mensalidade
    </p>

    <p class="stat-value">
        <?php echo $totalmensalidade; ?> Kz
    </p>
</div>

<div class="stat-card red">
    <div class="stat-icon">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>

    <p class="stat-label">
        Dívida
    </p>

    <p class="stat-value">
        <?php echo $totaldivida; ?> Kz
    </p>
</div>

<div class="stat-card orange">
    <div class="stat-icon">
        <i class="fa-solid fa-gavel"></i>
    </div>

    <p class="stat-label">
        Multas
    </p>

    <p class="stat-value">
        <?php echo $totalmulta; ?> Kz
    </p>
</div>

<div class="stat-card green">
    <div class="stat-icon">
        <i class="fa-solid fa-check"></i>
    </div>

    <p class="stat-label">
        Meses Pagos
    </p>

    <p class="stat-value">
        <?php echo $totalmensalidademes; ?>
    </p>
</div>

</div>
  </section>

  <!-- ── PEDIDOS DE VISITANTES ── -->
  <section class="tab-section" id="tab-pedidos">
    <div class="page-header">
      <h1 class="page-title">Cadastro de Funcionários</h1>
    </div>

</nav>

<!-- ── MAIN ── -->

  <div class="tab">
      <button class="filter-btn active" onclick="carregarMoradores()">Novo</button>
      <button class="filter-btn" onclick="filterRegs('pendente', this)">Todos</button>
    </div>

  <form action="funcionarios.php" method="POST">
  
      <h2 class="step-title">Dados Pessoais</h2>
      <div class="form-grid">
        <div class="form-group full">
          <label>Nome Completo *</label>
          <input type="text" name="nome" placeholder="Ex: Maria da Silva Santos" maxlength="60" />
        </div>
        <div class="form-group">
          <label>Senha *</label>
          <input type="password" name="senha" maxlength="60" />
        </div>
        <div class="form-group">
          <label>Telefone *</label>
          <input type="tel" name="telefone" placeholder="9XX XXX XXX" maxlength="9" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="email@exemplo.com" />
        </div>
        <div class="form-group">
          <label>Data de Nascimento *</label>
          <input type="date" name="nasc" />
        </div>
        <div class="form-group">
          <label>Nacionalidade *</label>
          <input type="text" name="nacionalidade" placeholder="Angolana" value="Angolana" />
        </div>
        <div class="form-group full">
          <label>Morada Actual *</label>
          <input type="text" name="morada" placeholder="Rua, bairro, município, província" />
        </div>
      
    <!-- STEP 2: Identificação -->
      <h2 class="step-title">Documento de Identificação</h2>
      <p class="step-desc">Para segurança do condomínio, precisamos dos seus dados de identificação. Os documentos serão enviados ao administrador.</p>
      
        <div class="form-group">
          <label>Nº do Bilhete de Identidade *</label>
          <input type="text" name="numbi" placeholder="000XXXXXX LA 000" />
        </div>
        <div class="form-group">
          <label>Data de Emissão *</label>
          <input type="date" name="emissao" />
        </div>
        <div class="form-group">
          <label>Data de Validade *</label>
          <input type="date" name="validade" />
        </div>
        <div class="form-group">
          <label>Local de Emissão *</label>
          <input type="text" name="locale" placeholder="Luanda, SAE Patriota" />
        </div>

        <div class="form-group">
          <label>Função *</label>
          <select name="funcao">
                <option value="adm">Administrador</option>
                <option value="rh">Recursos Humanos</option>
                <option value="seguranca">Segurança</option>
                <option value="at">Area Tecnica</option>
              </select>
        </div>
        <div class="form-group">
          <label>IBAN de Pagamento</label>
          <input type="text" name="iban" placeholder="AO06XXXXXXXXXXXXXXXXXXXXX">
        </div>

      <div class="reg-consent">
        <input type="checkbox" id="reg-consent-check" />
        <label for="reg-consent-check">Declaro que os dados fornecidos são verdadeiros e autorizo o Condomínio Nosso Zimbo a processar as minhas informações para fins de registo e contrato.</label>
      </div>
      <br>
        <a href="index.html" target="_blank"><button class="btn-secondary"><i class="fa-solid fa-arrow-left"></i> Cancelar</button></a>
        <a href="nosso-zimbo-admin.html" target="_blank"><button class="btn-primary" onclick="submitRegistration()"><i class="fa-solid fa-paper-plane"></i> Enviar Registo</button></a>
      
    </div>
  </form> 

  </section>

  <!-- ── CADASTRO DE MORADORES ── -->
  <section class="tab-section" id="tab-registos">
    <div class="page-header">
      <h1 class="page-title">Cadastro de Moradores</h1>
    </div>

    <!-- <div class="filter-bar"> -->
      <div class="tab">
      <button class="filter-btn active" onclick="carregarMoradores()">Novo</button>
      <button class="filter-btn" onclick="filterRegs('pendente', this)">Todos</button>
    </div>

    
      <!-- STEP 1: Dados Pessoais -->
      <form action="dadospessoais.php" method="POST">
  
      <h2 class="step-title">Dados Pessoais</h2>
      <div class="form-grid">
        <div class="form-group full">
          <label>Nome Completo *</label>
          <input type="text" name="nome" placeholder="Ex: Maria da Silva Santos" maxlength="60" />
        </div>
        <div class="form-group">
          <label>Senha *</label>
          <input type="password" name="senha" maxlength="60" />
        </div>
        <div class="form-group">
          <label>Telefone *</label>
          <input type="tel" name="telefone" placeholder="9XX XXX XXX" maxlength="9" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="email@exemplo.com" />
        </div>
        <div class="form-group">
          <label>Data de Nascimento *</label>
          <input type="date" name="nasc" />
        </div>
        <div class="form-group">
          <label>Nacionalidade *</label>
          <input type="text" name="nacionalidade" placeholder="Angolana" value="Angolana" />
        </div>
        <div class="form-group full">
          <label>Morada Actual *</label>
          <input type="text" name="morada" placeholder="Rua, bairro, município, província" />
        </div>
      
    <!-- STEP 2: Identificação -->
      <h2 class="step-title">Documento de Identificação</h2>
      <p class="step-desc">Para segurança do condomínio, precisamos dos seus dados de identificação. Os documentos serão enviados ao administrador.</p>
      
        <div class="form-group">
          <label>Nº do Bilhete de Identidade *</label>
          <input type="text" name="numbi" placeholder="000XXXXXX LA 000" />
        </div>
        <div class="form-group">
          <label>Data de Emissão *</label>
          <input type="date" name="emissao" />
        </div>
        <div class="form-group">
          <label>Data de Validade *</label>
          <input type="date" name="validade" />
        </div>
        <div class="form-group">
          <label>Local de Emissão *</label>
          <input type="text" name="locale" placeholder="Luanda, SAE Patriota" />
        </div>

      <div class="reg-consent">
        <input type="checkbox" id="reg-consent-check" />
        <label for="reg-consent-check">Declaro que os dados fornecidos são verdadeiros e autorizo o Condomínio Nosso Zimbo a processar as minhas informações para fins de registo e contrato.</label>
      </div>
      <br>
        <a href="index.html" target="_blank"><button class="btn-secondary"><i class="fa-solid fa-arrow-left"></i> Cancelar</button></a>
        <a href="nosso-zimbo-admin.html" target="_blank"><button class="btn-primary" onclick="submitRegistration()"><i class="fa-solid fa-paper-plane"></i> Enviar Registo</button></a>
      
    </div>
  </form> 
           
  </section>


  <!-- ── GESTÃO DE CASAS ── -->
  <section class="tab-section" id="tab-casas">
    <div class="page-header">
      <h1 class="page-title">Gestão de Casas</h1>
      <p class="page-sub">Adicione e gira as residências do condomínio</p>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">

      <!-- Lista de casas -->
      <div class="card">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-list"></i> Residências Registadas</p></div>
        <div style="overflow-x:auto; max-height:500px; overflow-y:auto;">
          <table class="data-table" id="houses-table">
<thead><tr><th>Bloco</th><th>Rua</th><th>Nº</th><th>Tipo</th><th>Estado</th><th>Acção</th></tr></thead>               
                <?php
                  include "vercasa.php"; 
                ?>
             
            </table>
        </div>
      </div>

    </div>
  </section>

      <!-- ── RESUMO FINANCEIRO (INFORMATIVO) ── -->
  <section class="tab-section" id="tab-pagamentos">
    <div class="page-header">
      <h1 class="page-title">💳 Resumo Financeiro</h1>
      <p class="page-sub">Informações sobre taxas, rendas e condições de compra</p>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
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

<section class="tab-section" id="tab-historico">

<div class="page-header">
<h1 class="page-title">
Histórico de Pagamentos
</h1>
</div>

<div class="card">

<table class="data-table">

<tr>
<th>Data</th>
<th>Mês</th>
<th>Valor</th>
<th>Serviço</th>
<th>Estado</th>
</tr>

<?php while($dadosf = mysqli_fetch_assoc($resultadof)){ ?>   
                <tr>
                  <td><?php echo $dadosf['ano']; ?></td> 
                  <td><?php echo $dadosf['mes']; ?></td>
                  <td><?php echo $dadosf['valor']; ?></td>
                  <td><?php echo $dadosf['servico']; ?></td>
                  <td><?php echo $dadosf['estado']; ?></td>
                </tr>
                <?php 
                } 
                ?>

</table>

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

function buildReport() {
  if (relTipo === 'dia') buildDayReport();
  else buildMonthReport();
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
    if (btn) {
      btn.style.background = 'transparent';
      btn.style.color = 'var(--text-muted)';
    }
  });
  const activePanel = document.getElementById('ppanel-' + tab);
  if (activePanel) activePanel.style.display = 'block';
  const activeBtn = document.getElementById('ptab-' + tab);
  if (activeBtn) {
    activeBtn.style.background = 'var(--gold)';
    activeBtn.style.color = '#000';
  }
}

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

function selecionarMotivo(btn, texto) {
  document.querySelectorAll('.negar-reason-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('negar-motivo-texto').value = texto;
  document.getElementById('negar-motivo-texto').focus();
}

function confirmarNegar() {
  const motivo = document.getElementById('negar-motivo-texto').value.trim();
  if (!motivo) {
    document.getElementById('negar-motivo-texto').style.borderColor = 'var(--danger)';
    document.getElementById('negar-motivo-texto').focus();
    showToast('Escreva ou seleccione o motivo da negação', true);
    return;
  }
  if (!currentPedidoId) return;
  const idx = allRegs.findIndex(x => x.id === currentPedidoId);
  if (idx >= 0) {
    allRegs[idx].status = 'rejeitado';
    allRegs[idx].motivoRejeicao = motivo;
    allRegs[idx].rejeitadoEm = new Date().toLocaleString('pt-AO');
    allRegs[idx].notasAdmin = document.getElementById('pd-notas-admin')?.value || '';
    // Guardar no localStorage para o visitante ler
    try {
      const myId = allRegs[idx].id;
      const notifKey = 'nz_notif_' + myId;
      localStorage.setItem(notifKey, JSON.stringify({
        status: 'rejeitado',
        motivo: motivo,
        rejeitadoEm: allRegs[idx].rejeitadoEm
      }));
    } catch(e) {}
  }
  save();
  closeModal('modal-negar');
  renderPedidos();
  renderDashboard();
  updateBadgePedidos();
  showToast('Pedido negado. O visitante verá o motivo no seu portal.');
}

// Legacy alias
function rejeitarPedido() { abrirNegarPedido(); }

// ── LIGHTBOX ──
function openLightbox(src, label) {
  if (!src) return;
  document.getElementById('lightbox-img').src = src;
  document.getElementById('lightbox-label').textContent = label || 'Documento';
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.getElementById('lightbox-img').src = '';
  document.body.style.overflow = '';
}
// ESC to close lightbox
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeLightbox();
    closeModal('modal-negar');
  }
});

function eliminarPedido() {
  if (!currentPedidoId) return;
  if (!confirm('Tem a certeza que deseja ELIMINAR permanentemente este pedido? Esta acção não pode ser revertida.')) return;
  allRegs = allRegs.filter(x => x.id !== currentPedidoId);
  // Remove from payments too
  allPays = allPays.filter(x => x.id !== currentPedidoId && x.id !== currentPedidoId + 1);
  save();
  closeModal('modal-pedido');
  renderPedidos();
  renderRegistos();
  renderDashboard();
  updateBadgePedidos();
  showToast('Pedido eliminado permanentemente.');
}

// ═══════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════
function fmt(n) { return new Intl.NumberFormat('pt-AO').format(n||0); }
function showToast(msg, err) {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast' + (err?' error':'') + ' show';
  setTimeout(()=>t.classList.remove('show'), 3000);
}
function triggerFile(id) { document.getElementById(id).click(); }
function fileSelected(id, input) {
  const area = document.getElementById(id + '-area');
  if (input.files[0]) { area.classList.add('uploaded'); area.querySelector('p').textContent = '✓ ' + input.files[0].name; }
}



function carregarMoradores(){

    fetch("vermoradores.php")

    .then(response => response.text())

    .then(data => {

        document.getElementById("corpoTabela").innerHTML = data;

    });

}


</script>
</body>
</html>
