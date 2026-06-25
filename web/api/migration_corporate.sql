-- ============================================================
-- SIG-Condominio: Migration for Corporate Standards
-- ============================================================

USE `condominio_nz`;

-- 1. Create Comunicado table properly if not exists
CREATE TABLE IF NOT EXISTS `comunicado` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `titulo`       VARCHAR(255) NOT NULL,
    `conteudo`     TEXT NOT NULL,
    `tipo`         ENUM('informativo', 'urgente', 'manutencao') DEFAULT 'informativo',
    `criado_por`   INT UNSIGNED,
    `criado_em`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_comunicado_admin` FOREIGN KEY (`criado_por`) REFERENCES `administrador`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Enhance Administrador table for profile
ALTER TABLE `administrador` 
    ADD COLUMN IF NOT EXISTS `foto_url` VARCHAR(255) DEFAULT NULL AFTER `funcao`,
    ADD COLUMN IF NOT EXISTS `whatsapp` VARCHAR(20) DEFAULT NULL AFTER `telefone`;

-- 3. Consolidate Mensalidade Pagamento
-- Ensure mensalidade_pagamento has comprovativo_url (it was in the schema but let's be sure)
-- Add id_morador for easier querying if needed, though id_mensalidade -> morador works.
-- Actually the schema had it. Let's just ensure it's clean.

-- 4. Cleanup legacy chat table
-- We keep 'chat_mensagem' data if needed, but we will move to 'mensagem' system.
-- For now, let's just ensure 'mensagem' table is correct.

-- 5. Add notifications for announcements if needed
-- (Notificacao table already exists)

-- 6. Indices for performance
ALTER TABLE `mensagem` ADD INDEX `idx_conversa_lida` (`id_conversa`, `lida`);
ALTER TABLE `mensalidade` ADD INDEX `idx_morador_estado` (`id_morador`, `estado`);
