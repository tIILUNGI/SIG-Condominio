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
$idcasa = intval($_POST['idcasa'] ?? 0);

if (!$nome || !$telefone || !$email || !$nasc || !$nacionalidade || !$morada || !$numbi || !$emissao || !$validade || !$locale) {
    die("Todos os campos obrigatórios devem ser preenchidos");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email inválido");
}

$stmt = $conexao->prepare("INSERT INTO moradores (nome, telefone, email, nasc, nacionalidade, morada, numbi, emissao, validade, locale, idcasa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssssi", $nome, $telefone, $email, $nasc, $nacionalidade, $morada, $numbi, $emissao, $validade, $locale, $idcasa);

if ($stmt->execute()) {
    header("Location: index.html");
} else {
    echo "Erro: " . $stmt->error;
}
$stmt->close();
?>
