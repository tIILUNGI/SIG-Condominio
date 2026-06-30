<?php
include(__DIR__ . '/conexao.php');
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
    exit;
}

$sql = "SELECT m.id, m.nome, m.email, m.telefone, m.numbi,
               m.estado_conta, m.tipo_interesse,
               m.preferencia_bloco, m.preferencia_tipologia, m.preferencia_andar,
               m.observacoes, m.criado_em,
               COALESCE(rp.estado, 'PendenteValidacao') as estado_processo,
               rp.notas_admin, rp.validado_em, rp.id_apartamento_atribuido
        FROM morador m
        LEFT JOIN registo_prospecto rp ON rp.id_morador = m.id
        WHERE m.estado_conta IN ('AguardandoValidacaoPagamento','AguardandoAtribuicaoCasa','Aprovado','Pendente')
        ORDER BY m.criado_em DESC";

$res = mysqli_query($conexao, $sql);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
echo json_encode(['sucesso' => true, 'dados' => $rows]);
