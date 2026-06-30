-- ============================================================================
-- DADOS DE TESTE — Condomínio Nosso Zimbo
-- ============================================================================
-- Script para popular a base de dados com dados de teste
-- Permite testar login de admin e moradores

-- ─────────────────────────────────────────────────────────────────────────
-- 1. INSERIR CONDOMÍNIO DE TESTE
-- ─────────────────────────────────────────────────────────────────────────

INSERT INTO condominio (nome, morada, cidade, pais, nif, telefone, email, mensalidade_base, multa_diaria)
VALUES ('Condomínio Nosso Zimbo', 'Talatona, Luanda', 'Luanda', 'Angola', '12345678901', '912345678', 'info@noszimbo.ao', 140000.00, 500.00);

-- ─────────────────────────────────────────────────────────────────────────
-- 2. INSERIR BLOCOS
-- ─────────────────────────────────────────────────────────────────────────

INSERT INTO bloco (id_condominio, letra, descricao)
VALUES 
  (1, 'A', 'Bloco A - Zona Residencial'),
  (1, 'B', 'Bloco B - Zona Residencial'),
  (1, 'C', 'Bloco C - Zona Comercial');

-- ─────────────────────────────────────────────────────────────────────────
-- 3. INSERIR APARTAMENTOS
-- ─────────────────────────────────────────────────────────────────────────

INSERT INTO apartamento (id_bloco, numero, andar, tipologia, estado, codigo, obs)
VALUES
  (1, '101', 1, 'V3', 'Disponivel', 'A-101', 'Apartamento frente'),
  (1, '102', 1, 'V3', 'Ocupado', 'A-102', 'Apartamento lateral'),
  (1, '201', 2, 'V4', 'Disponivel', 'A-201', NULL),
  (1, '202', 2, 'V3', 'Ocupado', 'A-202', NULL),
  (2, '101', 1, 'V3', 'Disponivel', 'B-101', NULL),
  (2, '102', 1, 'T1', 'Ocupado', 'B-102', 'Estúdio compacto'),
  (2, '201', 2, 'V4', 'Disponivel', 'B-201', NULL),
  (3, '101', 1, 'Comercial', 'Ocupado', 'C-101', 'Espaço comercial');

-- ─────────────────────────────────────────────────────────────────────────
-- 4. INSERIR FUNCIONÁRIOS/ADMINISTRADORES DE TESTE
-- ─────────────────────────────────────────────────────────────────────────
-- Senha padrão: admin123 (para todos)
-- Hash gerado com: password_hash('admin123', PASSWORD_DEFAULT)

INSERT INTO administrador (id_condominio, nome, email, numbi, telefone, funcao, iban, senha_hash, activo, nasc, nacionalidade, morada, emissao_bi, validade_bi, locale_bi)
VALUES
  -- Super Admin
  (1, 'João Silva', 'joao@noszimbo.ao', '000123456LA001', '923456789', 'Super Admin', 'AO06123456789012345678', '$2y$10$NjU4K3VkNLrKZPZk0H0sUO/Nf/x5RZt.QHrHQH.xDJ8xFJ0Yye3K6', 1, '1985-05-15', 'Angolana', 'Talatona, Luanda', '2015-03-22', '2030-03-22', 'SAE Patriota'),
  
  -- Administrador Geral
  (1, 'Maria Santos', 'maria@noszimbo.ao', '000654321LA002', '924567890', 'Administrador', 'AO06234567890123456789', '$2y$10$NjU4K3VkNLrKZPZk0H0sUO/Nf/x5RZt.QHrHQH.xDJ8xFJ0Yye3K6', 1, '1990-07-20', 'Angolana', 'Alvalade, Luanda', '2018-01-10', '2033-01-10', 'SAE Patriota'),
  
  -- RH
  (1, 'Pedro Lopes', 'pedro@noszimbo.ao', '000789012LA003', '925678901', 'Recursos Humanos', 'AO06345678901234567890', '$2y$10$NjU4K3VkNLrKZPZk0H0sUO/Nf/x5RZt.QHrHQH.xDJ8xFJ0Yye3K6', 1, '1992-11-08', 'Angolana', 'Maianga, Luanda', '2017-05-14', '2032-05-14', 'SAE Patriota'),
  
  -- Segurança
  (1, 'Carlos Mendes', 'carlos@noszimbo.ao', '000345678LA004', '926789012', 'Seguranca', NULL, '$2y$10$NjU4K3VkNLrKZPZk0H0sUO/Nf/x5RZt.QHrHQH.xDJ8xFJ0Yye3K6', 1, '1988-03-25', 'Angolana', 'Cacimba, Luanda', '2016-08-30', '2031-08-30', 'SAE Patriota'),
  
  -- Área Técnica
  (1, 'Ana Costa', 'ana@noszimbo.ao', '000912345LA005', '927890123', 'Area Tecnica', NULL, '$2y$10$NjU4K3VkNLrKZPZk0H0sUO/Nf/x5RZt.QHrHQH.xDJ8xFJ0Yye3K6', 1, '1995-09-12', 'Angolana', 'Samba, Luanda', '2019-02-05', '2034-02-05', 'SAE Patriota');

-- ─────────────────────────────────────────────────────────────────────────
-- 5. INSERIR MORADORES DE TESTE
-- ─────────────────────────────────────────────────────────────────────────
-- Senha padrão: morador123 (para todos)
-- Hash gerado com: password_hash('morador123', PASSWORD_DEFAULT)

