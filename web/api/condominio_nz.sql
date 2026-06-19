-- ============================================================
--  Condomínio Nosso Zimbo — Base de Dados Completa
--  Schema: condominio_nz
--  Versão: 2.0  |  Data: 2026-06-19
-- ============================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ------------------------------------------------------------
-- Criar e usar schema
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `condominio_nz` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `condominio_nz`;

-- ============================================================
--  DOMÍNIO 1 — NÚCLEO (estrutura física)
-- ============================================================

-- 1. CONDOMINIO
CREATE TABLE IF NOT EXISTS `condominio` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nome`         VARCHAR(120)    NOT NULL,
  `morada`       VARCHAR(255)    NOT NULL,
  `cidade`       VARCHAR(100)    NOT NULL DEFAULT 'Luanda',
  `pais`         VARCHAR(60)     NOT NULL DEFAULT 'Angola',
  `nif`          VARCHAR(20)     NULL,
  `telefone`     VARCHAR(20)     NULL,
  `email`        VARCHAR(120)    NULL,
  `mensalidade_base` DECIMAL(12,2) NOT NULL DEFAULT 140000.00 COMMENT 'Quota padrão mensal em Kz',
  `multa_diaria` DECIMAL(12,2)   NOT NULL DEFAULT 500.00,
  `iban`         VARCHAR(60)     NULL COMMENT 'IBAN de recebimento',
  `banco`        VARCHAR(80)     NULL,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_em` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Dados gerais do condomínio';

-- 2. BLOCO
CREATE TABLE IF NOT EXISTS `bloco` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_condominio` INT UNSIGNED   NOT NULL,
  `letra`        VARCHAR(5)      NOT NULL COMMENT 'Ex: A, B, C',
  `descricao`    VARCHAR(120)    NULL,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bloco_cond` (`id_condominio`, `letra`),
  CONSTRAINT `fk_bloco_cond` FOREIGN KEY (`id_condominio`) REFERENCES `condominio`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Blocos / torres do condomínio';

-- 3. APARTAMENTO (substitui casa — mais genérico)
CREATE TABLE IF NOT EXISTS `apartamento` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_bloco`     INT UNSIGNED    NOT NULL,
  `numero`       VARCHAR(10)     NOT NULL COMMENT 'Ex: 101, 202B',
  `andar`        TINYINT         NOT NULL DEFAULT 0,
  `tipologia`    VARCHAR(10)     NOT NULL DEFAULT 'V3' COMMENT 'T1, T2, T3, V3, etc.',
  `area_m2`      DECIMAL(8,2)    NULL,
  `estado`       ENUM('Disponivel','Ocupado','Manutencao','Reservado') NOT NULL DEFAULT 'Disponivel',
  `codigo`       VARCHAR(20)     NULL UNIQUE COMMENT 'Código único: A-101',
  `obs`          TEXT            NULL,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_em` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_apt_bloco` (`id_bloco`, `numero`),
  CONSTRAINT `fk_apt_bloco` FOREIGN KEY (`id_bloco`) REFERENCES `bloco`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Apartamentos / fracções do condomínio';

-- ============================================================
--  DOMÍNIO 2 — PESSOAS & ACESSOS
-- ============================================================

-- 4. ADMINISTRADOR
CREATE TABLE IF NOT EXISTS `administrador` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_condominio` INT UNSIGNED   NOT NULL,
  `nome`         VARCHAR(120)    NOT NULL,
  `email`        VARCHAR(120)    NOT NULL UNIQUE,
  `numbi`        VARCHAR(20)     NOT NULL UNIQUE,
  `telefone`     VARCHAR(20)     NULL,
  `funcao`       ENUM('Super Admin','Administrador','Recursos Humanos','Seguranca','Area Tecnica') NOT NULL DEFAULT 'Administrador',
  `iban`         VARCHAR(60)     NULL,
  `senha_hash`   VARCHAR(255)    NOT NULL,
  `activo`       TINYINT(1)      NOT NULL DEFAULT 1,
  `nasc`         DATE            NULL,
  `nacionalidade` VARCHAR(60)   NULL DEFAULT 'Angolana',
  `morada`       VARCHAR(255)    NULL,
  `emissao_bi`   DATE            NULL,
  `validade_bi`  DATE            NULL,
  `locale_bi`    VARCHAR(80)     NULL,
  `ultimo_login` DATETIME        NULL,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_em` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_adm_cond` FOREIGN KEY (`id_condominio`) REFERENCES `condominio`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Funcionários e administradores do condomínio';

