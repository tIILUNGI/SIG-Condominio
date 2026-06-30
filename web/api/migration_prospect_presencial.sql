-- ============================================================
-- SIG-Condominio: Migration — Prospectos + Pagamentos Presenciais
-- ============================================================

USE `condominio_nz`;

-- 1. Expandir estado_conta para incluir fluxo de registo pendente
ALTER TABLE `morador` 
  MODIFY COLUMN `estado_conta` 
  ENUM('Activo','Suspenso','Inactivo','Pendente','AguardandoValidacaoPagamento','AguardandoAtribuicaoCasa','Aprovado') 
  NOT NULL DEFAULT 'Activo';

-- 2. Campos de preferência e intenção de prospecto
ALTER TABLE `morador`
  ADD COLUMN IF NOT EXISTS `tipo_interesse` ENUM('Arrendamento','Compra') NULL AFTER `nacionalidade`,
  ADD COLUMN IF NOT EXISTS `preferencia_bloco` VARCHAR(5) NULL AFTER `tipo_interesse`,
  ADD COLUMN IF NOT EXISTS `preferencia_andar` VARCHAR(10) NULL AFTER `preferencia_bloco`,
  ADD COLUMN IF NOT EXISTS `preferencia_tipologia` VARCHAR(10) NULL AFTER `preferencia_andar`,
  ADD COLUMN IF NOT EXISTS `observacoes` TEXT NULL AFTER `preferencia_tipologia`;

-- 3. Adicionar método Presencial aos pagamentos
ALTER TABLE `mensalidade_pagamento`
  MODIFY COLUMN `metodo`
  ENUM('Transferência','Multicaixa','Dinheiro','TPA','Outro','Presencial') 
  NOT NULL DEFAULT 'Transferência';

-- 4. Índice para acelerar consulta de prospectos pendentes
ALTER TABLE `morador` ADD INDEX IF NOT EXISTS `idx_estado_conta` (`estado_conta`);

-- 5. Tabela para rastrear processo de registo de prospectos
CREATE TABLE IF NOT EXISTS `registo_prospecto` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_morador` INT UNSIGNED NOT NULL,
  `estado` ENUM('PendenteValidacao','Validado','Rejeitado','Atribuido') 
    NOT NULL DEFAULT 'PendenteValidacao',
  `validado_por` INT UNSIGNED NULL,
  `validado_em` DATETIME NULL,
  `comprovativo_url` VARCHAR(255) NULL,
  `notas_admin` TEXT NULL,
  `id_apartamento_atribuido` INT UNSIGNED NULL,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_rp_morador` FOREIGN KEY (`id_morador`) REFERENCES `morador`(`id`),
  CONSTRAINT `fk_rp_admin` FOREIGN KEY (`validado_por`) REFERENCES `administrador`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_rp_apartamento` FOREIGN KEY (`id_apartamento_atribuido`) REFERENCES `apartamento`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Processo de validação e atribuição de casa a prospectos';
