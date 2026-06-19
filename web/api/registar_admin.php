<?php
/**
 * registar_admin.php — Registo de funcionário/administrador
 */
session_start();
include("conexao.php");

// Apenas super admin pode criar outros admins
// if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') { http_response_code(403); exit; }

if (!$conexao) { die("Sem ligação à BD"); }

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
$funcao        = $_POST['funcao'] ?? 'Administrador';
$iban          = trim($_POST['iban'] ?? '');
$senha         = $_POST['senha'] ?? $numbi; // Senha padrão = numbi se não fornecida

if (!$nome || !$telefone || !$email || !$nasc || !$morada || !$numbi || !$emissao || !$validade || !$locale || !$funcao) {
    header("Location: ../pages/dashboard.php?tab=pedidos&erro=campos");
    exit;
}

$funcaoMap = [
    'adm'       => 'Administrador',
    'rh'        => 'Recursos Humanos',
    'seguranca' => 'Seguranca',
    'at'        => 'Area Tecnica',
];
$funcaoFinal = $funcaoMap[$funcao] ?? $funcao;

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
$id_cond = 1;

$stmt = $conexao->prepare(
    "INSERT INTO administrador (id_condominio, nome, telefone, email, nasc, nacionalidade, morada, numbi, emissao_bi, validade_bi, locale_bi, funcao, iban, senha_hash)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("isssssssssssss",
    $id_cond, $nome, $telefone, $email, $nasc, $nacionalidade,
    $morada, $numbi, $emissao, $validade, $locale, $funcaoFinal, $iban, $senha_hash
);

if ($stmt->execute()) {
    header("Location: ../pages/dashboard.php?tab=pedidos&ok=funcionario_adicionado");
} else {
    header("Location: ../pages/dashboard.php?tab=pedidos&erro=" . urlencode($stmt->error));
}
$stmt->close();
exit;