-- 5. MORADOR
CREATE TABLE IF NOT EXISTS `morador` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nome`         VARCHAR(120)    NOT NULL,
  `email`        VARCHAR(120)    NOT NULL UNIQUE,
  `numbi`        VARCHAR(20)     NOT NULL UNIQUE COMMENT 'BI — usado para login',
  `telefone`     VARCHAR(20)     NOT NULL,
  `senha_hash`   VARCHAR(255)    NOT NULL COMMENT 'password_hash()',
  `nasc`         DATE            NOT NULL,
  `nacionalidade` VARCHAR(60)   NOT NULL DEFAULT 'Angolana',
  `morada_anterior` VARCHAR(255) NULL,
  `emissao_bi`   DATE            NOT NULL,
  `validade_bi`  DATE            NOT NULL,
  `locale_bi`    VARCHAR(80)     NOT NULL,
  `estado_conta` ENUM('Activo','Suspenso','Inactivo') NOT NULL DEFAULT 'Activo',
  `ultimo_login` DATETIME        NULL,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_em` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Moradores registados no portal';

-- 6. MORADOR_APARTAMENTO (histórico de ocupação)
CREATE TABLE IF NOT EXISTS `morador_apartamento` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_morador`   INT UNSIGNED    NOT NULL,
  `id_apartamento` INT UNSIGNED  NOT NULL,
  `data_entrada` DATE            NOT NULL,
  `data_saida`   DATE            NULL COMMENT 'NULL = ainda ocupa',
  `activo`       TINYINT(1)      NOT NULL DEFAULT 1,
  `obs`          TEXT            NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ma_morador`     FOREIGN KEY (`id_morador`)    REFERENCES `morador`(`id`),
  CONSTRAINT `fk_ma_apartamento` FOREIGN KEY (`id_apartamento`) REFERENCES `apartamento`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Associação morador↔apartamento com histórico de ocupação';

-- ============================================================
--  DOMÍNIO 3 — FINANCEIRO
-- ============================================================

-- 7. AQUISICAO (processo de compra/arrendamento do apartamento)
CREATE TABLE IF NOT EXISTS `aquisicao` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_morador`   INT UNSIGNED    NOT NULL,
  `id_apartamento` INT UNSIGNED  NOT NULL,
  `tipo`         ENUM('Renda','Compra','Reserva') NOT NULL DEFAULT 'Renda',
  `valor_total`  DECIMAL(14,2)   NOT NULL,
  `data_contrato` DATE           NOT NULL,
  `data_inicio`  DATE            NOT NULL,
  `data_fim`     DATE            NULL,
  `estado`       ENUM('Activo','Concluido','Cancelado') NOT NULL DEFAULT 'Activo',
  `notas`        TEXT            NULL,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_aq_morador`     FOREIGN KEY (`id_morador`)    REFERENCES `morador`(`id`),
  CONSTRAINT `fk_aq_apartamento` FOREIGN KEY (`id_apartamento`) REFERENCES `apartamento`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Contratos de aquisição / arrendamento';

-- 8. MENSALIDADE (quota gerada por mês/apartamento)
CREATE TABLE IF NOT EXISTS `mensalidade` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_morador`   INT UNSIGNED    NOT NULL,
  `id_apartamento` INT UNSIGNED  NOT NULL,
  `servico`      ENUM('Renda Mensal','Quota Condominal','Manutencao','Multa') NOT NULL DEFAULT 'Quota Condominal',
  `mes`          TINYINT UNSIGNED NOT NULL COMMENT '1-12',
  `ano`          YEAR            NOT NULL,
  `valor`        DECIMAL(12,2)   NOT NULL,
  `vencimento`   DATE            NOT NULL,
  `estado`       ENUM('pendente','pago','atrasado','dispensado') NOT NULL DEFAULT 'pendente',
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_em` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mens` (`id_morador`, `id_apartamento`, `servico`, `mes`, `ano`),
  CONSTRAINT `fk_mens_morador`     FOREIGN KEY (`id_morador`)    REFERENCES `morador`(`id`),
  CONSTRAINT `fk_mens_apartamento` FOREIGN KEY (`id_apartamento`) REFERENCES `apartamento`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Quotas mensais geradas por apartamento';

-- 9. MENSALIDADE_PAGAMENTO (cada pagamento efectuado)
CREATE TABLE IF NOT EXISTS `mensalidade_pagamento` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_mensalidade`   INT UNSIGNED    NOT NULL,
  `valor_pago`       DECIMAL(12,2)   NOT NULL,
  `metodo`           ENUM('Transferência','Multicaixa','Dinheiro','TPA','Outro') NOT NULL DEFAULT 'Transferência',
  `referencia`       VARCHAR(80)     NULL COMMENT 'Número de referência bancária',
  `comprovativo_url` VARCHAR(255)    NULL COMMENT 'Caminho para o ficheiro',
  `data_pagamento`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado`           ENUM('pendente','confirmado','rejeitado') NOT NULL DEFAULT 'pendente',
  `confirmado_por`   INT UNSIGNED    NULL COMMENT 'id do administrador que confirmou',
  `notas_admin`      TEXT            NULL,
  `criado_em`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_mp_mensalidade`  FOREIGN KEY (`id_mensalidade`)  REFERENCES `mensalidade`(`id`),
  CONSTRAINT `fk_mp_admin`        FOREIGN KEY (`confirmado_por`)   REFERENCES `administrador`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Pagamentos efectuados para cada mensalidade';

