<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "meu_banco";

$conexao = null;

$socket = "/tmp/mysql.sock";
if (file_exists($socket)) {
    $conexao = @mysqli_connect($host, $usuario, $senha, $banco, null, $socket);
}

if (!$conexao) {
    $conexao = @mysqli_connect(null, $usuario, $senha, $banco);
}

if (!$conexao) {
    $conexao = @mysqli_connect("127.0.0.1", $usuario, $senha, $banco);
}

if (!$conexao) {
    $conexao = @mysqli_connect("localhost", $usuario, $senha, $banco, 3306);
}

if (!$conexao) {
    $conexao = @mysqli_connect("localhost", $usuario, $senha, $banco, 3307);
}

if ($conexao) {
    mysqli_set_charset($conexao, "utf8mb4");
} else {
    error_log("DB connection failed: " . mysqli_connect_error());
}
?>