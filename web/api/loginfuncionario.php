<?php
/**
 * loginfuncionario.php — Autenticação de Administradores/Funcionários
 */
session_start();
include("conexao.php");

$numbi = trim($_POST['numbi'] ?? '');
$senha = $_POST['senha'] ?? '';

if (!$numbi || !$senha) {
    header("Location: ../login.html?erro=campos");
    exit;
}

if (!$conexao) {
    header("Location: ../login.html?erro=db");
    exit;
}

$stmt = $conexao->prepare("SELECT id, nome, senha_hash, funcao, activo FROM administrador WHERE numbi = ? LIMIT 1");
$stmt->bind_param("s", $numbi);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $a = $res->fetch_assoc();

    if (!$a['activo']) {
        header("Location: ../login.html?erro=suspenso");
        exit;
    }

    // Verificar senha com password_hash ou texto plano
    $ok = password_verify($senha, $a['senha_hash']) || $senha === $a['senha_hash'];

    if ($ok) {
        $_SESSION['tipo']   = 'admin';
        $_SESSION['id']     = $a['id'];
        $_SESSION['nome']   = $a['nome'];
        $_SESSION['funcao'] = $a['funcao'];
        $_SESSION['numbi']  = $numbi;

        $upd = $conexao->prepare("UPDATE administrador SET ultimo_login = NOW() WHERE id = ?");
        $upd->bind_param("i", $a['id']);
        $upd->execute();

        header("Location: ../pages/dashboard.php");
        exit;
    }
}

$stmt->close();
header("Location: ../login.html?erro=credenciais");
exit;
?>