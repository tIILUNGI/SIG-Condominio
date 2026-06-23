<?php
/**
 * ============================================================================
 * RATE_LIMITING.PHP — Proteção contra Brute Force
 * ============================================================================
 * Limita tentativas de login falhadas por IP
 * Máximo: 5 tentativas em 15 minutos
 */

/**
 * Verifica se o IP foi bloqueado por demasiadas tentativas
 * Retorna true se bloqueado, false se permitido
 */
function rate_limit_check() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cache_file = __DIR__ . '/../.cache/rate_limit_' . md5($ip) . '.json';
    $cache_dir = __DIR__ . '/../.cache';

    // Criar diretório cache se não existir
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }

    $now = time();
    $limite_tentativas = 5;
    $janela_tempo = 15 * 60; // 15 minutos em segundos

    // Se ficheiro não existe, criar novo
    if (!file_exists($cache_file)) {
        $data = ['tentativas' => [], 'bloqueado_ate' => 0];
    } else {
        $data = json_decode(file_get_contents($cache_file), true);
    }

    // Limpar tentativas antigas (fora da janela de tempo)
    $data['tentativas'] = array_filter($data['tentativas'], function($timestamp) use ($now, $janela_tempo) {
        return ($now - $timestamp) < $janela_tempo;
    });

    // Se está bloqueado, verificar se ainda está dentro do período de bloqueio
    if ($data['bloqueado_ate'] > $now) {
        $tempo_espera = ceil(($data['bloqueado_ate'] - $now) / 60);
        error_log("[RATE_LIMIT] IP $ip bloqueado por $tempo_espera minutos. Tentativas: " . count($data['tentativas']));
        return true; // BLOQUEADO!
    }

    // Reset do bloqueio se expirou
    $data['bloqueado_ate'] = 0;

    // Se atingiu o limite, bloquear por 15 minutos
    if (count($data['tentativas']) >= $limite_tentativas) {
        $data['bloqueado_ate'] = $now + $janela_tempo;
        file_put_contents($cache_file, json_encode($data, JSON_UNESCAPED_SLASHES));
        error_log("[RATE_LIMIT] IP $ip bloqueado! Limite de tentativas atingido.");
        return true;
    }

    return false; // Permitido
}

/**
 * Registra uma tentativa de login falhada
 */
function rate_limit_fail() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cache_file = __DIR__ . '/../.cache/rate_limit_' . md5($ip) . '.json';
    $cache_dir = __DIR__ . '/../.cache';

    // Criar diretório cache se não existir
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }

    $now = time();

    // Se ficheiro não existe, criar novo
    if (!file_exists($cache_file)) {
        $data = ['tentativas' => [], 'bloqueado_ate' => 0];
    } else {
        $data = json_decode(file_get_contents($cache_file), true);
    }

    // Adicionar nova tentativa
    $data['tentativas'][] = $now;

    // Salvar ficheiro
    file_put_contents($cache_file, json_encode($data, JSON_UNESCAPED_SLASHES));

    $num_tentativas = count($data['tentativas']);
    error_log("[RATE_LIMIT] IP $ip tentativa de login falhou. Total: $num_tentativas/5");
}

/**
 * Limpar o registo de tentativas (após login bem-sucedido)
 */
function rate_limit_reset() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cache_file = __DIR__ . '/../.cache/rate_limit_' . md5($ip) . '.json';

    if (file_exists($cache_file)) {
        @unlink($cache_file);
        error_log("[RATE_LIMIT] IP $ip registado como bem-sucedido. Contador resetado.");
    }
}

/**
 * Retorna tempo de espera em minutos se bloqueado, 0 se permitido
 */
function rate_limit_tempo_espera() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cache_file = __DIR__ . '/../.cache/rate_limit_' . md5($ip) . '.json';

    if (!file_exists($cache_file)) {
        return 0;
    }

    $data = json_decode(file_get_contents($cache_file), true);
    $now = time();

    if ($data['bloqueado_ate'] > $now) {
        return ceil(($data['bloqueado_ate'] - $now) / 60);
    }

    return 0;
}
?>
