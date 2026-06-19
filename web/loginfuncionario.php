<?php

session_start();

include("conexao.php");

$numbi = trim($_POST['numbi'] ?? '');
$senha = $_POST['senha'] ?? '';

if (!$numbi || !$senha) {
    die("Preencha todos os campos");
}

if (!$conexao) {
    die("Erro de conexão com a base de dados");
}

$stmt = $conexao->prepare("SELECT * FROM funcionarios WHERE numbi=?");
$stmt->bind_param("s", $numbi);
$stmt->execute();
$resultado = $stmt->get_result();

if($resultado->num_rows > 0){
    $funcionario = $resultado->fetch_assoc();
    if ($senha === $funcionario['numbi'] || $senha === $funcionario['email']) {
        $_SESSION['numbi'] = $numbi;
        $_SESSION['funcao'] = $funcionario['funcao'] ?? '';
        header("Location: dashboard.html");
        exit;
    }
}
echo "Número do BI ou senha incorretos.";
$stmt->close();

?>