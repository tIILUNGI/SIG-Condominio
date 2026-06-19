<?php
/**
 * conexao.php — Ligação à base de dados
 * Condomínio Nosso Zimbo
 */

define('DB_HOST',   'localhost');
define('DB_USER',   'root');
define('DB_PASS',   '');  // ← Senha vazia
define('DB_NAME',   'condominio_nz');
define('DB_CHARSET','utf8mb4');

$conexao = null;

// Tenta conexão (suporta socket Unix e TCP)
$hosts = [null, '127.0.0.1', 'localhost'];
foreach ($hosts as $h) {
    $conexao = @mysqli_connect($h, DB_USER, DB_PASS, DB_NAME);
    if ($conexao) break;
}

// Tenta porta alternativa 3307
if (!$conexao) {
    $conexao = @mysqli_connect('127.0.0.1', DB_USER, DB_PASS, DB_NAME, 3307);
}

if ($conexao) {
    mysqli_set_charset($conexao, DB_CHARSET);
    mysqli_query($conexao, "SET time_zone = '+01:00'");
} else {
    // Em modo API retorna JSON; em modo HTML retorna erro legível
    $err = "Erro de ligação à base de dados: " . mysqli_connect_error();
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => false, 'erro' => $err]);
    } else {
        error_log($err);
    }
}
?>
