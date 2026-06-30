<?php
session_start();
include(__DIR__ . '/../api/conexao.php');

$id = intval($_GET['id'] ?? 0);
$registado = isset($_GET['registado']);

if (!$id || !$conexao) {
    header("Location: ../login.html");
    exit;
}

$stmt = $conexao->prepare("
    SELECT m.nome, m.email, m.telefone, m.estado_conta,
           rp.estado as estado_processo, rp.notas_admin, rp.validado_em
    FROM morador m
    LEFT JOIN registo_prospecto rp ON rp.id_morador = m.id
    WHERE m.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$dados = $res->fetch_assoc();
$stmt->close();

if (!$dados) {
    header("Location: ../login.html?erro=nao_encontrado");
    exit;
}

$estado_conta = $dados['estado_conta'] ?? 'Desconhecido';
$estado_processo = $dados['estado_processo'] ?? 'PendenteValidacao';

$status_labels = [
    'AguardandoValidacaoPagamento' => 'Aguardando validação do pagamento',
    'AguardandoAtribuicaoCasa' => 'Aguardando atribuição de casa',
    'Aprovado' => 'Aprovado — solicite acesso ao portal',
    'Activo' => 'Activo — já pode aceder ao portal',
    'Pendente' => 'Pendente de análise',
    'Suspenso' => 'Suspenso',
    'Inactivo' => 'Inactivo',
];

$status_color = [
    'AguardandoValidacaoPagamento' => '#f0a500',
    'AguardandoAtribuicaoCasa' => '#3498db',
    'Aprovado' => '#27ae60',
    'Activo' => '#27ae60',
    'Pendente' => '#f0a500',
    'Suspenso' => '#e74c3c',
    'Inactivo' => '#95a5a6',
];

$status_icon = [
    'AguardandoValidacaoPagamento' => 'fa-clock',
    'AguardandoAtribuicaoCasa' => 'fa-house',
    'Aprovado' => 'fa-check-circle',
    'Activo' => 'fa-door-open',
    'Pendente' => 'fa-hourglass-half',
    'Suspenso' => 'fa-ban',
    'Inactivo' => 'fa-circle-xmark',
];

$label = $status_labels[$estado_conta] ?? $estado_conta;
$color = $status_color[$estado_conta] ?? '#888';
$icon = $status_icon[$estado_conta] ?? 'fa-circle-info';
$nome = $dados['nome'] ?? 'Prospecto';
$notas = $dados['notas_admin'] ?? '';
$validado_em = $dados['validado_em'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status do Registo — Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .status-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .status-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 12px 40px rgba(0,0,0,.15);
            text-align: center;
        }
        .status-icon {
            width: 80px; height: 80px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 1.25rem;
        }
        .status-pill {
            display: inline-block;
            padding: .45rem 1.1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: .85rem;
            margin: .5rem 0 1.25rem;
        }
        .info-list {
            text-align: left;
            background: var(--bg);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin: 1rem 0;
        }
        .info-list div {
            display: flex;
            justify-content: space-between;
            padding: .5rem 0;
            font-size: .88rem;
        }
        .info-list .key { color: var(--text-muted); }
        .info-list .val { font-weight: 700; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .8rem 1.5rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: .9rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            margin-top: 1rem;
            transition: all .2s;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .btn-primary:hover {
            opacity: .9;
            transform: translateY(-2px);
        }
        .alert-box {
            background: rgba(240,165,0,.1);
            border: 1px solid rgba(240,165,0,.3);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: .85rem;
            color: var(--text);
            text-align: left;
        }
        .alert-box i { margin-right: .4rem; }
    </style>
</head>
<body>
<div class="status-wrapper">
    <div class="status-card">
        <div class="status-icon" style="background:rgba(<?php echo hexdec(substr($color,1,2)); ?>,<?php echo hexdec(substr($color,3,2)); ?>,<?php echo hexdec(substr($color,5,2)); ?>,.12); color:<?php echo $color; ?>">
            <i class="fa-solid <?php echo $icon; ?>"></i>
        </div>
        <h2 style="margin:.4rem 0;">Olá, <?php echo htmlspecialchars($nome); ?>!</h2>
        <p style="color:var(--text-muted); margin:.2rem 0 1rem;">O seu processo de registo está em andamento.</p>
        <div>
            <span class="status-pill" style="background:rgba(<?php echo hexdec(substr($color,1,2)); ?>,<?php echo hexdec(substr($color,3,2)); ?>,<?php echo hexdec(substr($color,5,2)); ?>,.12); color:<?php echo $color; ?>">
                <i class="fa-solid <?php echo $icon; ?>"></i> <?php echo $label; ?>
            </span>
        </div>

        <div class="info-list">
            <div><span class="key">Nome</span><span class="val"><?php echo htmlspecialchars($nome); ?></span></div>
            <div><span class="key">Email</span><span class="val"><?php echo htmlspecialchars($dados['email'] ?? ''); ?></span></div>
            <div><span class="key">Telefone</span><span class="val"><?php echo htmlspecialchars($dados['telefone'] ?? ''); ?></span></div>
            <div><span class="key">Estado actual</span><span class="val" style="color:<?php echo $color; ?>"><?php echo $label; ?></span></div>
            <?php if ($validado_em): ?>
            <div><span class="key">Validado em</span><span class="val"><?php echo date('d/m/Y H:i', strtotime($validado_em)); ?></span></div>
            <?php endif; ?>
        </div>

        <?php if ($estado_conta === 'AguardandoValidacaoPagamento'): ?>
            <div class="alert-box">
                <i class="fa-solid fa-circle-info" style="color:#f0a500;"></i>
                <strong>Passo seguinte:</strong> Dirija-se à administração do condomínio com o seu documento de identificação (BI) para efectuar o pagamento presencial e validar o seu registo.
            </div>
        <?php elseif ($estado_conta === 'AguardandoAtribuicaoCasa'): ?>
            <div class="alert-box">
                <i class="fa-solid fa-circle-info" style="color:#3498db;"></i>
                <strong>Pagamento confirmado!</strong> Aguarde a atribuição da sua casa. Entraremos em contacto consigo com os detalhes.
            </div>
        <?php elseif ($estado_conta === 'Aprovado' || $estado_conta === 'Activo'): ?>
            <a href="../login.html" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Aceder ao Portal</a>
        <?php endif; ?>

        <?php if ($notas): ?>
            <div class="alert-box" style="margin-top:1rem; background:var(--bg); border-color:var(--border);">
                <i class="fa-solid fa-comment-dots" style="color:var(--primary);"></i>
                <strong>Nota da administração:</strong> <?php echo nl2br(htmlspecialchars($notas)); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
