<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
    exit;
}

$morador_id = $_SESSION['id'];
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$nova_senha = $_POST['nova_senha'] ?? '';
$conf_senha = $_POST['conf_senha'] ?? '';

if (!$email) {
    echo json_encode(['sucesso' => false, 'erro' => 'Email é obrigatório']);
    exit;
}

$conexao->begin_transaction();
try {
    // 1. Atualizar dados básicos
    $stmt = $conexao->prepare("UPDATE morador SET email = ?, telefone = ? WHERE id = ?");
    $stmt->bind_param("ssi", $email, $telefone, $morador_id);
    $stmt->execute();

    // 2. Atualizar senha se fornecida
    if ($nova_senha) {
        if ($nova_senha !== $conf_senha) {
            throw new Exception("As passwords não coincidem");
        }
        if (strlen($nova_senha) < 4) {
            throw new Exception("A password deve ter pelo menos 4 caracteres");
        }
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmtS = $conexao->prepare("UPDATE morador SET senha_hash = ? WHERE id = ?");
        $stmtS->bind_param("si", $hash, $morador_id);
        $stmtS->execute();
    }

    $conexao->commit();
    // Atualizar sessão
    $_SESSION['email'] = $email;
    echo json_encode(['sucesso' => true]);
} catch (Exception $e) {
    $conexao->rollback();
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
?>
