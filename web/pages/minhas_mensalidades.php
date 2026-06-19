<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = $_SESSION['id'];

// Buscar mensalidades
$stmt = $conexao->prepare("
    SELECT m.*, a.numero as apartamento, bl.letra as bloco
    FROM mensalidade m
    LEFT JOIN apartamento a ON m.id_apartamento = a.id
    LEFT JOIN bloco bl ON a.id_bloco = bl.id
    WHERE m.id_morador = ?
    ORDER BY m.ano DESC, m.mes DESC
");
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$mensalidades = $stmt->get_result();

// Calcular totais
$total_pendente = 0;
$total_pago = 0;
while ($row = $mensalidades->fetch_assoc()) {
    if ($row['estado'] == 'pendente' || $row['estado'] == 'atrasado') {
        $total_pendente += $row['valor'];
    } else if ($row['estado'] == 'pago') {
        $total_pago += $row['valor'];
    }
}
$mensalidades->data_seek(0); // Resetar o pointer
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Mensalidades</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-back { background: #95a5a6; color: white; }
        .btn-back:hover { background: #7f8c8d; }
        .btn-pagar { background: #27ae60; color: white; font-size: 13px; }
        .btn-pagar:hover { background: #219a52; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #2c3e50; color: white; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .status-pendente { color: #e74c3c; font-weight: 600; }
        .status-pago { color: #27ae60; font-weight: 600; }
        .status-atrasado { color: #c0392b; font-weight: 600; }
        .status-dispensado { color: #95a5a6; }
        .meses { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
            gap: 10px; 
            margin-top: 15px;
        }
        .mes-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid transparent;
        }
        .mes-item.pago { border-color: #27ae60; }
        .mes-item.pendente { border-color: #e74c3c; }
        .mes-item .valor { font-size: 18px; font-weight: 700; }
        .total-box { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
            margin: 15px 0;
        }
        .total-item { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 8px; 
            text-align: center;
        }
        .total-item .valor { font-size: 24px; font-weight: 700; }
        .total-item.pendente .valor { color: #e74c3c; }
        .total-item.pago .valor { color: #27ae60; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-credit-card"></i> Minhas Mensalidades</h2>
            <a href="dashboard_morador.php" class="btn btn-back" style="margin-top:10px;">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Resumo -->
        <div class="card">
            <h3>📊 Resumo Financeiro</h3>
            <div class="total-box">
                <div class="total-item pendente">
                    <div style="font-size:14px; color:#888;">Pendente</div>
                    <div class="valor">Kz <?php echo number_format($total_pendente, 2); ?></div>
                </div>
                <div class="total-item pago">
                    <div style="font-size:14px; color:#888;">Pago</div>
                    <div class="valor">Kz <?php echo number_format($total_pago, 2); ?></div>
                </div>
            </div>
        </div>

        <!-- Lista -->
        <div class="card">
            <h3>📋 Histórico de Mensalidades</h3>
            
            <?php if ($mensalidades->num_rows > 0): ?>
                <table>
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
                        <?php while ($m = $mensalidades->fetch_assoc()): 
                            $nome_mes = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
                            $mes = $nome_mes[$m['mes'] - 1];
                            $status_class = 'status-' . $m['estado'];
                            $pode_pagar = ($m['estado'] == 'pendente' || $m['estado'] == 'atrasado');
                        ?>
                        <tr>
                            <td><?php echo $mes . '/' . $m['ano']; ?></td>
                            <td><?php echo $m['bloco'] . '-' . $m['apartamento']; ?></td>
                            <td><?php echo $m['servico']; ?></td>
                            <td><strong>Kz <?php echo number_format($m['valor'], 2); ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($m['vencimento'])); ?></td>
                            <td class="<?php echo $status_class; ?>">
                                <?php echo strtoupper($m['estado']); ?>
                            </td>
                            <td>
                                <?php if ($pode_pagar): ?>
                                    <button class="btn btn-pagar" onclick="pagar(<?php echo $m['id']; ?>)">
                                        <i class="fas fa-check"></i> Pagar
                                    </button>
                                <?php else: ?>
                                    <span style="color:#888;">✓</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; color:#888; padding:20px;">
                    <i class="fas fa-inbox"></i> Nenhuma mensalidade encontrada.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function pagar(id) {
            if (confirm('Confirmar pagamento da mensalidade?')) {
                window.location.href = 'pagar_mensalidade.php?id=' + id;
            }
        }
    </script>
</body>
</html>