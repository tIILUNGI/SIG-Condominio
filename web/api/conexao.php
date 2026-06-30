<?php
/**
 * ============================================================================
 * CONEXAO.PHP — Ligação à Base de Dados
 * ============================================================================
 * Gerencia a conexão com o servidor MySQL
 * Database: condominio_nz
 * Charset: utf8mb4 (suporta caracteres especiais)
 */

// Configuração de conexão
define('DB_HOST',   'localhost');
define('DB_USER',   'root');
define('DB_PASS',   '');              // ← Senha vazia (XAMPP padrão)
define('DB_NAME',   'condominio_nz');
define('DB_CHARSET','utf8mb4');

$conexao = null;

// ─────────────────────────────────────────────────────────────────────────
// TENTAR CONECTAR À BASE DE DADOS
// ─────────────────────────────────────────────────────────────────────────
// Tenta múltiplas estratégias de conexão para suportar diferentes
// configurações de servidor
// ─────────────────────────────────────────────────────────────────────────

$hosts = [null, '127.0.0.1', 'localhost'];
foreach ($hosts as $h) {
    try {
        $conexao = @mysqli_connect($h, DB_USER, DB_PASS, DB_NAME);
        if ($conexao) break;
    } catch (mysqli_sql_exception $e) {
        // Ignorar e tentar o próximo host da lista
    }
}

// Tentar porta alternativa se conexão padrão falhar
if (!$conexao) {
    try {
        $conexao = @mysqli_connect('127.0.0.1', DB_USER, DB_PASS, DB_NAME, 3307);
    } catch (mysqli_sql_exception $e) {
        // Ignorar erro para deixar cair no tratamento de erro abaixo
    }
}

// ─────────────────────────────────────────────────────────────────────────
// CONFIGURAR CHARSET E TIMEZONE
// ─────────────────────────────────────────────────────────────────────────

if ($conexao) {
    // Definir charset para suportar acentos e caracteres especiais
    mysqli_set_charset($conexao, DB_CHARSET);
    
    // Definir timezone (Hora de Angola — UTC+01:00)
    mysqli_query($conexao, "SET time_zone = '+01:00'");
} else {
    // ─────────────────────────────────────────────────────────────────────────
    // ERRO DE CONEXÃO — Tratamento de erro
    // ─────────────────────────────────────────────────────────────────────────
    
    $err = "Erro de ligação à base de dados: " . mysqli_connect_error();
    
    // Retornar JSON se for requisição AJAX/API
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => false, 'erro' => $err]);
    } else {
        // Log de erro para debugging
        error_log($err);
    }
}
?>
