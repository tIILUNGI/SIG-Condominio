<?php
session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include(__DIR__ . '/../api/conexao.php');

$sql = "SELECT m.nome, m.email, m.telefone, m.numbi,
               m.estado_conta, m.tipo_interesse,
               m.preferencia_bloco, m.preferencia_tipologia, m.preferencia_andar,
               m.observacoes, m.criado_em,
               COALESCE(rp.estado, 'PendenteValidacao') as estado_processo,
               rp.notas_admin, rp.validado_em
        FROM morador m
        LEFT JOIN registo_prospecto rp ON rp.id_morador = m.id
        WHERE m.estado_conta IN ('AguardandoValidacaoPagamento','AguardandoAtribuicaoCasa','Aprovado','Pendente')
        ORDER BY m.criado_em DESC";

$res = mysqli_query($conexao, $sql);
$prospectos = [];
while ($r = mysqli_fetch_assoc($res)) $prospectos[] = $r;

$estado_labels = [
    'AguardandoValidacaoPagamento' => 'Aguardando Validação de Pagamento',
    'AguardandoAtribuicaoCasa' => 'Aguardando Atribuição de Casa',
    'Aprovado' => 'Aprovado',
    'Pendente' => 'Pendente de Análise',
    'Activo' => 'Activo',
    'Suspenso' => 'Suspenso',
    'Inactivo' => 'Inactivo',
];
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Prospectos — Nosso Zimbo</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #222; margin: 0; background: #fff; }
        .relatorio { max-width: 900px; margin: 0 auto; }
        .rel-header {
            text-align: center; border-bottom: 3px solid #1a252f; padding-bottom: 1rem; margin-bottom: 1.5rem;
        }
        .rel-header h1 { margin: .3rem 0; font-size: 1.5rem; }
        .rel-header p { margin: .2rem 0; color: #555; font-size: .85rem; }
        .rel-meta { text-align: right; font-size: .8rem; color: #777; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th { background: #1a252f; color: #fff; padding: .6rem .8rem; font-size: .75rem; text-transform: uppercase; text-align: left; }
        td { padding: .55rem .8rem; border-bottom: 1px solid #ddd; font-size: .85rem; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .badge { padding: .2rem .5rem; border-radius: 12px; font-size: .7rem; font-weight: 700; text-transform: uppercase; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-validated { background: #d1ecf1; color: #0c5460; }
        .badge-assigned { background: #d4edda; color: #155724; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-rejected { background: #f8d7da; color: #721c24; }
        .rel-footer { margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #1a252f; display: flex; justify-content: space-between; font-size: .8rem; color: #777; }
        @media print {
            body * { visibility: hidden; }
            .relatorio, .relatorio * { visibility: visible; }
            .relatorio { position: absolute; left: 0; top: 0; width: 100%; }
        }
        .no-print { text-align: center; margin-top: 1.5rem; }
        .no-print button { padding: .7rem 1.5rem; border-radius: 10px; border: none; cursor: pointer; font-weight: 700; background: #1a252f; color: #fff; }
    </style>
</head>
<body>
    <div class="relatorio">
        <div class="rel-header">
            <h1>Condomínio Nosso Zimbo</h1>
            <p>Relatório de Prospectos / Cadastros Pendentes</p>
            <p>Gerado em: <?= date('d/m/Y H:i') ?></p>
        </div>

        <p style="font-size:.85rem; color:#555; margin-bottom:1rem;">
            Total de prospectos: <strong><?= count($prospectos) ?></strong>
        </p>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Contacto</th>
                    <th>Interesse</th>
                    <th>Preferências</th>
                    <th>Data Registo</th>
                    <th>Estado</th>
                    <th>Notas Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prospectos as $p): 
                    $pref = [$p['preferencia_bloco'], $p['preferencia_tipologia'], $p['preferencia_andar']];
                    $pref = array_filter($pref);
                    $pref_str = !empty($pref) ? implode(', ', $pref) : '—';
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['nome']) ?></strong></td>
                    <td>
                        <div><?= htmlspecialchars($p['email']) ?></div>
                        <div style="color:#777;"><?= htmlspecialchars($p['telefone']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($p['tipo_interesse'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($pref_str) ?></td>
                    <td><?= $p['criado_em'] ? date('d/m/Y', strtotime($p['criado_em'])) : '—' ?></td>
                    <td>
                        <span class="badge 
                            <?= $p['estado_conta']==='AguardandoValidacaoPagamento'?'badge-pending':($p['estado_conta']==='AguardandoAtribuicaoCasa'?'badge-validated':($p['estado_conta']==='Aprovado'?'badge-approved':'badge-pending')) ?>">
                            <?= htmlspecialchars($estado_labels[$p['estado_conta']] ?? $p['estado_conta']) ?>
                        </span>
                    </td>
                    <td style="font-size:.8rem; color:#555;"><?= htmlspecialchars($p['notas_admin'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="rel-footer">
            <span>Total: <?= count($prospectos) ?> prospectos</span>
            <span>Assinatura: _____________________</span>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir / Salvar PDF</button>
    </div>
</body>
</html>
