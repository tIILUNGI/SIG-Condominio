<?php
session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include(__DIR__ . '/../api/conexao.php');

$sql = "SELECT mp.id, mor.nome as morador, a.codigo as apartamento,
               mp.valor_pago, mp.metodo, mp.referencia,
               mp.data_pagamento, mp.estado, mp.notas_admin
        FROM mensalidade_pagamento mp
        JOIN mensalidade m ON m.id = mp.id_mensalidade
        JOIN morador mor ON mor.id = m.id_morador
        JOIN apartamento a ON a.id = m.id_apartamento
        ORDER BY mp.data_pagamento DESC";

$res = mysqli_query($conexao, $sql);
$pagamentos = [];
while ($r = mysqli_fetch_assoc($res)) $pagamentos[] = $r;

$estado_labels = [
    'pendente' => 'Pendente',
    'confirmado' => 'Confirmado',
    'rejeitado' => 'Rejeitado',
];
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Pagamentos — Nosso Zimbo</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #222; margin: 0; background: #fff; }
        .relatorio { max-width: 1000px; margin: 0 auto; }
        .rel-header { text-align: center; border-bottom: 3px solid #1a252f; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .rel-header h1 { margin: .3rem 0; font-size: 1.5rem; }
        .rel-header p { margin: .2rem 0; color: #555; font-size: .85rem; }
        .rel-meta { text-align: right; font-size: .8rem; color: #777; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th { background: #1a252f; color: #fff; padding: .6rem .8rem; font-size: .72rem; text-transform: uppercase; text-align: left; }
        td { padding: .5rem .8rem; border-bottom: 1px solid #ddd; font-size: .82rem; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .val { font-weight: 700; }
        .val-green { color: #27ae60; }
        .badge { padding: .2rem .5rem; border-radius: 12px; font-size: .68rem; font-weight: 700; text-transform: uppercase; }
        .badge-confirmado { background: #d4edda; color: #155724; }
        .badge-pendente { background: #fff3cd; color: #856404; }
        .badge-rejeitado { background: #f8d7da; color: #721c24; }
        .rel-footer { margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #1a252f; display: flex; justify-content: space-between; font-size: .8rem; color: #777; }
        .totals { display: flex; gap: 2rem; margin-bottom: 1.5rem; }
        .total-box { flex: 1; background: #f5f5f5; padding: 1rem; border-radius: 10px; text-align: center; }
        .total-box .label { font-size: .75rem; text-transform: uppercase; color: #777; }
        .total-box .value { font-size: 1.3rem; font-weight: 800; margin-top: .3rem; }
        @media print {
            body * { visibility: hidden; }
            .relatorio, .relatorio * { visibility: visible; }
            .relatorio { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none; }
        }
        .no-print { text-align: center; margin-top: 1.5rem; }
        .no-print button { padding: .7rem 1.5rem; border-radius: 10px; border: none; cursor: pointer; font-weight: 700; background: #1a252f; color: #fff; }
    </style>
</head>
<body>
    <div class="relatorio">
        <div class="rel-header">
            <div style="font-size:2rem; color:#1a252f;">🏛️</div>
            <h1>Condomínio Nosso Zimbo</h1>
            <p>Relatório de Pagamentos</p>
            <p>Gerado em: <?= date('d/m/Y H:i') ?></p>
        </div>

        <div class="totals">
            <div class="total-box">
                <div class="label">Total de Pagamentos</div>
                <div class="value"><?= count($pagamentos) ?></div>
            </div>
            <div class="total-box">
                <div class="label">Confirmados</div>
                <div class="value val-green"><?= count(array_filter($pagamentos, fn($p)=>$p['estado']==='confirmado')) ?></div>
            </div>
            <div class="total-box">
                <div class="label">Pendentes</div>
                <div class="value" style="color:#f0a500;"><?= count(array_filter($pagamentos, fn($p)=>$p['estado']==='pendente')) ?></div>
            </div>
            <div class="total-box">
                <div class="label">Rejeitados</div>
                <div class="value" style="color:#e74c3c;"><?= count(array_filter($pagamentos, fn($p)=>$p['estado']==='rejeitado')) ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Morador</th>
                    <th>Apto</th>
                    <th>Valor</th>
                    <th>Método</th>
                    <th>Data</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagamentos as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['referencia']) ?></strong></td>
                    <td><?= htmlspecialchars($p['morador']) ?></td>
                    <td><?= htmlspecialchars($p['apartamento']) ?></td>
                    <td class="val">Kz <?= number_format($p['valor_pago'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($p['metodo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['data_pagamento'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $p['estado'] ?>">
                            <?= $estado_labels[$p['estado']] ?? $p['estado'] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="rel-footer">
            <span>Total: <?= count($pagamentos) ?> pagamentos</span>
            <span>Assinatura: _____________________</span>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir / Salvar PDF</button>
    </div>
</body>
</html>
