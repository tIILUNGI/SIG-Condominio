<?php
/**
 * ============================================================================
 * CSRF_PROTECTION.PHP — Proteção contra CSRF (Cross-Site Request Forgery)
 * ============================================================================
 * Funções para gerar e validar tokens CSRF
 */

/**
 * Gera um token CSRF e o armazena na sessão
 * Retorna o token para ser incluído em formulários
 */
function csrf_token() {
    // Iniciar sessão se não estiver iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Se não existe token na sessão, gerar um novo
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Valida um token CSRF do formulário com o da sessão
 * Retorna true se válido, false caso contrário
 */
function csrf_validate($token) {
    // Iniciar sessão se não estiver iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar se o token foi fornecido
    if (!isset($token) || empty($token)) {
        error_log("[CSRF] Token não fornecido!");
        return false;
    }

    // Verificar se o token da sessão existe
    if (!isset($_SESSION['csrf_token'])) {
        error_log("[CSRF] Nenhum token na sessão!");
        return false;
    }

    // Comparação segura usando hash_equals (previne timing attacks)
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        error_log("[CSRF] Token inválido! Esperado: " . substr($_SESSION['csrf_token'], 0, 10) . "..., Recebido: " . substr($token, 0, 10) . "...");
        return false;
    }

    // ✅ Token válido!
    return true;
}

/**
 * Campo HTML oculto para formulários
 * Use em todos os formulários: <?php csrf_field(); ?>
 */
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Recuperar token do POST/GET para validação
 */
function csrf_get_token() {
    return $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
}
?>
