<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = (int)$_SESSION['id'];

$stmt = $conexao->prepare(
    "SELECT m.*, a.numero as apartamento, bl.letra as bloco
     FROM mensalidade m
     LEFT JOIN apartamento a ON m.id_apartamento = a.id
     LEFT JOIN bloco bl ON a.id_bloco = bl.id
     WHERE m.id_morador = ?
     ORDER BY m.ano DESC, m.mes DESC"
);
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$mensalidades = $stmt->get_result();

$total_pendente = 0;
$total_pago = 0;
while ($row = $mensalidades->fetch_assoc()) {
    if ($row['estado'] === 'pendente' || $row['estado'] === 'atrasado') {
        $total_pendente += (float)$row['valor'];
    } elseif ($row['estado'] === 'pago') {
        $total_pago += (float)$row['valor'];
    }
}
$mensalidades->data_seek(0);

$nome = $_SESSION['nome'] ?? 'Morador';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nosso Zimbo — Mensalidades</title>
    <link rel="stylesheet" href="../Css/nosso-zimbo-admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Mensalidades</p>
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
        <button class="nav-item active" onclick="window.location.href='minhas_mensalidades.php'">
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
        <span class="topbar-title"><i class="fa-solid fa-credit-card"></i> Nosso Zimbo — Mensalidades</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active" id="tab-mensalidades">
        <div class="page-header">
            <h1 class="page-title">Mensalidades</h1>
            <p class="page-sub">Pagar e acompanhar o histórico</p>
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-chart-line"></i> Resumo Financeiro</p>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="stat-card red" style="margin:0;">
                    <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <p class="stat-label">Pendente</p>
                    <p class="stat-value">Kz <?php echo number_format($total_pendente, 2); ?></p>
                    <p class="stat-hint">A aguardar pagamento</p>
                </div>
                <div class="stat-card green" style="margin:0;">
                    <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <p class="stat-label">Pago</p>
                    <p class="stat-value">Kz <?php echo number_format($total_pago, 2); ?></p>
                    <p class="stat-hint">Pagamentos confirmados</p>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-file-invoice"></i> Histórico de Mensalidades</p>
            </div>

            <div style="overflow-x:auto;">
                <table class="data-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Mês/Ano</th>
                            <th>Apartamento</th>
                            <th>Serviço</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $nome_mes = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
                    if ($mensalidades->num_rows > 0):
                        while ($m = $mensalidades->fetch_assoc()):
                            $mes_idx = (int)$m['mes'] - 1;
                            $mes_txt = $nome_mes[$mes_idx] ?? ('M' . $m['mes']);
                            $pode_pagar = ($m['estado'] === 'pendente' || $m['estado'] === 'atrasado');
                            $status = strtoupper($m['estado']);
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mes_txt . '/' . $m['ano']); ?></td>
                            <td><?php echo htmlspecialchars(($m['bloco'] ?? '') . '-' . ($m['apartamento'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($m['servico']); ?></td>
                            <td><strong>Kz <?php echo number_format((float)$m['valor'], 2); ?></strong></td>
                            <td><?php echo $m['vencimento'] ? date('d/m/Y', strtotime($m['vencimento'])) : '—'; ?></td>
                            <td>
                                <span class="badge-info" style="background:rgba(100,149,237,.15);color:#6495ed;border:1px solid rgba(100,149,237,.3);padding:2px 8px;border-radius:6px;font-weight:700;">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($pode_pagar): ?>
                                    <button class="btn btn-pagar" onclick="pagar(<?php echo (int)$m['id']; ?>)">
                                        <i class="fas fa-check"></i> Pagar
                                    </button>
                                <?php else: ?>
                                    <span style="color:#888;font-weight:700;">✓</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">
                                Nenhuma mensalidade encontrada.
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

function pagar(id) {
    if (confirm('Confirmar pagamento da mensalidade?')) {
        window.location.href = 'pagar_mensalidade.php?id=' + id;
    }
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
</script>
</body>
</html>

