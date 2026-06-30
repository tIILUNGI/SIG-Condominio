<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = $_SESSION['id'];

// 0. AUTO-CRIAÇÃO DE TABELAS (Self-healing)
mysqli_query($conexao, "CREATE TABLE IF NOT EXISTS comunicado (id INT AUTO_INCREMENT PRIMARY KEY, titulo VARCHAR(255) NOT NULL, conteudo TEXT NOT NULL, tipo ENUM('informativo', 'urgente') DEFAULT 'informativo', criado_por INT, criado_em DATETIME DEFAULT CURRENT_TIMESTAMP)");
mysqli_query($conexao, "CREATE TABLE IF NOT EXISTS conversa (id INT AUTO_INCREMENT PRIMARY KEY, tipo ENUM('privada', 'grupo') DEFAULT 'privada', criada_em DATETIME DEFAULT CURRENT_TIMESTAMP)");
mysqli_query($conexao, "CREATE TABLE IF NOT EXISTS conversa_participante (id_conversa INT, id_user INT, tipo_user ENUM('morador', 'administrador'), PRIMARY KEY (id_conversa, id_user, tipo_user))");
mysqli_query($conexao, "CREATE TABLE IF NOT EXISTS mensagem (id INT AUTO_INCREMENT PRIMARY KEY, id_conversa INT, tipo_remetente ENUM('morador', 'administrador'), id_remetente INT, conteudo TEXT, lida TINYINT(1) DEFAULT 0, enviada_em DATETIME DEFAULT CURRENT_TIMESTAMP)");