INSERT INTO morador (nome, email, numbi, telefone, senha_hash, nasc, nacionalidade, morada_anterior, emissao_bi, validade_bi, locale_bi, estado_conta)
VALUES
  -- Morador 1
  ('Francisco Neves', 'francisco@email.ao', '000111222LA010', '931111222', '$2y$10$FN1B2c3d4E5f6G7h8I9j0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6', '1980-04-12', 'Angolana', 'Viana, Luanda', '2014-06-20', '2029-06-20', 'SAE Patriota', 'Activo'),
  
  -- Morador 2
  ('Lurdes Gomes', 'lurdes@email.ao', '000222333LA011', '932222333', '$2y$10$FN1B2c3d4E5f6G7h8I9j0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6', '1987-08-30', 'Angolana', 'Ingombota, Luanda', '2016-11-15', '2031-11-15', 'SAE Patriota', 'Activo'),
  
  -- Morador 3
  ('Óscar Rodrigues', 'oscar@email.ao', '000333444LA012', '933333444', '$2y$10$FN1B2c3d4E5f6G7h8I9j0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6', '1993-12-05', 'Angolana', 'Kilamba, Luanda', '2018-09-10', '2033-09-10', 'SAE Patriota', 'Activo'),
  
  -- Morador 4
  ('Justina Ferreira', 'justina@email.ao', '000444555LA013', '934444555', '$2y$10$FN1B2c3d4E5f6G7h8I9j0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6', '1991-01-18', 'Angolana', 'Manuelas, Luanda', '2017-04-22', '2032-04-22', 'SAE Patriota', 'Activo');

-- ─────────────────────────────────────────────────────────────────────────
-- 6. ASSOCIAR MORADORES A APARTAMENTOS
-- ─────────────────────────────────────────────────────────────────────────

INSERT INTO morador_apartamento (id_morador, id_apartamento, data_entrada, data_saida, activo)
VALUES
  (1, 2, '2022-01-15', NULL, 1),    -- Francisco → A-102
  (2, 4, '2023-03-20', NULL, 1),    -- Lurdes → A-202
  (3, 6, '2023-06-10', NULL, 1),    -- Óscar → B-102
  (4, 1, '2024-01-05', NULL, 1);    -- Justina → A-101 (agora ocupado)

-- ─────────────────────────────────────────────────────────────────────────
-- 7. CRIAR MENSALIDADES DE TESTE
-- ─────────────────────────────────────────────────────────────────────────

-- Mensalidades de Junho/2026
INSERT INTO mensalidade (id_morador, id_apartamento, servico, mes, ano, valor, vencimento, estado)
VALUES
  (1, 2, 'Quota Condominal', 6, 2026, 14000.00, '2026-06-30', 'pago'),
  (2, 4, 'Quota Condominal', 6, 2026, 14000.00, '2026-06-30', 'pendente'),
  (3, 6, 'Quota Condominal', 6, 2026, 14000.00, '2026-06-30', 'pendente'),
  (4, 1, 'Quota Condominal', 6, 2026, 14000.00, '2026-06-30', 'atrasado');

-- Mensalidades de Maio/2026
INSERT INTO mensalidade (id_morador, id_apartamento, servico, mes, ano, valor, vencimento, estado)
VALUES
  (1, 2, 'Quota Condominal', 5, 2026, 14000.00, '2026-05-30', 'pago'),
  (2, 4, 'Quota Condominal', 5, 2026, 14000.00, '2026-05-30', 'pago'),
  (3, 6, 'Quota Condominal', 5, 2026, 14000.00, '2026-05-30', 'pago'),
  (4, 1, 'Quota Condominal', 5, 2026, 14000.00, '2026-05-30', 'pago');

-- ─────────────────────────────────────────────────────────────────────────
-- 8. CRIAR PAGAMENTOS DE TESTE
-- ─────────────────────────────────────────────────────────────────────────

INSERT INTO mensalidade_pagamento (id_mensalidade, valor_pago, metodo, referencia, data_pagamento, estado, confirmado_por)
VALUES
  (1, 14000.00, 'Transferência', 'REF001', '2026-05-28', 'confirmado', 1),    -- Pago em Maio
  (5, 14000.00, 'Transferência', 'REF002', '2026-05-25', 'confirmado', 1),    -- Pago em Maio
  (6, 14000.00, 'Transferência', 'REF003', '2026-05-26', 'confirmado', 1),    -- Pago em Maio
  (7, 14000.00, 'Transferência', 'REF004', '2026-05-27', 'confirmado', 2),    -- Pago em Maio
  (2, 14000.00, 'Transferência', 'REF005', '2026-06-15', 'confirmado', 1);    -- Pago em Junho

-- ─────────────────────────────────────────────────────────────────────────
-- DADOS DE TESTE INSERIDOS COM SUCESSO
-- ─────────────────────────────────────────────────────────────────────────
--
-- CREDENCIAIS DE TESTE:
--
-- ADMIN:
--   BI: 000123456LA001
--   Senha: admin123
--   Nome: João Silva (Super Admin)
--
-- ADMIN 2:
--   BI: 000654321LA002
--   Senha: admin123
--   Nome: Maria Santos (Administrador)
--
-- MORADOR:
--   BI: 000111222LA010
--   Senha: morador123
--   Nome: Francisco Neves
--   Apartamento: A-102
--
-- MORADOR 2:
--   BI: 000222333LA011
--   Senha: morador123
--   Nome: Lurdes Gomes
--   Apartamento: A-202
-- ─────────────────────────────────────────────────────────────────────────
