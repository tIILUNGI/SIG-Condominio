<?php
/**
 * simular_pagamento.php — Simula pagamento de mensalidade pelo morador
 * Suporta Multicaixa Express, ATM/Referência, Transferência Bancária
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

$id_mensalidade = intval($_POST['id_mensalidade'] ?? 0);
$metodo         = trim($_POST['metodo'] ?? 'Multicaixa Express');
$telefone       = trim($_POST['telefone'] ?? '');

if (!$id_mensalidade) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID de mensalidade inválido']);
    exit;
}

if (!$conexao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sem conexão à base de dados']);
    exit;
}

// Verify ownership + that mensalidade exists and is pending
$stmt = $conexao->prepare(
    "SELECT m.id, m.id_morador, m.valor, m.mes, m.ano, m.estado
     FROM mensalidade m
     WHERE m.id = ? AND m.id_morador = ? AND m.estado IN ('pendente','vencida')"
);
$stmt->bind_param("ii", $id_mensalidade, $_SESSION['id']);
$stmt->execute();
$res = $stmt->get_result();
$men = $res->fetch_assoc();
$stmt->close();

if (!$men) {
    echo json_encode(['sucesso' => false, 'erro' => 'Mensalidade não encontrada ou já paga']);
    exit;
}

// Check if already has pending payment
$chk = $conexao->prepare(
    "SELECT id FROM mensalidade_pagamento WHERE id_mensalidade=? AND estado IN ('pendente','pendente_confirmacao')"
);
$chk->bind_param("i", $id_mensalidade);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    $chk->close();
    echo json_encode(['sucesso' => false, 'erro' => 'Já existe um pagamento pendente de confirmação para esta mensalidade']);
    exit;
}
$chk->close();

// Generate unique reference
$referencia = strtoupper(substr($metodo, 0, 2)) . date('ymd') . strtoupper(substr(md5(uniqid()), 0, 6));
$valor      = floatval($men['valor']);
$notas      = "Pagamento simulado via portal · Método: $metodo" . ($telefone ? " · Tel: +244 $telefone" : '');

// Insert payment record
$stmt = $conexao->prepare(
    "INSERT INTO mensalidade_pagamento
        (id_mensalidade, valor_pago, metodo, referencia, data_pagamento, estado, notas_admin)
     VALUES (?, ?, ?, ?, NOW(), 'pendente', ?)"
);
$stmt->bind_param("idsss", $id_mensalidade, $valor, $metodo, $referencia, $notas);

if ($stmt->execute()) {
    // Mark mensalidade as pending confirmation
    $upd = $conexao->prepare("UPDATE mensalidade SET estado='pendente' WHERE id=?");
    $upd->bind_param("i", $id_mensalidade);
    $upd->execute();
    $upd->close();

    echo json_encode([
        'sucesso'    => true,
        'referencia' => $referencia,
        'mensagem'   => 'Pagamento registado. Aguarda confirmação do administrador.'
    ]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
}
$stmt->close();
?>