-- ============================================================
--  DOMÍNIO 4 — COMUNICAÇÃO
-- ============================================================

-- 10. CONVERSA
CREATE TABLE IF NOT EXISTS `conversa` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `tipo`         ENUM('privada','grupo','anuncio') NOT NULL DEFAULT 'privada',
  `titulo`       VARCHAR(120)    NULL COMMENT 'Para grupos e anúncios',
  `criado_por`   INT UNSIGNED    NULL COMMENT 'id do morador ou admin criador',
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Conversas / canais de comunicação';

-- 11. CONVERSA_PARTICIPANTE
CREATE TABLE IF NOT EXISTS `conversa_participante` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_conversa`  INT UNSIGNED    NOT NULL,
  `tipo_user`    ENUM('morador','administrador') NOT NULL,
  `id_user`      INT UNSIGNED    NOT NULL COMMENT 'id em morador ou administrador',
  `entrou_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `saiu_em`      DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cp` (`id_conversa`, `tipo_user`, `id_user`),
  CONSTRAINT `fk_cp_conversa` FOREIGN KEY (`id_conversa`) REFERENCES `conversa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Participantes de cada conversa';

-- 12. MENSAGEM
CREATE TABLE IF NOT EXISTS `mensagem` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_conversa`  INT UNSIGNED    NOT NULL,
  `tipo_remetente` ENUM('morador','administrador') NOT NULL,
  `id_remetente` INT UNSIGNED    NOT NULL,
  `conteudo`     TEXT            NOT NULL,
  `anexo_url`    VARCHAR(255)    NULL,
  `lida`         TINYINT(1)      NOT NULL DEFAULT 0,
  `enviada_em`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_msg_conversa` FOREIGN KEY (`id_conversa`) REFERENCES `conversa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Mensagens de cada conversa';

-- ============================================================
--  DOMÍNIO 5 — OPERAÇÕES
-- ============================================================

-- 13. OCORRENCIA (avarias, reclamações, sugestões)
CREATE TABLE IF NOT EXISTS `ocorrencia` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_morador`   INT UNSIGNED    NOT NULL,
  `id_apartamento` INT UNSIGNED  NULL,
  `tipo`         ENUM('Avaria','Reclamacao','Sugestao','Outro') NOT NULL DEFAULT 'Outro',
  `titulo`       VARCHAR(120)    NOT NULL,
  `descricao`    TEXT            NOT NULL,
  `prioridade`   ENUM('Baixa','Media','Alta','Urgente') NOT NULL DEFAULT 'Media',
  `estado`       ENUM('aberta','em_analise','resolvida','encerrada') NOT NULL DEFAULT 'aberta',
  `resolvida_por` INT UNSIGNED   NULL COMMENT 'id do administrador',
  `data_resolucao` DATETIME      NULL,
  `notas_admin`  TEXT            NULL,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_em` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_oc_morador`     FOREIGN KEY (`id_morador`)     REFERENCES `morador`(`id`),
  CONSTRAINT `fk_oc_apartamento` FOREIGN KEY (`id_apartamento`) REFERENCES `apartamento`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_oc_admin`       FOREIGN KEY (`resolvida_por`)  REFERENCES `administrador`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Ocorrências / avarias / reclamações reportadas pelos moradores';

-- 14. NOTIFICACAO
CREATE TABLE IF NOT EXISTS `notificacao` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `tipo_destino` ENUM('morador','administrador','todos') NOT NULL DEFAULT 'morador',
  `id_destino`   INT UNSIGNED    NULL COMMENT 'NULL = broadcast (todos)',
  `titulo`       VARCHAR(120)    NOT NULL,
  `mensagem`     TEXT            NOT NULL,
  `tipo`         ENUM('info','aviso','alerta','pagamento','ocorrencia') NOT NULL DEFAULT 'info',
  `entidade_ref` VARCHAR(50)     NULL COMMENT 'Ex: mensalidade:12, ocorrencia:3',
  `lida`         TINYINT(1)      NOT NULL DEFAULT 0,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Notificações automáticas do sistema';

-- ============================================================
--  DADOS INICIAIS (seed)
-- ============================================================

-- Condomínio base
INSERT INTO `condominio` (`nome`, `morada`, `cidade`, `nif`, `telefone`, `email`, `mensalidade_base`, `multa_diaria`, `iban`, `banco`)
VALUES ('Condomínio Nosso Zimbo', 'Camama, Luanda', 'Luanda', '000.000.000', '+244 931 612 489', 'nossozimbo@admin.ao', 140000.00, 500.00, 'AO06000000000000000000000', 'BAI');

-- Blocos
INSERT INTO `bloco` (`id_condominio`, `letra`, `descricao`) VALUES
(1, 'A', 'Bloco A — Zona Norte'),
(1, 'B', 'Bloco B — Zona Central'),
(1, 'C', 'Bloco C — Zona Sul');

-- Apartamentos (amostra)
INSERT INTO `apartamento` (`id_bloco`, `numero`, `andar`, `tipologia`, `estado`, `codigo`) VALUES
(1, '101', 1, 'V3', 'Disponivel', 'A-101'),
(1, '102', 1, 'V3', 'Disponivel', 'A-102'),
(1, '201', 2, 'V3', 'Disponivel', 'A-201'),
(2, '101', 1, 'V3', 'Disponivel', 'B-101'),
(2, '102', 1, 'V3', 'Disponivel', 'B-102'),
(3, '101', 1, 'V3', 'Disponivel', 'C-101');

-- Super Admin padrão (senha: Admin@2026 — alterar após primeiro login)
-- password_hash('Admin@2026', PASSWORD_DEFAULT)
INSERT INTO `administrador` (`id_condominio`, `nome`, `email`, `numbi`, `telefone`, `funcao`, `senha_hash`, `activo`)
VALUES (1, 'Administrador Principal', 'admin@nossozimbo.ao', '000000000', '+244 931 612 489', 'Super Admin',
        '$2y$12$eT3hWJz.vv.sXW3p5oF4OuWdRV3CfbW5v8y6hJ2K9mL1nP7qR0sOe', 1);

-- 15. VISITA (acesso de convidados de moradores)
CREATE TABLE IF NOT EXISTS `visita` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_morador`   INT UNSIGNED    NOT NULL,
  `id_apartamento` INT UNSIGNED  NOT NULL,
  `nome_visitante` VARCHAR(120)  NOT NULL,
  `numbi_visitante` VARCHAR(20)  NULL,
  `data_prevista` DATE           NOT NULL,
  `hora_prevista` TIME           NULL,
  `estado`       ENUM('pendente','autorizado','entrada_registada','saida_registada','negado') NOT NULL DEFAULT 'pendente',
  `codigo_acesso` VARCHAR(10)    NULL UNIQUE,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_vis_morador`    FOREIGN KEY (`id_morador`)    REFERENCES `morador`(`id`),
  CONSTRAINT `fk_vis_apartamento` FOREIGN KEY (`id_apartamento`) REFERENCES `apartamento`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registo de visitas agendadas pelos moradores';

-- 16. AGENDAMENTO (uso de áreas comuns)
CREATE TABLE IF NOT EXISTS `agendamento` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_morador`   INT UNSIGNED    NOT NULL,
  `area_comum`   ENUM('Pisina','Salao de Festas','Churrasqueira','Campo Jogos') NOT NULL,
  `data_evento`  DATE            NOT NULL,
  `hora_inicio`  TIME            NOT NULL,
  `hora_fim`     TIME            NOT NULL,
  `estado`       ENUM('pendente','confirmado','cancelado') NOT NULL DEFAULT 'pendente',
  `notas`        TEXT            NULL,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_age_morador`    FOREIGN KEY (`id_morador`)    REFERENCES `morador`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Agendamento de áreas comuns';

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
