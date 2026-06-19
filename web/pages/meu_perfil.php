<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = $_SESSION['id'];
$morador_nome = $_SESSION['nome'];

$stmt = $conexao->prepare("SELECT * FROM morador WHERE id = ?");
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$result = $stmt->get_result();
$morador = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Meu Perfil - Nosso Zimbo</title>
    <link rel="stylesheet" href="../Css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Meu Perfil</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu</p>
        <button class="nav-item" onclick="window.location.href='dashboard_morador.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item active">
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

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo — Perfil</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active">
        <div class="page-header">
            <h1 class="page-title">👤 Meu Perfil</h1>
            <p class="page-sub">Gerencie suas informações pessoais</p>
        </div>

        <div class="card">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-id-card"></i> Dados Pessoais</p>
            </div>
            <div style="padding:20px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div><strong>Nome:</strong> <?php echo htmlspecialchars($morador['nome'] ?? 'N/A'); ?></div>
                    <div><strong>Email:</strong> <?php echo htmlspecialchars($morador['email'] ?? 'N/A'); ?></div>
                    <div><strong>BI:</strong> <?php echo htmlspecialchars($morador['numbI'] ?? 'N/A'); ?></div>
                    <div><strong>Telefone:</strong> <?php echo htmlspecialchars($morador['telefone'] ?? 'N/A'); ?></div>
                    <div><strong>Nacionalidade:</strong> <?php echo htmlspecialchars($morador['nacionalidade'] ?? 'N/A'); ?></div>
                    <div><strong>Data Nascimento:</strong> <?php echo $morador['nasc'] ? date('d/m/Y', strtotime($morador['nasc'])) : 'N/A'; ?></div>
                    <div><strong>Morada:</strong> <?php echo htmlspecialchars($morador['morada_anterior'] ?? 'N/A'); ?></div>
                    <div><strong>Status:</strong> 
                        <span style="color:<?php echo $morador['estado_conta'] === 'Activo' ? 'var(--success)' : 'var(--danger)'; ?>">
                            <?php echo $morador['estado_conta'] ?? 'N/A'; ?>
                        </span>
                    </div>
                </div>
                <div style="margin-top:20px;border-top:1px solid var(--border);padding-top:20px;">
                    <button class="btn-primary" onclick="alert('Funcionalidade em desenvolvimento')">
                        <i class="fa-solid fa-pen"></i> Editar Perfil
                    </button>
                    <button class="btn-secondary" onclick="alert('Funcionalidade em desenvolvimento')" style="margin-left:10px;">
                        <i class="fa-solid fa-key"></i> Alterar Senha
                    </button>
                </div>
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
window.onload = function() { clock(); setInterval(clock, 1000); };
</script>
</body>
</html>