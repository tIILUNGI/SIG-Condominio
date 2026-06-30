<?php
/**
 * ============================================================================
 * LOGINFUNCIONARIO.PHP — Autenticação de Funcionários/Administradores
 * ============================================================================
 * Processa login de funcionários e administradores do condomínio
 * Tabela: administrador
 * Sessão: tipo='admin'
 */

session_start();

// Ativar erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_debug.log');

// Logging função
function debug_log($msg) {
    error_log("[" . date('Y-m-d H:i:s') . "] " . $msg);
}

debug_log("=== TENTATIVA DE LOGIN ADMIN ===");

include("conexao.php");

// ─────────────────────────────────────────────────────────────────────────
// 1. RECEBER E VALIDAR DADOS DO FORMULÁRIO
// ─────────────────────────────────────────────────────────────────────────

$numbi = trim($_POST['numbi'] ?? '');
$senha = $_POST['senha'] ?? '';

debug_log("BI recebido: '$numbi' | Senha recebida: (não exibida por segurança)");

// Validação básica
if (!$numbi || !$senha) {
    debug_log("ERRO: Campos vazios - BI: '$numbi' | Senha: '" . ($senha ? 'preenchida' : 'vazia') . "'");
    header("Location: ../login.html?erro=campos");
    exit;
}

// Verificar conexão com base de dados
if (!$conexao) {
    debug_log("ERRO: Sem conexão com base de dados - " . mysqli_connect_error());
    header("Location: ../login.html?erro=db");
    exit;
}

debug_log("✅ Conexão com BD estabelecida");

// ─────────────────────────────────────────────────────────────────────────
// 2. BUSCAR FUNCIONÁRIO NA BASE DE DADOS (tabela: administrador)
// ─────────────────────────────────────────────────────────────────────────

$stmt = $conexao->prepare("
    SELECT 
        id, 
        nome, 
        numbi,
        senha_hash, 
        funcao, 
        activo 
    FROM administrador 
    WHERE numbi = ? 
    LIMIT 1
");

if (!$stmt) {
    debug_log("ERRO ao preparar query: " . $conexao->error);
    header("Location: ../login.html?erro=db");
    exit;
}

$stmt->bind_param("s", $numbi);
$stmt->execute();
$res = $stmt->get_result();

debug_log("Query executada - Registros encontrados: " . $res->num_rows);

// Verificar se funcionário existe
if ($res->num_rows > 0) {
    $admin = $res->fetch_assoc();
    
    debug_log("Admin encontrado:");
    debug_log("  ID: " . $admin['id']);
    debug_log("  Nome: " . $admin['nome']);
    debug_log("  BI: " . $admin['numbi']);
    debug_log("  Función: " . $admin['funcao']);
    debug_log("  Activo: " . ($admin['activo'] ? 'SIM' : 'NÃO'));

    // ─────────────────────────────────────────────────────────────────────────
    // 3. VERIFICAR STATUS DA CONTA
    // ─────────────────────────────────────────────────────────────────────────
    
    if (!$admin['activo']) {
        // Conta desativada/suspensa
        debug_log("ERRO: Conta inactiva/suspensa");
        $stmt->close();
        header("Location: ../login.html?erro=suspenso");
        exit;
    }

    debug_log("✅ Conta está ativa");

    // ─────────────────────────────────────────────────────────────────────────
    // 4. VERIFICAR SENHA (com suporte a hash e texto plano como fallback)
    // ─────────────────────────────────────────────────────────────────────────
    
    $senha_valida = password_verify($senha, $admin['senha_hash']) || 
                    $senha === $admin['senha_hash'];
    
    debug_log("Password_verify: " . (password_verify($senha, $admin['senha_hash']) ? 'TRUE' : 'FALSE'));
    debug_log("Senha válida: " . ($senha_valida ? 'SIM' : 'NÃO'));

    if ($senha_valida) {
        debug_log("✅ LOGIN BEM-SUCEDIDO!");
        
        // ─────────────────────────────────────────────────────────────────────────
        // 5. LOGIN BEM-SUCEDIDO — CRIAR SESSÃO
        // ─────────────────────────────────────────────────────────────────────────
        
        // Administrador = acesso total; outras funções = funcionario (acesso restrito)
        $adminRoles         = ['Administrador'];
        $_SESSION['tipo']   = in_array($admin['funcao'], $adminRoles) ? 'admin' : 'funcionario';
        $_SESSION['id']     = $admin['id'];
        $_SESSION['nome']   = $admin['nome'];
        $_SESSION['funcao'] = $admin['funcao'];
        $_SESSION['numbi']  = $numbi;
        
        debug_log("Sessão criada com sucesso");

        // Atualizar último login na base de dados
        $upd = $conexao->prepare("UPDATE administrador SET ultimo_login = NOW() WHERE id = ?");
        if ($upd) {
            $upd->bind_param("i", $admin['id']);
            $upd->execute();
            $upd->close();
            debug_log("Último login atualizado");
        }

        // Redirecionar para dashboard
        $stmt->close();
        debug_log("Redirecionando para dashboard...");
        header("Location: ../pages/admin_portal.php");
        exit;
    } else {
        debug_log("❌ ERRO: Senha incorreta para BI: $numbi");
    }
} else {
    debug_log("❌ ERRO: Nenhum admin encontrado com BI: '$numbi'");
    
    // Debug: Listar todos os BIs cadastrados
    $check = $conexao->query("SELECT id, numbi, nome FROM administrador");
    if ($check && $check->num_rows > 0) {
        debug_log("Admins cadastrados no sistema:");
        while ($row = $check->fetch_assoc()) {
            debug_log("  - BI: '" . $row['numbi'] . "' | Nome: " . $row['nome']);
        }
    } else {
        debug_log("⚠️ AVISO: Nenhum admin cadastrado na tabela administrador!");
    }
}

// ─────────────────────────────────────────────────────────────────────────
// 6. LOGIN FALHOU — RETORNAR COM ERRO
// ─────────────────────────────────────────────────────────────────────────

$stmt->close();
debug_log("=== FIM DA TENTATIVA (FALHA) ===");
header("Location: ../login.html?erro=credenciais");
exit;