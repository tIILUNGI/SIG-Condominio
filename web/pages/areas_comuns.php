<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
$morador_nome = $_SESSION['nome'];
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Áreas Comuns - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="../js/theme-manager.js"></script>
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:1000; align-items:center; justify-content:center; -webkit-backdrop-filter: blur(5px); backdrop-filter: blur(5px); }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface,#fff); border:1px solid var(--border,#ddd); border-radius:18px; padding:2rem; width:100%; max-width:500px; position:relative; box-shadow:var(--shadow-md); animation: scaleIn 0.3s ease; }
        @keyframes scaleIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-close { position:absolute; top:1rem; right:1rem; background:var(--bg); border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
        .modal-title { font-size:1.3rem; font-weight:700; color:var(--gold); margin-bottom:1rem; }
        .area-card { transition: transform 0.3s; cursor: pointer; }
        .area-card:hover { transform: translateY(-5px); border-color: var(--gold); }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Áreas Comuns</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu</p>
        <button class="nav-item" onclick="window.location.href='dashboard_morador.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item active">
            <i class="fa-solid fa-calendar-check"></i><span>Áreas Comuns</span>
        </button>
        <button class="nav-item" onclick="window.location.href='minhas_ocorrencias.php'">
            <i class="fa-solid fa-exclamation-triangle"></i><span>Ocorrências</span>
        </button>
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
        <span class="topbar-title"><i class="fa-solid fa-water-ladder"></i> Nosso Zimbo — Áreas Comuns</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active">
        <div class="page-header">
            <h1 class="page-title">🏊 Áreas Comuns</h1>
            <p class="page-sub">Reserve espaços de lazer para si e para a sua família</p>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px,1fr)); gap:20px;">
            <div class="card area-card" onclick="openReserva('Piscina', 'fa-water-ladder', '#3498db')">
                <div style="text-align:center;padding:2rem;">
                    <div style="background:rgba(52,152,219,0.1); width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                        <i class="fa-solid fa-water-ladder" style="font-size:36px;color:#3498db;"></i>
                    </div>
                    <h3 style="font-size:1.2rem;margin-bottom:.5rem;">Piscina</h3>
                    <p style="color:var(--text-muted);font-size:14px;margin-bottom:1.5rem;">Acesso livre para moradores registados. Disponível das 08h às 19h.</p>
                    <button class="btn-primary" style="width:100%;">
                        <i class="fa-solid fa-calendar-plus"></i> Reservar Espaço
                    </button>
                </div>
            </div>
            <div class="card area-card" onclick="openReserva('Salão de Festas', 'fa-utensils', '#f39c12')">
                <div style="text-align:center;padding:2rem;">
                    <div style="background:rgba(243,156,18,0.1); width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                        <i class="fa-solid fa-utensils" style="font-size:36px;color:#f39c12;"></i>
                    </div>
                    <h3 style="font-size:1.2rem;margin-bottom:.5rem;">Salão de Festas</h3>
                    <p style="color:var(--text-muted);font-size:14px;margin-bottom:1.5rem;">Capacidade para 50 pessoas. Ideal para aniversários e eventos familiares.</p>
                    <button class="btn-primary" style="width:100%;">
                        <i class="fa-solid fa-calendar-plus"></i> Reservar Espaço
                    </button>
                </div>
            </div>
            <div class="card area-card" onclick="openReserva('Churrasqueira', 'fa-fire', '#e74c3c')">
                <div style="text-align:center;padding:2rem;">
                    <div style="background:rgba(231,76,60,0.1); width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                        <i class="fa-solid fa-fire" style="font-size:36px;color:#e74c3c;"></i>
                    </div>
                    <h3 style="font-size:1.2rem;margin-bottom:.5rem;">Churrasqueira</h3>
                    <p style="color:var(--text-muted);font-size:14px;margin-bottom:1.5rem;">Área coberta com grelhadores e mesas. Reserva necessária para grupos.</p>
                    <button class="btn-primary" style="width:100%;">
                        <i class="fa-solid fa-calendar-plus"></i> Reservar Espaço
                    </button>
                </div>
            </div>
            <div class="card area-card" onclick="openReserva('Campo de Jogos', 'fa-futbol', '#27ae60')">
                <div style="text-align:center;padding:2rem;">
                    <div style="background:rgba(39,174,96,0.1); width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                        <i class="fa-solid fa-futbol" style="font-size:36px;color:#27ae60;"></i>
                    </div>
                    <h3 style="font-size:1.2rem;margin-bottom:.5rem;">Campo de Jogos</h3>
                    <p style="color:var(--text-muted);font-size:14px;margin-bottom:1.5rem;">Polidesportivo para futebol e basquetebol. Reserva de 1 hora.</p>
                    <button class="btn-primary" style="width:100%;">
                        <i class="fa-solid fa-calendar-plus"></i> Reservar Espaço
                    </button>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- MODAL: Reserva -->
<div class="modal-overlay" id="modal-booking">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
        <h3 class="modal-title" id="modal-title">Solicitar Reserva</h3>
        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem;">Preencha os dados abaixo para solicitar o uso da área comum.</p>
        <form onsubmit="confirmReserva(event)">
            <div class="form-group" style="margin-bottom:1rem;">
                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px;">Espaço Seleccionado</label>
                <input type="text" id="area-nome" readonly style="background:var(--bg); border:1px solid var(--border); padding:.6rem; border-radius:8px; width:100%; font-weight:700;" />
            </div>
            <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:1rem;">
                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px;">Data *</label>
                    <input type="date" required style="width:100%; padding:.6rem; border:1px solid var(--border); border-radius:8px;" />
                </div>
                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px;">Hora *</label>
                    <input type="time" required style="width:100%; padding:.6rem; border:1px solid var(--border); border-radius:8px;" />
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:5px;">Observações / Nº de Convidados</label>
                <textarea style="width:100%; padding:.6rem; border:1px solid var(--border); border-radius:8px; height:80px; font-family:inherit;"></textarea>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">
                <i class="fa-solid fa-check-circle"></i> Confirmar Solicitação
            </button>
        </form>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
function clock() {
    const now = new Date();
    const el = document.getElementById('clock-display');
    if (el) el.textContent = now.toLocaleTimeString('pt-AO');
}
function openReserva(nome, icon, color) {
    document.getElementById('area-nome').value = nome;
    document.getElementById('modal-booking').classList.add('open');
}
function closeModal() {
    document.getElementById('modal-booking').classList.remove('open');
}
function confirmReserva(e) {
    e.preventDefault();
    alert('A sua solicitação de reserva foi enviada para a administração. Receberá uma resposta em breve.');
    closeModal();
}
window.onload = function() { clock(); setInterval(clock, 1000); };
</script>
</body>
</html>