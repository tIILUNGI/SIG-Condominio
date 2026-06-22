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
    // Compatibilidade: a UI atual pode não enviar valor/metodo/referencia.
    // Buscar o valor da mensalidade e registar o pagamento como confirmado.
    $mens = $conexao->prepare("SELECT valor FROM mensalidade WHERE id = ? AND id_morador = ? LIMIT 1");
    $mens->bind_param("ii", $id_mens, $id_morador);
    $mens->execute();
    $r = $mens->get_result();
    $row = $r->fetch_assoc();
    $mens->close();

    $valor = floatval($_POST['valor'] ?? ($row['valor'] ?? 0));
    $metodo = $_POST['metodo'] ?? 'Transferência';
    $referencia = trim($_POST['referencia'] ?? '');

    if ($id_morador && $valor > 0) {
        $ins = $conexao->prepare(
            "INSERT INTO mensalidade_pagamento (id_mensalidade, valor_pago, metodo, referencia, estado)
             VALUES (?, ?, ?, ?, 'confirmado')"
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
