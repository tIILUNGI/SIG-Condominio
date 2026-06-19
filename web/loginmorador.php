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

$stmt = $conexao->prepare("SELECT * FROM moradores WHERE numbi=? AND senha=?");
$stmt->bind_param("ss", $numbi, $senha);
$stmt->execute();
$resultado = $stmt->get_result();

if($resultado->num_rows > 0){
    $_SESSION['numbi'] = $numbi;
    header("Location: morador.php");
    exit;
} else {
    echo "Número do BI ou senha incorretos.";
}
$stmt->close();
?>