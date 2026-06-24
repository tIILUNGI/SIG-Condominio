<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = (int)$_SESSION['id'];

// Processar nova ocorrência
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $tipo = $_POST['tipo'] ?? 'Outro';
    $prioridade = $_POST['prioridade'] ?? 'Media';

    if ($titulo !== '' && $descricao !== '') {
        $stmt = $conexao->prepare(
            "INSERT INTO ocorrencia (id_morador, titulo, descricao, tipo, prioridade) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("issss", $morador_id, $titulo, $descricao, $tipo, $prioridade);
        $ok = $stmt->execute();
        $stmt->close();

        $msg = $ok ? 'Ocorrência criada com sucesso!' : 'Erro ao criar ocorrência.';
    } else {
        $msg = 'Preenche título e descrição.';
    }
}

// Buscar ocorrências do morador
$stmt = $conexao->prepare(
    "SELECT id, titulo, descricao, tipo, prioridade, estado, criado_em, data_resolucao, notas_admin
     FROM ocorrencia
     WHERE id_morador = ?
     ORDER BY criado_em DESC"
);
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$ocorrencias = $stmt->get_result();

$nome = $_SESSION['nome'] ?? 'Morador';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nosso Zimbo — Ocorrências</title>
    <link rel="stylesheet" href="../Css/nosso-zimbo-admin.css">
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
            <p class="brand-sub">Ocorrências</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu Principal</p>
        <button class="nav-item" onclick="window.location.href='dashboard_morador.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item active" onclick="window.location.href='minhas_ocorrencias.php'">
            <i class="fa-solid fa-exclamation-triangle"></i><span>Ocorrências</span>
        </button>
        <button class="nav-item" onclick="window.location.href='minhas_mensalidades.php'">
            <i class="fa-solid fa-credit-card"></i><span>Mensalidades</span>
        </button>
        <button class="nav-item" onclick="window.location.href='comunicacao.php'">
            <i class="fa-solid fa-comments"></i><span>Comunicação</span>
        </button>
        <button class="nav-item" onclick="window.location.href='visitas.php'">
            <i class="fa-solid fa-user-plus"></i><span>Visitas</span>
        </button>
        <p class="nav-section">Configurações</p>
        <button class="nav-item" onclick="window.location.href='meu_perfil.php'">
            <i class="fa-solid fa-user"></i><span>Meu Perfil</span>
        </button>
    </nav>
    <div class="sidebar-footer">
        <div class="avatar-admin"><?php echo strtoupper(substr($nome, 0, 2)); ?></div>
        <div style="flex:1;">
            <p class="af-name"><?php echo htmlspecialchars($nome); ?></p>
            <p class="af-role">Morador</p>
        </div>
        <a href="../api/logout.php" title="Sair" style="color:var(--text-muted); font-size:1rem;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="topbar-title"><i class="fa-solid fa-exclamation-triangle"></i> Nosso Zimbo — Ocorrências</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active" id="tab-ocorrencias">
        <div class="page-header">
            <h1 class="page-title">Minhas Ocorrências</h1>
            <p class="page-sub">Registe e acompanhe o estado das suas ocorrências</p>
        </div>

        <?php if ($msg): ?>
            <div class="card" style="margin-top:10px; background:rgba(76,175,125,.12); border:1px solid rgba(76,175,125,.25);">
                <div class="card-head"><p class="card-title"><i class="fa-solid fa-circle-check"></i> Info</p></div>
                <div style="padding:0 1rem 1rem; color:var(--text);"> <?php echo htmlspecialchars($msg); ?> </div>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-top:20px;">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-plus"></i> Nova Ocorrência</p>
            </div>
            <form method="POST" style="padding:20px;">
                <input type="hidden" name="acao" value="criar" />
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Título *</label>
                        <input type="text" name="titulo" required />
                    </div>
                    <div class="form-group full">
                        <label>Descrição *</label>
                        <textarea name="descricao" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo">
                            <option value="Avaria">Avaria</option>
                            <option value="Reclamacao">Reclamação</option>
                            <option value="Sugestao">Sugestão</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Prioridade</label>
                        <select name="prioridade">
                            <option value="Baixa">Baixa</option>
                            <option value="Media" selected>Média</option>
                            <option value="Alta">Alta</option>
                            <option value="Urgente">Urgente</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="margin-top:1rem; width:100%;">
                    <i class="fa-solid fa-paper-plane"></i> Criar Ocorrência
                </button>
            </form>
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-list"></i> Minhas Ocorrências</p>
            </div>

            <div style="padding:0 1rem 1rem;">
                <?php if ($ocorrencias && $ocorrencias->num_rows > 0): ?>
                    <?php while ($oc = $ocorrencias->fetch_assoc()): ?>
                        <div style="background:rgba(248,249,250,.85); border-radius:10px; padding:14px; margin-top:12px; border:1px solid var(--border);">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                                <div>
                                    <h4 style="margin:0;"><?php echo htmlspecialchars($oc['titulo']); ?></h4>
                                    <div style="font-size:13px; color:var(--text-muted); margin-top:6px;">
                                        <span><b>Tipo:</b> <?php echo htmlspecialchars($oc['tipo']); ?></span>
                                        &nbsp;|&nbsp;
                                        <span><b>Prioridade:</b> <?php echo htmlspecialchars($oc['prioridade']); ?></span>
                                        &nbsp;|&nbsp;
                                        <span>
                                            <b>Data:</b> <?php echo date('d/m/Y H:i', strtotime($oc['criado_em'])); ?>
                                        </span>
                                        <?php if (!empty($oc['data_resolucao'])): ?>
                                            &nbsp;|&nbsp; <span><b>Resolvida:</b> <?php echo date('d/m/Y', strtotime($oc['data_resolucao'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge-info" style="background:rgba(100,149,237,.15); color:#6495ed; border:1px solid rgba(100,149,237,.3); padding:4px 10px; border-radius:999px; font-weight:700;">
                                        <?php echo htmlspecialchars(strtoupper($oc['estado'])); ?>
                                    </span>
                                </div>
                            </div>

                            <p style="margin:10px 0 0; color:var(--text);">
                                <?php echo nl2br(htmlspecialchars($oc['descricao'])); ?>
                            </p>

                            <?php if (!empty($oc['notas_admin'])): ?>
                                <div style="margin-top:12px; background:rgba(59,130,246,.08); border:1px solid rgba(59,130,246,.15); padding:12px; border-radius:10px;">
                                    <strong>Resposta da Administração:</strong>
                                    <div style="margin-top:6px;"> <?php echo nl2br(htmlspecialchars($oc['notas_admin'])); ?> </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align:center; color:var(--text-muted); padding:22px 0;">
                        <i class="fas fa-inbox" style="font-size:28px; opacity:.35;"></i>
                        <div style="margin-top:8px; font-weight:600;">Nenhuma ocorrência registrada.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

function clock() {
    const now = new Date();
    const el = document.getElementById('clock-display');
    if (el) el.textContent = now.toLocaleTimeString('pt-AO');
}

window.onload = function() {
    clock();
    setInterval(clock, 1000);
};

function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }
</script>
</body>
</html>

