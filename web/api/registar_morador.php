<?php
/**
 * registar_morador.php — Registo de novo morador
 */
include("conexao.php");

if (!$conexao) { die(json_encode(['sucesso'=>false,'erro'=>'Sem ligação à BD'])); }

$nome          = trim($_POST['nome'] ?? '');
$telefone      = trim($_POST['telefone'] ?? '');
$email         = trim($_POST['email'] ?? '');
$nasc          = $_POST['nasc'] ?? '';
$nacionalidade = trim($_POST['nacionalidade'] ?? 'Angolana');
$morada        = trim($_POST['morada'] ?? '');
$numbi         = trim($_POST['numbi'] ?? '');
$emissao       = $_POST['emissao'] ?? '';
$validade      = $_POST['validade'] ?? '';
$locale        = trim($_POST['locale'] ?? '');
$senha         = $_POST['senha'] ?? '';

// Validação
if (!$nome || !$telefone || !$email || !$nasc || !$morada || !$numbi || !$emissao || !$validade || !$locale || !$senha) {
    header("Location: ../login.html?erro=campos_reg");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../login.html?erro=email");
    exit;
}

if (strlen($senha) < 6) {
    header("Location: ../login.html?erro=senha_fraca");
    exit;
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Verificar se BI ja existe
$chk = $conexao->prepare("SELECT id FROM morador WHERE numbi=? OR email=? LIMIT 1");
$chk->bind_param("ss", $numbi, $email);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    header("Location: ../login.html?erro=duplicado");
    exit;
}

$stmt = $conexao->prepare(
    "INSERT INTO morador (nome, telefone, email, nasc, nacionalidade, morada_anterior, numbi, emissao_bi, validade_bi, locale_bi, senha_hash)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssssssssss",
    $nome, $telefone, $email, $nasc, $nacionalidade,
    $morada, $numbi, $emissao, $validade, $locale, $senha_hash
);

if ($stmt->execute()) {
    header("Location: ../login.html?ok=registado");
} else {
    header("Location: ../login.html?erro=bd");
}
$stmt->close();
exit;
