<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = (int)$_SESSION['id'];
$morador_nome = $_SESSION['nome'];

// Buscar visitas agendadas
$stmt = $conexao->prepare("SELECT * FROM visita WHERE id_morador = ? ORDER BY data_prevista DESC, hora_prevista DESC");
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$visitas = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Visitas - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="../js/theme-manager.js"></script>
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
            <p class="brand-sub">Visitas</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu Principal</p>
        <button class="nav-item" onclick="window.location.href='dashboard_morador.php'">
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
        <button class="nav-item active">
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

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo — Visitas</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active">
        <div class="page-header">
            <h1 class="page-title">🚪 Gestão de Visitas</h1>
            <p class="page-sub">Registe e acompanhe visitantes</p>
        </div>

        <div class="card">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-plus"></i> Nova Visita</p>
            </div>
            <form id="form-visita" style="padding:20px;">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nome do Visitante</label>
                        <input type="text" name="nome" placeholder="Nome completo" required>
                    </div>
                    <div class="form-group">
                        <label>BI do Visitante (Opcional)</label>
                        <input type="text" name="numbi" placeholder="Nº do Bilhete de Identidade">
                    </div>
                    <div class="form-group">
                        <label>Data</label>
                        <input type="date" name="data" required>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <input type="time" name="hora" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Registrar Visita
                </button>
            </form>
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-list"></i> Visitas Agendadas</p>
            </div>
            <div style="padding:20px; overflow-x:auto;">
                <table class="data-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Visitante</th>
                            <th>Data/Hora</th>
                            <th>Código</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($visitas->num_rows > 0): ?>
                            <?php while ($v = $visitas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($v['nome_visitante']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($v['data_prevista'])) . ' ' . $v['hora_prevista']; ?></td>
                                    <td><code style="background:var(--dark3); padding:2px 6px; border-radius:4px;"><?php echo $v['codigo_acesso']; ?></code></td>
                                    <td>
                                        <span class="badge" style="padding:4px 8px; border-radius:20px; font-size:11px; background:<?php 
                                            echo $v['estado'] === 'autorizado' ? 'rgba(76,175,125,0.2); color:#4caf7d;' : 'rgba(255,255,255,0.1); color:#ccc;'; 
                                        ?>">
                                            <?php echo strtoupper($v['estado']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:2rem; color:var(--text-muted);">
                                    Nenhuma visita agendada
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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

    document.getElementById('form-visita').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('../api/api_morador.php?acao=novo_agendamento_visita', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                alert('Visita agendada com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + data.erro);
            }
        });
    };
};
</script>
</body>
</html>