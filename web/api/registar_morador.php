<?php
/**
 * ============================================================================
 * REGISTAR_MORADOR.PHP — Registo de Novo Morador
 * ============================================================================
 * Processa o registo de novos moradores (prospect/visitante)
 * Tabela: morador
 * Acesso: Público (não requer autenticação)
 */

session_start();
include("conexao.php");
include("csrf_protection.php");

// ✅ PROTEÇÃO CSRF: Validar token do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate(csrf_get_token())) {
        http_response_code(403);
        die(json_encode(['erro' => 'Token CSRF inválido! Formulário expirou. Recarregue a página.', 'codigo' => 403]));
    }
}

// ─────────────────────────────────────────────────────────────────────────
// 1. VERIFICAR CONEXÃO
// ─────────────────────────────────────────────────────────────────────────

if (!$conexao) {
    header("Location: ../login.html?erro=db");
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// 2. RECEBER DADOS DO FORMULÁRIO
// ─────────────────────────────────────────────────────────────────────────

$nome          = trim($_POST['nome'] ?? '');
$telefone      = trim($_POST['telefone'] ?? '');
$email         = trim($_POST['email'] ?? '');
$numbi         = trim($_POST['numbi'] ?? '');
$senha         = $_POST['senha'] ?? '';

// Campos opcionais — valores padrão se não fornecidos
$nasc          = trim($_POST['nasc'] ?? '');
$nacionalidade = trim($_POST['nacionalidade'] ?? 'Angolana');
$morada        = trim($_POST['morada'] ?? 'Luanda');
$emissao       = trim($_POST['emissao'] ?? '');
$validade      = trim($_POST['validade'] ?? '');
$locale        = trim($_POST['locale'] ?? 'Luanda');

// ─────────────────────────────────────────────────────────────────────────
// 3. DEFINIR VALORES PADRÃO PARA CAMPOS OPCIONAIS
// ─────────────────────────────────────────────────────────────────────────

if ($nasc === '') {
    // Nascimento padrão: 20 anos atrás
    $nasc = date('Y-m-d', strtotime('-20 years'));
}
if ($emissao === '') {
    // Emissão padrão: data de hoje
    $emissao = date('Y-m-d');
}
if ($validade === '') {
    // Validade padrão: 5 anos no futuro
    $validade = date('Y-m-d', strtotime('+5 years'));
}

// ─────────────────────────────────────────────────────────────────────────
// 4. VALIDAÇÃO MÍNIMA — CAMPOS OBRIGATÓRIOS
// ─────────────────────────────────────────────────────────────────────────

if (!$nome || !$telefone || !$email || !$numbi || !$senha) {
    header("Location: ../login.html?erro=campos_reg");
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// 5. VALIDAÇÃO DE EMAIL
// ─────────────────────────────────────────────────────────────────────────

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../login.html?erro=email");
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// 6. VALIDAÇÃO DE FORÇA DE SENHA
// ─────────────────────────────────────────────────────────────────────────

if (strlen($senha) < 6) {
    header("Location: ../login.html?erro=senha_fraca");
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// 7. HASHEAR SENHA (usando PASSWORD_DEFAULT: bcrypt)
// ─────────────────────────────────────────────────────────────────────────

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// ─────────────────────────────────────────────────────────────────────────
// 8. VERIFICAR SE BI OU EMAIL JÁ EXISTEM
// ─────────────────────────────────────────────────────────────────────────

$chk = $conexao->prepare("SELECT id FROM morador WHERE numbi = ? OR email = ? LIMIT 1");
$chk->bind_param("ss", $numbi, $email);
$chk->execute();
$chk->store_result();

if ($chk->num_rows > 0) {
    // BI ou Email já registado
    header("Location: ../login.html?erro=duplicado");
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// 9. INSERIR NOVO MORADOR NA BASE DE DADOS
// ─────────────────────────────────────────────────────────────────────────

$estado_conta = 'Activo';

$stmt = $conexao->prepare(
    "INSERT INTO morador 
     (nome, email, numbi, telefone, senha_hash, nasc, nacionalidade, morada_anterior, emissao_bi, validade_bi, locale_bi, estado_conta)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "ssssssssssss",
    $nome,
    $email,
    $numbi,
    $telefone,
    $senha_hash,
    $nasc,
    $nacionalidade,
    $morada,
    $emissao,
    $validade,
    $locale,
    $estado_conta
);

// ─────────────────────────────────────────────────────────────────────────
// 10. PROCESSAR RESULTADO
// ─────────────────────────────────────────────────────────────────────────

if ($stmt->execute()) {
    // Sucesso — redirecionar para login com mensagem positiva
    header("Location: ../login.html?ok=registado");
} else {
    // Erro ao guardar — retornar com mensagem de erro
    header("Location: ../login.html?erro=bd");
}

$stmt->close();
exit;
?>

