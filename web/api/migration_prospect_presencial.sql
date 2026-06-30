-- ============================================================
-- SIG-Condominio: Migration — Prospectos + Pagamentos Presenciais
-- ============================================================

USE `condominio_nz`;

-- 1. Verificar se a coluna tipo_interesse já existe, se não, adicionar os novos estados e colunas
SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns 
                    WHERE table_schema = 'condominio_nz' 
                    AND table_name = 'morador' 
                    AND column_name = 'tipo_interesse');

SET @sql1 := 'ALTER TABLE `morador` 
  MODIFY COLUMN `estado_conta` 
  ENUM(''Activo'',''Suspenso'',''Inactivo'',''Pendente'',''AguardandoValidacaoPagamento'',''AguardandoAtribuicaoCasa'',''Aprovado'') 
  NOT NULL DEFAULT ''Activo''';

SET @sql2 := 'ALTER TABLE `morador`
  ADD COLUMN `tipo_interesse` ENUM(''Arrendamento'',''Compra'') NULL AFTER `nacionalidade`,
  ADD COLUMN `preferencia_bloco` VARCHAR(5) NULL AFTER `tipo_interesse`,
  ADD COLUMN `preferencia_andar` VARCHAR(10) NULL AFTER `preferencia_bloco`,
  ADD COLUMN `preferencia_tipologia` VARCHAR(10) NULL AFTER `preferencia_andar`,
  ADD COLUMN `observacoes` TEXT NULL AFTER `preferencia_tipologia`';

PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- 3. Adicionar método Presencial aos pagamentos
SET @sql3 := 'ALTER TABLE `mensalidade_pagamento`
  MODIFY COLUMN `metodo`
  ENUM(''Transferência'',''Multicaixa'',''Dinheiro'',''TPA'',''Outro'',''Presencial'') 
  NOT NULL DEFAULT ''Transferência''';

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- 4. Índice para acelerar consulta de prospectos pendentes
SET @sql4 := 'ALTER TABLE `morador` ADD INDEX IF NOT EXISTS `idx_estado_conta` (`estado_conta`)';
-- Nota: ADD INDEX IF NOT EXISTS não existe no MySQL 5.7, então ignoramos erro se já existir
PREPARE stmt4 FROM @sql4;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

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
