<?php
/**
 * casa.php — Adicionar novo apartamento
 */
session_start();
include("conexao.php");

if (!$conexao) { die("Sem ligação à BD"); }

$id_bloco  = intval($_POST['id_bloco'] ?? 1);
$numero    = trim($_POST['casanum'] ?? '');
$andar     = intval($_POST['andar'] ?? 0);
$tipologia = $_POST['tipologia'] ?? 'V3';
$estado    = $_POST['estado'] ?? 'Disponivel';
$bloco_letra = trim($_POST['bloco'] ?? 'A');

if (!$numero) {
    header("Location: ../pages/dashboard.php?tab=casas&erro=campos");
    exit;
}

// Gerar código único
$codigo = strtoupper($bloco_letra) . '-' . $numero;

$stmt = $conexao->prepare(
    "INSERT INTO apartamento (id_bloco, numero, andar, tipologia, estado, codigo) VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("iissss", $id_bloco, $numero, $andar, $tipologia, $estado, $codigo);

if ($stmt->execute()) {
    header("Location: ../pages/dashboard.php?tab=casas&ok=casa_adicionada");
} else {
    header("Location: ../pages/dashboard.php?tab=casas&erro=" . urlencode($stmt->error));
}
$stmt->close();
exit;
