<?php
/**
 * pagar.php — Registar pagamento de mensalidade
 */
session_start();
include("conexao.php");

if (!$conexao) { die("Sem ligação à BD"); }

$id_mens = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id_mens <= 0) {
    header("Location: ../pages/morador.php?erro=id_invalido");
    exit;
}

// Marcar mensalidade como paga
$stmt = $conexao->prepare("UPDATE mensalidade SET estado='pago', actualizado_em=NOW() WHERE id=?");
$stmt->bind_param("i", $id_mens);

if ($stmt->execute()) {
    // Registar pagamento
    $id_morador = intval($_SESSION['id'] ?? 0);
    $valor = floatval($_POST['valor'] ?? 0);
    $metodo = $_POST['metodo'] ?? 'Transferência';
    $referencia = trim($_POST['referencia'] ?? '');

    if ($id_morador && $valor > 0) {
        $ins = $conexao->prepare(
            "INSERT INTO mensalidade_pagamento (id_mensalidade, valor_pago, metodo, referencia, estado)
             VALUES (?, ?, ?, ?, 'pendente')"
        );
        $ins->bind_param("idss", $id_mens, $valor, $metodo, $referencia);
        $ins->execute();
        $ins->close();
    }

    header("Location: ../pages/morador.php?ok=pagamento_registado");
} else {
    header("Location: ../pages/morador.php?erro=pagamento_falhou");
}
$stmt->close();
exit;
