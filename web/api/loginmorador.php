<?php
/**
 * loginmorador.php — Autenticação de Moradores
 * Condomínio Nosso Zimbo
 */
session_start();
include("conexao.php");

// Ativar debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Receber dados do POST
$numbi = trim($_POST['numbi'] ?? '');
$senha = $_POST['senha'] ?? '';

// DEBUG: Log dos dados recebidos
error_log("=== TENTATIVA DE LOGIN MORADOR ===");
error_log("BI recebido: '" . $numbi . "'");
error_log("Senha recebida: '" . $senha . "'");

// Validar campos
if (!$numbi || !$senha) {
    error_log("ERRO: Campos vazios");
    header("Location: ../login.html?erro=campos");
    exit;
}

// Verificar conexão
if (!$conexao) {
    error_log("ERRO: Sem conexão com o banco");
    header("Location: ../login.html?erro=db");
    exit;
}

// Buscar morador por BI
$stmt = $conexao->prepare("
    SELECT id, nome, email, numbi, senha_hash, estado_conta 
    FROM morador 
    WHERE numbi = ?
    LIMIT 1
");
$stmt->bind_param("s", $numbi);
$stmt->execute();
$res = $stmt->get_result();

error_log("Número de registros encontrados: " . $res->num_rows);

if ($res->num_rows > 0) {
    $morador = $res->fetch_assoc();
    
    error_log("Morador encontrado:");
    error_log("ID: " . $morador['id']);
    error_log("Nome: " . $morador['nome']);
    error_log("Email: " . $morador['email']);
    error_log("BI: " . $morador['numbi']);
    error_log("Status: " . $morador['estado_conta']);
    error_log("Hash da senha: " . $morador['senha_hash']);
    
    // Verificar se a conta está ativa
    if ($morador['estado_conta'] !== 'Activo') {
        error_log("ERRO: Conta não está ativa - Status: " . $morador['estado_conta']);
        header("Location: ../login.html?erro=suspenso");
        exit;
    }
    
    // Verificar senha com password_verify
    $senha_valida = password_verify($senha, $morador['senha_hash']);
    error_log("Resultado password_verify: " . ($senha_valida ? 'TRUE' : 'FALSE'));
    
    // Fallback para senha em texto plano
    if (!$senha_valida) {
        $senha_valida = ($senha === $morador['senha_hash']);
        error_log("Resultado fallback (texto plano): " . ($senha_valida ? 'TRUE' : 'FALSE'));
    }
    
    if ($senha_valida) {
        error_log("✅ LOGIN BEM-SUCEDIDO para o morador: " . $morador['nome']);
        
        // Iniciar sessão do morador
        $_SESSION['tipo'] = 'morador';
        $_SESSION['id'] = $morador['id'];
        $_SESSION['nome'] = $morador['nome'];
        $_SESSION['email'] = $morador['email'];
        $_SESSION['numbi'] = $morador['numbi'];
        
        // Atualizar último login
        $upd = $conexao->prepare("UPDATE morador SET ultimo_login = NOW() WHERE id = ?");
        $upd->bind_param("i", $morador['id']);
        $upd->execute();
        $upd->close();
        
        header("Location: ../pages/dashboard_morador.php");
        exit;
    } else {
        error_log("❌ SENHA INCORRETA para o morador: " . $morador['nome']);
    }
} else {
    error_log("❌ NENHUM MORADOR encontrado com o BI: '" . $numbi . "'");
    
    // DEBUG: Mostrar todos os BI's cadastrados
    $check = $conexao->query("SELECT numbI, nome FROM morador");
    error_log("BI's cadastrados no sistema:");
    while ($row = $check->fetch_assoc()) {
        error_log("  - BI: '" . $row['numbi'] . "' | Nome: " . $row['nome']);
    }
}

$stmt->close();

// Se chegou aqui, credenciais inválidas
error_log("=== FIM DA TENTATIVA DE LOGIN (FALHA) ===");
header("Location: ../login.html?erro=credenciais");
exit;
?>