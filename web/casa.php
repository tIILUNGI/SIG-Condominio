<?php
/**
 * casa.php — Adicionar novo apartamento via formulário
 */
session_start();
include("api/conexao.php");

if (!$conexao) { die("Sem ligação à BD"); }

$bloco = strtoupper(trim($_POST['bloco'] ?? 'A'));
$numero = trim($_POST['casanum'] ?? '');
$andar = intval($_POST['andar'] ?? 0);
$tipologia = $_POST['tipologia'] ?? 'V3';

if (!$numero) {
    header("Location: dashboard.php?tab=casas&erro=campos");
    exit;
}

// Buscar ID do bloco pela letra
$bloco_stmt = $conexao->prepare("SELECT id FROM bloco WHERE letra = ?");
$bloco_stmt->bind_param("s", $bloco);
$bloco_stmt->execute();
$bloco_result = $bloco_stmt->get_result();

if ($bloco_result->num_rows === 0) {
    // Criar bloco se não existir
    $bloco_stmt = $conexao->prepare("INSERT INTO bloco (id_condominio, letra, descricao) VALUES (1, ?, ?)");
    $bloco_stmt->bind_param("ss", $bloco, $descricao);
    $descricao = "Bloco $bloco";
    $bloco_stmt->execute();
    $id_bloco = $bloco_stmt->insert_id;
    $bloco_stmt->close();
} else {
    $id_bloco = $bloco_result->fetch_assoc()['id'];
    $bloco_stmt->close();
}

$codigo = $bloco . '-' . $numero;

$stmt = $conexao->prepare(
    "INSERT INTO apartamento (id_bloco, numero, andar, tipologia, estado, codigo) 
     VALUES (?, ?, ?, ?, 'Disponivel', ?)"
);
$stmt->bind_param("iisss", $id_bloco, $numero, $andar, $tipologia, $codigo);

if ($stmt->execute()) {
    header("Location: dashboard.php?tab=casas&ok=casa_adicionada");
} else {
    header("Location: dashboard.php?tab=casas&erro=" . urlencode($stmt->error));
}
$stmt->close();
exit;