<?php
session_start();

// Verificar login
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}

include("../api/conexao.php");

$morador_id = $_SESSION['id'];
$morador_nome = $_SESSION['nome'];
$morador_numbi = $_SESSION['numbi'] ?? '';
$morador_email = $_SESSION['email'] ?? '';

// Buscar dados do morador
$stmt = $conexao->prepare("
    SELECT m.*, a.numero as apartamento, bl.letra as bloco
    FROM morador m
    LEFT JOIN morador_apartamento ma ON m.id = ma.id_morador AND ma.activo = 1
    LEFT JOIN apartamento a ON ma.id_apartamento = a.id
    LEFT JOIN bloco bl ON a.id_bloco = bl.id
    WHERE m.id = ?
");
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$result = $stmt->get_result();
$morador = $result->fetch_assoc();

// Contar ocorrências abertas
$stmt = $conexao->prepare("SELECT COUNT(*) as total FROM ocorrencia WHERE id_morador = ? AND estado != 'encerrada'");
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$result = $stmt->get_result();
$ocorrencias_abertas = $result->fetch_assoc()['total'] ?? 0;

// Contar mensalidades pendentes
$stmt = $conexao->prepare("SELECT COUNT(*) as total FROM mensalidade WHERE id_morador = ? AND estado = 'pendente'");
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$result = $stmt->get_result();
$mensalidades_pendentes = $result->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nosso Zimbo — Painel do Morador</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Painel do Morador</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu Principal</p>
        <button class="nav-item active" onclick="switchTab('dashboard', this)">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item" onclick="window.location.href='minhas_ocorrencias.php'">
            <i class="fa-solid fa-exclamation-triangle"></i><span>Ocorrências</span>
        </button>
        <button class="nav-item" onclick="window.location.href='minhas_mensalidades.php'">
            <i class="fa-solid fa-credit-card"></i><span>Mensalidades</span>
        </button>
        <button class="nav-item" onclick="window.location.href='comunicacao.php'">
            <i class="fa-solid fa-comments"></i><span>Comunicação</span>
        </button>
        <button class="nav-item" onclick="window.location.href='areas_comuns.php'">
            <i class="fa-solid fa-calendar-check"></i><span>Áreas Comuns</span>
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
        <div class="avatar-admin"><?php echo strtoupper(substr($morador_nome, 0, 2)); ?></div>
        <div style="flex:1;">
            <p class="af-name"><?php echo htmlspecialchars($morador_nome); ?></p>
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
        <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo — Morador</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <!-- ── DASHBOARD ── -->
    <section class="tab-section active" id="tab-dashboard">
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-sub" id="dash-date"></p>
        </div>

        <div class="stat-grid">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fa-solid fa-home"></i></div>
                <p class="stat-label">Apartamento</p>
                <p class="stat-value">
                    <?php 
                    if ($morador && isset($morador['apartamento'])) {
                        echo htmlspecialchars($morador['bloco'] . '-' . $morador['apartamento']);
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </p>
                <p class="stat-hint">Sua residência</p>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fa-solid fa-exclamation-triangle"></i></div>
                <p class="stat-label">Ocorrências</p>
                <p class="stat-value"><?php echo $ocorrencias_abertas; ?></p>
                <p class="stat-hint">Abertas</p>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fa-solid fa-credit-card"></i></div>
                <p class="stat-label">Mensalidades</p>
                <p class="stat-value"><?php echo $mensalidades_pendentes; ?></p>
                <p class="stat-hint">Pendentes</p>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fa-solid fa-user-check"></i></div>
                <p class="stat-label">Status</p>
                <p class="stat-value" style="font-size:18px;">
                    <?php 
                    $status = $morador['estado_conta'] ?? 'Inactivo';
                    $cor = $status === 'Activo' ? 'var(--success)' : 'var(--danger)';
                    echo "<span style='color:$cor;'>" . $status . "</span>";
                    ?>
                </p>
                <p class="stat-hint">Conta</p>
            </div>
        </div>

        <!-- Informações Pessoais -->
        <div class="card" style="margin-top:20px;">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-id-card"></i> Meus Dados</p>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; padding:10px;">
                <div><strong>Nome:</strong> <?php echo htmlspecialchars($morador['nome'] ?? 'N/A'); ?></div>
                <div><strong>Email:</strong> <?php echo htmlspecialchars($morador['email'] ?? 'N/A'); ?></div>
                <div><strong>BI:</strong> <?php echo htmlspecialchars($morador['numbi'] ?? 'N/A'); ?></div>
                <div><strong>Telefone:</strong> <?php echo htmlspecialchars($morador['telefone'] ?? 'N/A'); ?></div>
                <div><strong>Nacionalidade:</strong> <?php echo htmlspecialchars($morador['nacionalidade'] ?? 'N/A'); ?></div>
                <div><strong>Data Nascimento:</strong> <?php echo $morador['nasc'] ? date('d/m/Y', strtotime($morador['nasc'])) : 'N/A'; ?></div>
            </div>
        </div>
    </section>
</main>

<script>
// ═══════════════════════════════════════════════════════════
// NAVIGATION
// ═══════════════════════════════════════════════════════════
function switchTab(id, btn) {
    document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    if (btn) btn.classList.add('active');
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

// ═══════════════════════════════════════════════════════════
// CLOCK
// ═══════════════════════════════════════════════════════════
function clock() {
    const now = new Date();
    const relogio = document.getElementById('clock-display');
    if (relogio) {
        relogio.textContent = now.toLocaleTimeString('pt-AO');
    }
}

// ═══════════════════════════════════════════════════════════
// DATE
// ═══════════════════════════════════════════════════════════
function updateDate() {
    const now = new Date();
    const el = document.getElementById('dash-date');
    if (el) {
        el.textContent = now.toLocaleDateString('pt-AO', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
}

// ═══════════════════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════════════════
window.onload = function() {
    clock();
    updateDate();
    setInterval(clock, 1000);
};

// Fechar sidebar ao clicar fora (mobile)
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.menu-toggle');
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
            sidebar.classList.remove('open');
        }
    }
});
</script>
</body>
</html>