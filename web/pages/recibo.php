<?php
session_start();
include(__DIR__ . '/../api/conexao.php');

$id_pag = intval($_GET['id'] ?? 0);
if (!$id_pag || !$conexao) {
    die('Pagamento não encontrado');
}

$sql = "SELECT mp.id, mp.valor_pago, mp.metodo, mp.referencia, mp.data_pagamento, mp.estado,
               m.servico, m.mes, m.ano,
               mor.nome as morador_nome, mor.numbi, mor.telefone,
               a.codigo as apartamento, bl.letra as bloco
        FROM mensalidade_pagamento mp
        JOIN mensalidade m ON m.id = mp.id_mensalidade
        JOIN morador mor ON mor.id = m.id_morador
        JOIN apartamento a ON a.id = m.id_apartamento
        JOIN bloco bl ON bl.id = a.id_bloco
        WHERE mp.id = ? LIMIT 1";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_pag);
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();
$stmt->close();

if (!$p) die('Pagamento não encontrado');

$nome_mes = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$mes_txt = $nome_mes[(int)$p['mes'] - 1] ?? 'Mês ' . $p['mes'];

function fmtMoney($v) {
    return 'Kz ' . number_format($v, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?php echo $p['id']; ?> — Nosso Zimbo</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #222;
            margin: 0;
            background: #fff;
        }
        .recibo {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #1a252f;
            border-radius: 18px;
            overflow: hidden;
        }
        .recibo-header {
            background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .recibo-header .logo-icon { font-size: 2.5rem; margin-bottom: .5rem; }
        .recibo-header h1 { margin: .3rem 0; font-size: 1.6rem; }
        .recibo-header p { margin: .2rem 0; opacity: .85; font-size: .9rem; }
        .recibo-body { padding: 2rem; }
        .recibo-meta {
            display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;
            font-size: .82rem; color: #555;
        }
        .recibo-meta div strong { color: #222; }
        .recibo-section { margin-bottom: 1.25rem; }
        .recibo-section h3 {
            font-size: .75rem; text-transform: uppercase; letter-spacing: .06em;
            color: #777; border-bottom: 1px solid #ddd; padding-bottom: .3rem; margin: 0 0 .6rem;
        }
        .recibo-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: .55rem 0; border-bottom: 1px dashed #e5e5e5; font-size: .92rem;
        }
        .recibo-row .key { color: #555; }
        .recibo-row .val { font-weight: 700; }
        .recibo-row.total {
            border-bottom: none; border-top: 2px solid #1a252f;
            margin-top: .5rem; padding-top: .75rem; font-size: 1.1rem;
        }
        .recibo-row.total .val { color: #27ae60; font-size: 1.3rem; }
        .recibo-footer {
            margin-top: 2rem; padding-top: 1.25rem; border-top: 2px solid #1a252f;
            display: flex; justify-content: space-between; align-items: flex-end;
            font-size: .82rem; color: #555;
        }
        .stamp {
            margin-top: 1.5rem; padding: 1rem; border: 3px solid #27ae60; border-radius: 14px;
            text-align: center; color: #27ae60; font-weight: 800; font-size: 1.1rem;
        }
        .no-print { text-align: center; margin-top: 1.5rem; }
        .no-print button {
            padding: .7rem 1.5rem; border-radius: 10px; border: none; cursor: pointer;
            font-weight: 700; font-size: .9rem; background: #1a252f; color: #fff;
        }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .recibo { border: 2px solid #000; }
        }
    </style>
</head>
<body>
    <div class="recibo">
        <div class="recibo-header">
            <div class="logo-icon">🏛️</div>
            <h1>Condomínio Nosso Zimbo</h1>
            <p>Comprovativo de Pagamento</p>
            <p>Referência: <strong><?php echo htmlspecialchars($p['referencia']); ?></strong></p>
        </div>
        <div class="recibo-body">
            <div class="recibo-meta">
                <div><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($p['data_pagamento'])); ?></div>
                <div><strong>Nº Recibo:</strong> #<?php echo str_pad($p['id'], 8, '0', STR_PAD_LEFT); ?></div>
            </div>

            <div class="recibo-section">
                <h3>Dados do Morador</h3>
                <div class="recibo-row"><span class="key">Nome</span><span class="val"><?php echo htmlspecialchars($p['morador_nome']); ?></span></div>
                <div class="recibo-row"><span class="key">BI</span><span class="val"><?php echo htmlspecialchars($p['numbi']); ?></span></div>
                <div class="recibo-row"><span class="key">Telefone</span><span class="val"><?php echo htmlspecialchars($p['telefone']); ?></span></div>
                <div class="recibo-row"><span class="key">Apartamento</span><span class="val"><?php echo htmlspecialchars($p['bloco'] . '-' . $p['apartamento']); ?></span></div>
            </div>

            <div class="recibo-section">
                <h3>Detalhes do Pagamento</h3>
                <div class="recibo-row"><span class="key">Serviço</span><span class="val"><?php echo htmlspecialchars($p['servico']); ?> (<?php echo $mes_txt . ' ' . $p['ano']; ?>)</span></div>
                <div class="recibo-row"><span class="key">Método</span><span class="val"><?php echo htmlspecialchars($p['metodo']); ?></span></div>
                <div class="recibo-row total"><span class="key">Total Pago</span><span class="val"><?php echo fmtMoney($p['valor_pago']); ?></span></div>
            </div>

            <div class="stamp">✅ PAGAMENTO VALIDADO</div>

            <div class="recibo-footer">
                <div>
                    <strong>Assinatura do Morador:</strong><br>
                    _____________________________
                </div>
                <div style="text-align:right;">
                    <strong>Assinatura da Administração:</strong><br>
                    _____________________________
                </div>
            </div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir Recibo</button>
    </div>
</body>
</html>
