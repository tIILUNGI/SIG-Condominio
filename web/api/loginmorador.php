<?php
/**
 * ============================================================================
 * LOGINMORADOR.PHP — Autenticação de Moradores
 * ============================================================================
 * Processa login de moradores do condomínio
 * Tabela: morador
 * Sessão: tipo='morador'
 * Redirecionamento: pages/dashboard_morador.php
 */

session_start();
include("conexao.php");

// ─────────────────────────────────────────────────────────────────────────
// 1. RECEBER E VALIDAR DADOS DO FORMULÁRIO
// ─────────────────────────────────────────────────────────────────────────

$numbi = trim($_POST['numbi'] ?? '');
$senha = $_POST['senha'] ?? '';

// Validação básica — campos não vazios
if (!$numbi || !$senha) {
    header("Location: ../login.html?erro=campos");
    exit;
}

// Verificar conexão com base de dados
if (!$conexao) {
    header("Location: ../login.html?erro=db");
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// 2. BUSCAR MORADOR NA BASE DE DADOS (tabela: morador)
// ─────────────────────────────────────────────────────────────────────────

$stmt = $conexao->prepare("
<<<<<<< HEAD
    SELECT id, nome, email, numbi, senha_hash, estado_conta 
    FROM morador 
    WHERE numbi = ?
=======
    SELECT 
        id, 
        nome, 
        email, 
        numbi, 
        senha_hash, 
        estado_conta 
    FROM morador 
    WHERE numbi = ? 
>>>>>>> 49edb5e (Ajustes baiscos)
    LIMIT 1
");
$stmt->bind_param("s", $numbi);
$stmt->execute();
$res = $stmt->get_result();

// Verificar se morador existe
if ($res->num_rows > 0) {
    $morador = $res->fetch_assoc();

    // ─────────────────────────────────────────────────────────────────────────
    // 3. VERIFICAR STATUS DA CONTA
    // ─────────────────────────────────────────────────────────────────────────
    
<<<<<<< HEAD
    error_log("Morador encontrado:");
    error_log("ID: " . $morador['id']);
    error_log("Nome: " . $morador['nome']);
    error_log("Email: " . $morador['email']);
    error_log("BI: " . $morador['numbi']);
    error_log("Status: " . $morador['estado_conta']);
    error_log("Hash da senha: " . $morador['senha_hash']);
    
    // Verificar se a conta está ativa
=======
>>>>>>> 49edb5e (Ajustes baiscos)
    if ($morador['estado_conta'] !== 'Activo') {
        // Conta não está activa (Suspenso ou Inactivo)
        $stmt->close();
        header("Location: ../login.html?erro=suspenso");
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. VERIFICAR SENHA
    // ─────────────────────────────────────────────────────────────────────────
    // Tentativa 1: password_verify (recomendado para senhas hashadas)
    // Tentativa 2: Comparação directa (fallback para senhas em texto plano)
    
    $senha_valida = password_verify($senha, $morador['senha_hash']) || 
                    $senha === $morador['senha_hash'];

    if ($senha_valida) {
        // ─────────────────────────────────────────────────────────────────────────
        // 5. LOGIN BEM-SUCEDIDO — CRIAR SESSÃO
        // ─────────────────────────────────────────────────────────────────────────
        
<<<<<<< HEAD
        // Iniciar sessão do morador
        $_SESSION['tipo'] = 'morador';
        $_SESSION['id'] = $morador['id'];
        $_SESSION['nome'] = $morador['nome'];
        $_SESSION['email'] = $morador['email'];
        $_SESSION['numbi'] = $morador['numbi'];
        
        // Atualizar último login
=======
        $_SESSION['tipo']   = 'morador';
        $_SESSION['id']     = $morador['id'];
        $_SESSION['nome']   = $morador['nome'];
        $_SESSION['email']  = $morador['email'];
        $_SESSION['numbi']  = $morador['numbi'];

        // Atualizar campo último_login na base de dados
>>>>>>> 49edb5e (Ajustes baiscos)
        $upd = $conexao->prepare("UPDATE morador SET ultimo_login = NOW() WHERE id = ?");
        $upd->bind_param("i", $morador['id']);
        $upd->execute();
        $upd->close();

        // Redirecionar para dashboard do morador
        $stmt->close();
        header("Location: ../pages/dashboard_morador.php");
        exit;
    }
}

// ─────────────────────────────────────────────────────────────────────────
// 6. LOGIN FALHOU — RETORNAR COM ERRO
// ─────────────────────────────────────────────────────────────────────────

$stmt->close();
header("Location: ../login.html?erro=credenciais");
exit;
?>