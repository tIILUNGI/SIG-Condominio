<?php

include("conexao.php");

if (!$conexao) {
    die("Erro de conexão com a base de dados");
}

$bloco = trim($_POST['bloco'] ?? '');
$rua = trim($_POST['rua'] ?? '');
$casanum = intval($_POST['casanum'] ?? 0);
$tipologia = $_POST['tipologia'] ?? 'V3';
$andar = trim($_POST['andar'] ?? '');
$estado = $_POST['estado'] ?? 'Desocupada';

if (!$bloco || !$rua) {
    die("Bloco e Rua são obrigatórios");
}

$codigo = $bloco . "-" . $rua . "-" . $casanum;

$stmt = $conexao->prepare("INSERT INTO casa (bloco, rua, casanum, codigo, tipologia, andar, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssissss", $bloco, $rua, $casanum, $codigo, $tipologia, $andar, $estado);

if ($stmt->execute()) {
    header("Location: dashboard.html");
} else {
    echo "Erro: " . $stmt->error;
}
$stmt->close();
?>