// 1. DADOS DO MORADOR
$stmt = $conexao->prepare("
    SELECT m.*, a.codigo as apt_codigo, a.id as apt_id, b.letra as bloco, a.tipologia, a.andar
    FROM morador m
    LEFT JOIN morador_apartamento ma ON ma.id_morador = m.id AND ma.activo = 1
    LEFT JOIN apartamento a ON a.id = ma.id_apartamento
    LEFT JOIN bloco b ON b.id = a.id_bloco
    WHERE m.id = ?
");
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$morador = $stmt->get_result()->fetch_assoc();

// 2. KPIS FINANCEIROS REAIS
$res_pago = mysqli_query($conexao, "SELECT SUM(mp.valor_pago) FROM mensalidade_pagamento mp JOIN mensalidade m ON m.id = mp.id_mensalidade WHERE m.id_morador = $morador_id AND mp.estado = 'confirmado'");
$total_cash = mysqli_fetch_row($res_pago)[0] ?? 0;

$res_meses = mysqli_query($conexao, "SELECT COUNT(*) FROM mensalidade WHERE id_morador = $morador_id AND estado = 'pago'");
$meses_pagos = mysqli_fetch_row($res_meses)[0] ?? 0;

$res_pend = mysqli_query($conexao, "SELECT COUNT(*) FROM mensalidade WHERE id_morador = $morador_id AND estado = 'pendente'");
$pendentes = mysqli_fetch_row($res_pend)[0] ?? 0;

$total_multas = 0;
$check_m = mysqli_query($conexao, "SHOW TABLES LIKE 'multas'");
if (mysqli_num_rows($check_m) > 0) {
    $res_m = mysqli_query($conexao, "SELECT SUM(valor) FROM multas WHERE id_morador = $morador_id AND estado = 'pendente'");
    $total_multas = mysqli_fetch_row($res_m)[0] ?? 0;
}

$sql_logs = "SELECT m.servico, m.mes, mp.estado, mp.data_pagamento FROM mensalidade_pagamento mp JOIN mensalidade m ON m.id = mp.id_mensalidade WHERE m.id_morador = $morador_id ORDER BY mp.data_pagamento DESC LIMIT 4";
$res_logs = mysqli_query($conexao, $sql_logs);
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Morador — Nosso Zimbo</title>
    
    <!-- CSS Corporativo Base -->
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="../js/theme-manager.js"></script>
    
    <style>
        /* Estilos e Ajustes Específicos do Portal (Sem quebrar o Admin CSS) */
        :root { --gold-soft: rgba(180, 145, 74, 0.1); }
        
        .main-content { transition: all 0.3s; }
        .page-header { margin-top: 10px; }

        /* Ajuste do Chat (Indentação e Tamanho) */
        .chat-app { display: flex; height: calc(100vh - 250px); min-height: 500px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
        .chat-sidebar { width: 300px; border-right: 1px solid var(--border); background: var(--bg); display: flex; flex-direction: column; }
        .chat-main { flex: 1; display: flex; flex-direction: column; background: var(--surface); }
        .chat-list { flex: 1; overflow-y: auto; padding: 1rem; }
        .chat-user-item { padding: 12px; border-radius: var(--radius); display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 5px; transition: 0.2s; }
        .chat-user-item:hover { background: var(--gold-soft); }
        .chat-user-item.active { background: var(--gold-soft); border: 1px solid var(--gold); }
        .chat-header { padding: 1.25rem 2rem; border-bottom: 1px solid var(--border); font-weight: 700; background: var(--surface); display: flex; justify-content: space-between; align-items: center; }
        .chat-messages { flex: 1; padding: 2rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; background: var(--bg); }
        
        .bubble { max-width: 70%; padding: 12px 18px; border-radius: 18px; font-size: 0.92rem; line-height: 1.5; color: var(--text); position: relative; }
        .bubble.sent { align-self: flex-end; background: var(--gold); color: #000; border-bottom-right-radius: 4px; box-shadow: var(--shadow); }
        .bubble.received { align-self: flex-start; background: var(--surface); border: 1px solid var(--border); border-bottom-left-radius: 4px; }
        
        .chat-input-area { padding: 1.5rem; background: var(--surface); border-top: 1px solid var(--border); display: flex; gap: 10px; }
        .chat-input { flex: 1; padding: 12px 20px; border-radius: 30px; border: 1px solid var(--border); background: var(--bg); color: var(--text); outline: none; transition: 0.2s; }
        .chat-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px var(--gold-soft); }

        /* Comunicados no portal do morador */
        .comunicado-card {
            background: var(--surface); border-radius: 10px; padding: 14px 16px; margin-bottom: 10px;
            border-left: 4px solid var(--gold); transition: transform .15s;
        }
        .comunicado-card:hover { transform: translateX(3px); }
        .comunicado-card.urgente { border-left-color: var(--danger); }
        .comunicado-card.manutencao { border-left-color: #3498db; }
        .comunicado-card h4 { margin: 0 0 6px; font-size: 0.9rem; }
        .comunicado-card p { margin: 0 0 8px; font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; }
        .comunicado-meta { font-size: 0.72rem; color: var(--text-muted); display: flex; gap: 8px; align-items: center; }

        /* Ajuste Vizinhos Grid */
        .neighbor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.25rem; }
        .neighbor-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: 0.3s; }
        .neighbor-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--gold); }
        .neighbor-avatar { width: 44px; height: 44px; border-radius: 12px; background: var(--gold-soft); color: var(--gold); display: flex; align-items: center; justify-content: center; font-weight: 700; }

        /* KPI Cards custom content alignment */
        .stat-value { letter-spacing: -0.02em; }
        .sub-nav { display: flex; gap: 8px; margin-bottom: 2rem; }
        .sub-nav-btn { background: var(--surface); border: 1px solid var(--border); padding: 8px 20px; border-radius: 20px; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .sub-nav-btn.active { background: var(--gold); color: #000; border-color: var(--gold); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-gem"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Portal do Morador</p>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <p class="nav-section">GERAL</p>
        <button class="nav-item active" onclick="switchTab('dashboard', this)"><i class="fa-solid fa-table-columns"></i> <span>Dashboard</span></button>
        <button class="nav-item" onclick="switchTab('pagamentos', this)"><i class="fa-solid fa-receipt"></i> <span>Pagamentos</span></button>
        <button class="nav-item" onclick="switchTab('comunicacao', this)"><i class="fa-solid fa-comments"></i> <span>Comunicação</span></button>
        
        <p class="nav-section">COMUNIDADE</p>
        <button class="nav-item" onclick="switchTab('vizinhos', this)"><i class="fa-solid fa-people-roof"></i> <span>Meus Vizinhos</span></button>
        
        <p class="nav-section">USER</p>
        <button class="nav-item" onclick="switchTab('perfil', this)"><i class="fa-solid fa-user-gear"></i> <span>Meu Perfil</span></button>
        <a href="../api/logout.php" class="nav-item" style="margin-top:auto; color:var(--danger);"><i class="fa-solid fa-door-open"></i> <span>Sair do Sistema</span></a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="avatar-admin"><?= strtoupper(substr($morador['nome'], 0, 1)) ?></div>
        <div style="flex:1;">
            <p class="af-name"><?= explode(' ', $morador['nome'])[0] ?></p>
            <p class="af-role">Apt <?= $morador['apt_codigo'] ?></p>
        </div>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle"><i class="fa-solid fa-bars"></i></button>
        <div class="topbar-title">Canal do Residente</div>
        <div class="topbar-right">
            <button class="btn-secondary" style="border-radius: 50%; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;" onclick="ThemeManager.toggle()" title="Trocar Tema">
                <i class="fa-solid fa-circle-half-stroke"></i>
            </button>
            <div id="clock" class="clock-display">00:00:00</div>
            <div class="avatar-admin" style="background:var(--gold); border-radius:50%; width:32px; height:32px; font-size:0.75rem; color:#000;"><?= strtoupper(substr($morador['nome'], 0, 1)) ?></div>
        </div>
    </header>

    <!-- ── DASHBOARD ── -->
    <section class="tab-section active" id="tab-dashboard">
        <div class="page-header">
            <h1 class="page-title">Boas-vindas, Residência <?= $morador['apt_codigo'] ?></h1>
            <p class="page-sub">Acompanhe sua situação financeira e avisos do condomínio.</p>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
                <p class="stat-label">Total Liquidado</p>
                <p class="stat-value"><?= number_format($total_cash, 0, ',', '.') ?> <small style="font-size:1rem;">Kz</small></p>
                <p class="stat-hint">Confirmado em tesouraria</p>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fa-solid fa-check-double"></i></div>
                <p class="stat-label">Meses em Dia</p>
                <p class="stat-value"><?= $meses_pagos ?></p>
                <p class="stat-hint">Mensalidades regulares</p>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <p class="stat-label">Meses em Aberto</p>
                <p class="stat-value"><?= $pendentes ?></p>
                <p class="stat-hint">Aguardando regularização</p>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fa-solid fa-gavel"></i></div>
                <p class="stat-label">Multas Totais</p>
                <p class="stat-value"><?= number_format($total_multas, 0, ',', '.') ?> <small style="font-size:1rem;">Kz</small></p>
                <p class="stat-hint">Inclusivo mora de atraso</p>
            </div>
        </div>

        <div class="charts-grid" style="grid-template-columns: 1.5fr 1fr; margin-top:25px;">
            <div class="card">
                <div class="card-head"><p class="card-title">Análise de Pagamentos Acumulados</p></div>
                <div class="card-body" style="padding:1.5rem;"><canvas id="residentChart" style="height:280px;"></canvas></div>
            </div>
            <div class="card">
                <div class="card-head"><p class="card-title">Atividades Recentes</p></div>
                <div class="card-body" style="padding:0;">
                    <?php while($l = mysqli_fetch_assoc($res_logs)): ?>
                    <div style="padding:1rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px;">
                        <div style="background:var(--gold-soft); color:var(--gold); border-radius:8px; width:36px; height:36px; display:flex; align-items:center; justify-content:center; font-size:0.8rem;"><i class="fa-solid fa-receipt"></i></div>
                        <div style="flex:1;">
                            <p style="font-size:0.85rem; font-weight:600; margin:0;"><?= $l['servico'] ?> (<?= strtoupper($l['mes']) ?>)</p>
                            <p style="font-size:0.7rem; color:var(--text-muted); margin:0;"><?= date('d/m/y', strtotime($l['data_pagamento'])) ?> · <?= strtoupper($l['estado']) ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($res_logs) == 0): ?>
                    <div style="padding:2rem; text-align:center; color:var(--text-muted);">Sem registros de faturas.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ── PAGAMENTOS ── -->
    <section class="tab-section" id="tab-pagamentos">
        <div class="page-header"><h1 class="page-title">Gestão de Mensalidades</h1><p class="page-sub">Visualize faturas abertas e seu histórico de liquidação.</p></div>
        
        <div class="sub-nav">
            <button class="sub-nav-btn active" onclick="switchSubTab('pag-liqui', this)">Faturas em Aberto</button>
            <button class="sub-nav-btn" onclick="switchSubTab('pag-hist', this)">Histórico Completo</button>
        </div>

        <div id="pag-liqui" class="sub-tab-content">
            <div class="stat-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                <?php 
                $all_m = mysqli_query($conexao, "SELECT m.*, mp.id as pag_id, mp.referencia, mp.data_pagamento, mp.metodo FROM mensalidade m LEFT JOIN mensalidade_pagamento mp ON mp.id_mensalidade = m.id WHERE m.id_morador = $morador_id ORDER BY ano DESC, id DESC");
                while($m = mysqli_fetch_assoc($all_m)): 
                    $pago = ($m['estado'] === 'pago');
                ?>
                <div class="stat-card" style="<?= $pago ? 'border-top: 4px solid var(--success);' : 'border-top: 4px solid var(--danger);' ?> padding:25px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                        <p style="font-weight:700; font-size:1.1rem; margin:0;"><?= $m['mes'] ?> / <?= $m['ano'] ?></p>
                        <span class="badge <?= $pago ? 'pago' : 'vencido' ?>"><?= $pago ? 'PAGO' : 'PENDENTE' ?></span>
                    </div>
                    <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:15px;"><?= $m['servico'] ?></p>
                    <div style="font-size:1.6rem; font-weight:800; margin-bottom:20px;"><?= number_format($m['valor'], 0) ?> <small>Kz</small></div>
                    <?php if(!$pago): ?>
                    <button class="btn-primary" style="width:100%; justify-content:center;" onclick="openPay(<?= $m['id'] ?>, '<?= $m['servico'] ?>', <?= $m['valor'] ?>)">Pagar Agora</button>
                    <?php else: ?>
                    <div style="display:flex; gap:.5rem;">
                        <button class="btn-secondary" style="flex:1; justify-content:center; pointer-events:none; opacity:0.7;">Comprovado</button>
                        <?php if($m['pag_id']): ?>
                        <a href="recibo.php?id=<?= $m['pag_id'] ?>" target="_blank" class="btn-primary" style="flex:1; justify-content:center; text-decoration:none;"><i class="fa-solid fa-print"></i> Recibo</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div id="pag-hist" class="sub-tab-content" style="display:none;">
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead><tr><th>Referência</th><th>Mês/Ano</th><th>Serviço</th><th>Valor</th><th>Método</th><th>Data de Liquidação</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php 
                            $hist = mysqli_query($conexao, "SELECT m.*, mp.id as pag_id, mp.data_pagamento, mp.metodo, mp.valor_pago, mp.referencia FROM mensalidade m LEFT JOIN mensalidade_pagamento mp ON mp.id_mensalidade = m.id WHERE m.id_morador = $morador_id AND m.estado = 'pago' ORDER BY m.ano DESC, m.id DESC");
                            while($h = mysqli_fetch_assoc($hist)): 
                            ?>
                            <tr>
                                <td><?php echo $h['pag_id'] ? '<a href="../pages/recibo.php?id=' . $h['pag_id'] . '" target="_blank" style="color:var(--primary); text-decoration:none; font-weight:700;">#Ref: ' . htmlspecialchars($h['referencia'] ?: $h['pag_id']) . '</a>' : '#' . $h['id']; ?></td>
                                <td><strong><?= $h['mes'] ?> / <?= $h['ano'] ?></strong></td>
                                <td><?= $h['servico'] ?></td>
                                <td><?= number_format($h['valor'], 0) ?> Kz</td>
                                <td><?= $h['metodo'] ?? 'Bancário' ?></td>
                                <td><?= $h['data_pagamento'] ? date('d/m/y H:i', strtotime($h['data_pagamento'])) : 'N/A' ?></td>
                                <td><span class="badge pago"><i class="fa-solid fa-circle-check"></i> CONFIRMADO</span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- ── VIZINHOS ── -->
    <section class="tab-section" id="tab-vizinhos">
        <div class="page-header"><h1 class="page-title">Comunidade Nosso Zimbo</h1><p class="page-sub">Interaja e localize os vizinhos do seu condomínio.</p></div>
        <div class="neighbor-grid">
            <?php 
            $viz = mysqli_query($conexao, "SELECT m.id, m.nome, a.codigo as apt, b.letra as bloco FROM morador m JOIN morador_apartamento ma ON ma.id_morador = m.id AND ma.activo = 1 JOIN apartamento a ON a.id = ma.id_apartamento JOIN bloco b ON b.id = a.id_bloco WHERE m.id != $morador_id AND m.estado_conta = 'Activo'");
            while($v = mysqli_fetch_assoc($viz)): 
            ?>
            <div class="neighbor-card">
                <div class="neighbor-avatar"><?= strtoupper(substr($v['nome'], 0, 1)) ?></div>
                <div style="flex:1;">
                    <p style="font-weight:700; margin:0; font-size:1rem;"><?= htmlspecialchars($v['nome']) ?></p>
                    <p style="font-size:0.8rem; color:var(--text-muted); margin:0;">Bloco <?= $v['bloco'] ?> · Apto <?= $v['apt'] ?></p>
                </div>
                <button class="btn-primary btn-sm" style="border-radius:50%; width:40px; height:40px; justify-content:center;" onclick="startChat(<?= $v['id'] ?>, '<?= $v['nome'] ?>')"><i class="fa-solid fa-message"></i></button>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- ── COMUNICAÇÃO ── -->
    <section class="tab-section" id="tab-comunicacao">
        <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:22px;">
            <!-- COLUNA ESQUERDA: COMUNICADOS -->
            <div>
                <div class="card">
                    <div class="card-head">
                        <p class="card-title"><i class="fa-solid fa-bullhorn"></i> Comunicados Oficiais</p>
                    </div>
                    <div id="comunicados-list-morador" style="padding:20px; max-height:460px; overflow-y:auto;">
                        <p style="text-align:center; color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Carregando...</p>
                    </div>
                </div>
            </div>

            <!-- COLUNA DIREITA: CHAT -->
            <div class="chat-app">
                <div class="chat-main">
                    <div class="chat-header">
                        <div><span id="targetName">Administração</span><br><span style="font-size:0.7rem; color:var(--success); font-weight:400;"><i class="fa-solid fa-circle"></i> Online</span></div>
                        <i class="fa-solid fa-shield-halved" style="color:var(--gold);"></i>
                    </div>
                    <div class="chat-messages" id="messageBox">
                        <div class="bubble received">Olá! Como podemos ajudá-lo hoje?</div>
                    </div>
                    <div class="chat-input-area">
                        <input type="text" id="chatInput" class="chat-input" placeholder="Escreva sua mensagem aqui...">
                        <button class="btn-primary" style="border-radius:50%; width:48px; height:48px; justify-content:center; padding:0;" onclick="sendMsg()"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── PERFIL ── -->
    <section class="tab-section" id="tab-perfil">
        <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:30px;">
            <div class="card">
                <div class="card-head"><p class="card-title">Minhas Configurações</p></div>
                <div class="card-body">
                    <form id="perfilForm">
                        <div class="form-grid">
                            <div class="form-group"><label>Email de Contacto</label><input type="email" name="email" value="<?= $morador['email'] ?>"></div>
                            <div class="form-group"><label>Telefone</label><input type="text" name="telefone" value="<?= $morador['telefone'] ?>"></div>
                            <div class="form-group"><label>Nova Password</label><input type="password" name="nova_senha" placeholder="••••••••"></div>
                            <div class="form-group"><label>Confirmar Password</label><input type="password" name="conf_senha" placeholder="••••••••"></div>
                        </div>
                        <button type="submit" class="btn-primary" style="margin-top:25px;">Atualizar Meus Dados</button>
                    </form>
                </div>
            </div>
            
            <div class="card" style="background:var(--sidebar-bg); color:#fff; border-color:var(--gold);">
                <div class="card-body" style="text-align:center;">
                    <i class="fa-solid fa-house-chimney-window" style="font-size:3.5rem; color:var(--gold); margin-bottom:20px;"></i>
                    <h2 style="font-size:2.8rem; margin:0; font-family:'DM Serif Display';"><?= $morador['apt_codigo'] ?></h2>
                    <p style="font-weight:700; color:var(--gold); opacity:0.8; letter-spacing:1px; margin-bottom:30px;">UNIDADE ATRIBUÍDA</p>
                    <div style="text-align:left; background:rgba(0,0,0,0.2); padding:20px; border-radius:12px; font-size:0.9rem; line-height:2;">
                        <p><strong>Bloco:</strong> <?= $morador['bloco'] ?></p>
                        <p><strong>Andar:</strong> <?= $morador['andar'] ?>º Andar</p>
                        <p><strong>Tipologia:</strong> <?= $morador['tipologia'] ?></p>
                        <p><strong>Estado:</strong> Ocupado (Residente)</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- PAY MODAL -->
<div class="modal-overlay" id="payModal">
    <div class="modal-box">
        <h3 class="modal-title">Submeter Comprovativo</h3>
        <form id="payForm" action="../api/upload_recibo_mensalidade.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_mensalidade" id="m-id">
            <div class="form-group"><label>Serviço</label><input type="text" id="m-serv" readonly style="background:var(--bg);"></div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin:15px 0;">
                <div class="form-group"><label>Valor Pago (Kz)</label><input type="number" name="valor_pago" id="m-val" required></div>
                <div class="form-group"><label>Comprovativo (PDF/IMG)</label><input type="file" name="recibo" required></div>
            </div>
            <p style="font-size:0.75rem; color:var(--text-muted); background:var(--gold-soft); padding:10px; border-radius:8px; border-left:3px solid var(--gold);">
                <strong>IBAN Bancário:</strong> AO06.0040.0000.1234.5678.1018.9
            </p>
            <div style="display:flex; gap:12px; margin-top:25px;">
                <button type="button" class="btn-secondary" style="flex:1;" onclick="closePay()">Cancelar</button>
                <button type="submit" class="btn-primary" style="flex:2; justify-content:center;">Confirmar Pagamento</button>
            </div>
        </form>
    </div>
</div>

<script>
    // System Clock
    function clock() { document.getElementById('clock').textContent = new Date().toLocaleTimeString('pt-AO'); }
    setInterval(clock, 1000); clock();

    // Tab Navigation
    function switchTab(id, btn) {
        document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.getElementById('tab-' + id).classList.add('active');
        if(btn) btn.classList.add('active');
    }
    function switchSubTab(id, btn) {
        const parent = btn.closest('section');
        parent.querySelectorAll('.sub-tab-content').forEach(c => c.style.display = 'none');
        parent.querySelectorAll('.sub-nav-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(id).style.display = 'block';
        btn.classList.add('active');
    }

    // Chart logic
    const ctx = document.getElementById('residentChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Pagamento (Kz)',
                data: [<?= $total_cash*0.1?>, <?= $total_cash*0.3?>, <?= $total_cash*0.5?>, <?= $total_cash*0.7?>,<?= $total_cash*0.9?>,<?= $total_cash?>],
                borderColor: '#b4914a',
                backgroundColor: 'rgba(180, 145, 74, 0.1)',
                fill: true, tension: 0.4, borderWidth: 3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // Chat Functions
    const API_CHAT = '../api/api_comunicacao.php';
    let lastMsgId = 0;
    let chatPolling = false;
    function loadMessages() {
        if (chatPolling) return;
        chatPolling = true;
        fetch(API_CHAT + '?acao=listar_mensagens')
            .then(r => r.json())
            .then(data => {
                if (!data.sucesso) { chatPolling = false; return; }
                const box = document.getElementById('messageBox');
                if (data.dados.length === 0) {
                    box.innerHTML = '<div class="bubble received">Nenhuma mensagem ainda. Inicie a conversa!</div>';
                    lastMsgId = 0;
                    chatPolling = false;
                    return;
                }
                const newestId = data.dados[data.dados.length - 1].id;
                if (newestId === lastMsgId) { chatPolling = false; return; }
                lastMsgId = newestId;
                box.innerHTML = data.dados.map(m => {
                    const cls = m.de_morador ? 'sent' : 'received';
                    const time = new Date(m.enviada_em).toLocaleTimeString('pt-AO', {hour:'2-digit', minute:'2-digit'});
                    return `<div class="bubble ${cls}">${escHtml(m.conteudo)}<div style="font-size:0.65rem; opacity:0.7; margin-top:3px;">${time}</div></div>`;
                }).join('');
                box.scrollTop = box.scrollHeight;
                chatPolling = false;
            })
            .catch(() => { chatPolling = false; });
    }
    function sendMsg() {
        const input = document.getElementById('chatInput');
        const txt = input.value.trim();
        if (!txt) return;
        const fd = new FormData();
        fd.append('conteudo', txt);
        fetch(API_CHAT + '?acao=enviar_mensagem', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                input.value = '';
                if (data.sucesso) loadMessages();
            });
    }
    function escHtml(s) { if(!s) return ''; return s.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function loadComunicadosMorador() {
        fetch(API_CHAT + '?acao=listar_comunicados')
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById('comunicados-list-morador');
                if (!data.sucesso || !data.dados.length) {
                    el.innerHTML = '<p style="text-align:center;color:var(--text-muted);">Nenhum comunicado.</p>';
                    return;
                }
                el.innerHTML = data.dados.map(c => {
                    const color = c.tipo === 'urgente' ? 'var(--danger)' : (c.tipo === 'manutencao' ? '#3498db' : 'var(--gold)');
                    const dt = new Date(c.criado_em).toLocaleDateString('pt-AO', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'});
                    return `<div class="comunicado-card ${c.tipo}" style="margin-bottom:12px;">
                        <h4 style="margin:0 0 6px; color:${color};">${escHtml(c.titulo)}</h4>
                        <p style="margin:0 0 8px; font-size:0.82rem; color:var(--text-muted); line-height:1.4;">${escHtml(c.conteudo)}</p>
                        <div style="font-size:0.7rem; color:var(--text-muted);"><i class="fa-regular fa-clock"></i> ${dt}</div>
                    </div>`;
                }).join('');
            });
    }

    // Payment functions
    function openPay(id, serv, val) {
        window.location.href = 'pagar_mensalidade.php?id=' + id;
    }
    function closePay() { document.getElementById('payModal').classList.remove('open'); }

    // Forms handling
    document.getElementById('payForm').onsubmit = function(e){
        e.preventDefault();
        fetch(this.action, { method: 'POST', body: new FormData(this) }).then(r=>r.json()).then(d=>{
            if(d.sucesso) { alert('Comprovativo submetido com sucesso!'); window.location.reload(); }
            else alert(d.erro);
        });
    }
    document.getElementById('perfilForm').onsubmit = function(e){
        e.preventDefault();
        fetch('../api/api_perfil_morador.php', { method: 'POST', body: new FormData(this) }).then(r=>r.json()).then(d=>{
            if(d.sucesso) { alert('Perfil atualizado!'); window.location.reload(); }
            else alert(d.erro);
        });
    }

    // Theme Toggle (Linked to and handled by ThemeManager in theme-manager.js)
    // The theme-manager.js script already injects a button or is accessible via ThemeManager.toggle()
    // We can also add a manual trigger if needed, but we ensure it matches the attribute.
    // The theme-manager.js expects a container .topbar-right to auto-inject.

    // Init chat + comunicados
    loadMessages();
    loadComunicadosMorador();
    setInterval(loadMessages, 3000);
    setInterval(loadComunicadosMorador, 60000);
    document.getElementById('chatInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
    });
</script>

</body>
</html>
