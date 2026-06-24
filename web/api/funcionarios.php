<?php

include("conexao.php");

if (!$conexao) {
    die("Erro de conexão com a base de dados");
}

$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$nasc = $_POST['nasc'] ?? '';
$nacionalidade = trim($_POST['nacionalidade'] ?? '');
$morada = trim($_POST['morada'] ?? '');
$numbi = trim($_POST['numbi'] ?? '');
$emissao = $_POST['emissao'] ?? '';
$validade = $_POST['validade'] ?? '';
$locale = trim($_POST['locale'] ?? '');
$funcao = $_POST['funcao'] ?? '';
$iban = trim($_POST['iban'] ?? '');

if (!$nome || !$telefone || !$email || !$nasc || !$nacionalidade || !$morada || !$numbi || !$emissao || !$validade || !$locale || !$funcao) {
    die("Todos os campos obrigatórios devem ser preenchidos");
}

$stmt = $conexao->prepare("INSERT INTO funcionarios (nome, telefone, email, nasc, nacionalidade, morada, numbi, emissao, validade, locale, funcao, iban) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssssss", $nome, $telefone, $email, $nasc, $nacionalidade, $morada, $numbi, $emissao, $validade, $locale, $funcao, $iban);

if ($stmt->execute()) {
    header("Location: ../pages/dashboard.php");
} else {
    echo "Erro: " . $stmt->error;
}
$stmt->close();
?>