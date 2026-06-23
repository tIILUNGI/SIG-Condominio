<?php
/**
 * ============================================================================
 * REGISTAR_ADMIN.PHP — Registo de Funcionário/Administrador
 * ============================================================================
 * Processa o registo de novos funcionários e administradores do condomínio
 * Tabela: administrador
 * Permissões: Apenas admin pode registar outros admins
 */

session_start();
include("conexao.php");
include("csrf_protection.php");

// ✅ PROTEÇÃO CRÍTICA: Apenas admin pode registar outros admins
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') { 
    http_response_code(403);
    die(json_encode(['erro' => 'Acesso negado! Apenas administradores podem registar novos funcionários.', 'codigo' => 403]));
}

// ✅ PROTEÇÃO CSRF: Validar token do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate(csrf_get_token())) {
        http_response_code(403);
        die(json_encode(['erro' => 'Token CSRF inválido! Formulário expirou. Recarregue a página.', 'codigo' => 403]));
    }
}

// Verificar conexão com base de dados
if (!$conexao) { 
    die("Sem ligação à BD"); 
}

// ─────────────────────────────────────────────────────────────────────────
// 1. RECEBER DADOS DO FORMULÁRIO
// ─────────────────────────────────────────────────────────────────────────

$nome          = trim($_POST['nome'] ?? '');
$telefone      = trim($_POST['telefone'] ?? '');
$email         = trim($_POST['email'] ?? '');
$nasc          = $_POST['nasc'] ?? '';
$nacionalidade = trim($_POST['nacionalidade'] ?? 'Angolana');
$morada        = trim($_POST['morada'] ?? '');
$numbi         = trim($_POST['numbi'] ?? '');
$emissao       = $_POST['emissao'] ?? '';
$validade      = $_POST['validade'] ?? '';
$locale        = trim($_POST['locale'] ?? '');
$funcao        = $_POST['funcao'] ?? 'Administrador';
$iban          = trim($_POST['iban'] ?? '');

// Senha padrão = numbi se não fornecida
$senha = $_POST['senha'] ?? $numbi;

// ─────────────────────────────────────────────────────────────────────────
// 2. VALIDAR CAMPOS OBRIGATÓRIOS
// ─────────────────────────────────────────────────────────────────────────

if (!$nome || !$telefone || !$email || !$nasc || !$morada || !$numbi || !$emissao || !$validade || !$locale || !$funcao) {
    header("Location: ../pages/dashboard.php?tab=pedidos&erro=campos");
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// 3. MAPEAR FUNÇÃO (de abreviatura para nome completo)
// ─────────────────────────────────────────────────────────────────────────

$funcaoMap = [
    'adm'       => 'Administrador',
    'rh'        => 'Recursos Humanos',
    'seguranca' => 'Seguranca',
    'at'        => 'Area Tecnica',
];
$funcaoFinal = $funcaoMap[$funcao] ?? $funcao;

// ─────────────────────────────────────────────────────────────────────────
// 4. HASHEAR SENHA (usando PASSWORD_DEFAULT: bcrypt)
// ─────────────────────────────────────────────────────────────────────────

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// ID do condomínio (padrão: 1 — único condomínio)
$id_cond = 1;

// ─────────────────────────────────────────────────────────────────────────
// 5. INSERIR NOVO ADMINISTRADOR NA BASE DE DADOS
// ─────────────────────────────────────────────────────────────────────────

$stmt = $conexao->prepare(
    "INSERT INTO administrador 
     (id_condominio, nome, telefone, email, nasc, nacionalidade, morada, numbi, emissao_bi, validade_bi, locale_bi, funcao, iban, senha_hash)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "isssssssssssss",
    $id_cond, 
    $nome, 
    $telefone, 
    $email, 
    $nasc, 
    $nacionalidade,
    $morada, 
    $numbi, 
    $emissao, 
    $validade, 
    $locale, 
    $funcaoFinal, 
    $iban, 
    $senha_hash
);

// ─────────────────────────────────────────────────────────────────────────
// 6. PROCESSAR RESULTADO
// ─────────────────────────────────────────────────────────────────────────

if ($stmt->execute()) {
    // Sucesso — redirecionar com mensagem positiva
    header("Location: ../pages/dashboard.php?tab=pedidos&ok=funcionario_adicionado");
} else {
    // Erro — redirecionar com mensagem de erro
    header("Location: ../pages/dashboard.php?tab=pedidos&erro=" . urlencode($stmt->error));
}

$stmt->close();
exit;
?>
