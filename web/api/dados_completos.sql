-- ============================================================================
-- SCRIPT COMPLETO DE DADOS — Condomínio Nosso Zimbo
-- ============================================================================
-- Hashes correctos (gerados com PHP 8.x password_hash):
--   admin123   → $2y$12$B16GFOp0OH8MMZ75dbSyyO7Ykiy9R1Jjal4tvce0UUFMKZ.irOfKK
--   morador123 → $2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa
-- ============================================================================

-- Limpar dados antigos mantendo a estrutura
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE mensalidade_pagamento;
TRUNCATE TABLE mensalidade;
TRUNCATE TABLE morador_apartamento;
TRUNCATE TABLE morador;
TRUNCATE TABLE administrador;
TRUNCATE TABLE apartamento;
TRUNCATE TABLE bloco;
TRUNCATE TABLE condominio;
TRUNCATE TABLE comunicado;
TRUNCATE TABLE ocorrencia;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. CONDOMÍNIO
-- ============================================================================
INSERT INTO condominio (nome, morada, cidade, pais, nif, telefone, email, mensalidade_base, multa_diaria, iban, banco)
VALUES ('Condomínio Nosso Zimbo', 'Talatona, Luanda', 'Luanda', 'Angola', '000.000.000', '+244 931 612 489', 'info@nossozimbo.ao', 140000.00, 500.00, 'AO06000000000000000000000', 'BAI');

-- ============================================================================
-- 2. BLOCOS
-- ============================================================================
INSERT INTO bloco (id_condominio, letra, descricao) VALUES
  (1, 'A', 'Bloco A — Torre Norte'),
  (1, 'B', 'Bloco B — Torre Central'),
  (1, 'C', 'Bloco C — Torre Sul'),
  (1, 'D', 'Bloco D — Zona Comercial');

-- ============================================================================
-- 3. APARTAMENTOS (Bloco A — 10 apartamentos)
-- ============================================================================
INSERT INTO apartamento (id_bloco, numero, andar, tipologia, area_m2, estado, codigo) VALUES
  -- Bloco A
  (1, '101', 1, 'T2', 75.00,  'Ocupado',    'A-101'),
  (1, '102', 1, 'T3', 95.00,  'Ocupado',    'A-102'),
  (1, '103', 1, 'T1', 55.00,  'Disponivel', 'A-103'),
  (1, '201', 2, 'T4', 120.00, 'Ocupado',    'A-201'),
  (1, '202', 2, 'T3', 95.00,  'Ocupado',    'A-202'),
  (1, '203', 2, 'T2', 75.00,  'Disponivel', 'A-203'),
  (1, '301', 3, 'T4', 130.00, 'Ocupado',    'A-301'),
  (1, '302', 3, 'T3', 98.00,  'Disponivel', 'A-302'),
  -- Bloco B
  (2, '101', 1, 'T2', 78.00,  'Ocupado',    'B-101'),
  (2, '102', 1, 'T3', 100.00, 'Ocupado',    'B-102'),
  (2, '103', 1, 'T1', 52.00,  'Disponivel', 'B-103'),
  (2, '201', 2, 'T4', 125.00, 'Ocupado',    'B-201'),
  (2, '202', 2, 'T3', 95.00,  'Disponivel', 'B-202'),
  (2, '301', 3, 'T2', 78.00,  'Ocupado',    'B-301'),
  -- Bloco C
  (3, '101', 1, 'T3', 90.00,  'Ocupado',    'C-101'),
  (3, '102', 1, 'T2', 70.00,  'Disponivel', 'C-102'),
  (3, '201', 2, 'T4', 115.00, 'Ocupado',    'C-201'),
  (3, '202', 2, 'T3', 90.00,  'Ocupado',    'C-202'),
  -- Bloco D (Comercial)
  (4, 'L01', 0, 'Comercial', 45.00, 'Ocupado',    'D-L01'),
  (4, 'L02', 0, 'Comercial', 60.00, 'Disponivel', 'D-L02');

-- ============================================================================
-- 4. ADMINISTRADORES / FUNCIONÁRIOS
--    Senha para todos: admin123
-- ============================================================================
INSERT INTO administrador (id_condominio, nome, email, numbi, telefone, funcao, iban, senha_hash, activo, nasc, nacionalidade, morada, emissao_bi, validade_bi, locale_bi) VALUES
  (1, 'João Silva',    'joao@nossozimbo.ao',    '000123456LA001', '+244 923 456 789', 'Super Admin',       'AO06001000000000000000001', '$2y$12$B16GFOp0OH8MMZ75dbSyyO7Ykiy9R1Jjal4tvce0UUFMKZ.irOfKK', 1, '1985-05-15', 'Angolana', 'Talatona, Luanda',    '2015-03-22', '2030-03-22', 'SAE Patriota'),
  (1, 'Maria Santos',  'maria@nossozimbo.ao',   '000654321LA002', '+244 924 567 890', 'Administrador',     'AO06001000000000000000002', '$2y$12$B16GFOp0OH8MMZ75dbSyyO7Ykiy9R1Jjal4tvce0UUFMKZ.irOfKK', 1, '1990-07-20', 'Angolana', 'Alvalade, Luanda',    '2018-01-10', '2033-01-10', 'SAE Patriota'),
  (1, 'Pedro Lopes',   'pedro@nossozimbo.ao',   '000789012LA003', '+244 925 678 901', 'Recursos Humanos',  NULL,                        '$2y$12$B16GFOp0OH8MMZ75dbSyyO7Ykiy9R1Jjal4tvce0UUFMKZ.irOfKK', 1, '1992-11-08', 'Angolana', 'Maianga, Luanda',     '2017-05-14', '2032-05-14', 'SAE Patriota'),
  (1, 'Carlos Mendes', 'carlos@nossozimbo.ao',  '000345678LA004', '+244 926 789 012', 'Seguranca',         NULL,                        '$2y$12$B16GFOp0OH8MMZ75dbSyyO7Ykiy9R1Jjal4tvce0UUFMKZ.irOfKK', 1, '1988-03-25', 'Angolana', 'Cacimba, Luanda',     '2016-08-30', '2031-08-30', 'SAE Patriota'),
  (1, 'Ana Costa',     'ana@nossozimbo.ao',     '000912345LA005', '+244 927 890 123', 'Area Tecnica',      NULL,                        '$2y$12$B16GFOp0OH8MMZ75dbSyyO7Ykiy9R1Jjal4tvce0UUFMKZ.irOfKK', 1, '1995-09-12', 'Angolana', 'Samba, Luanda',       '2019-02-05', '2034-02-05', 'SAE Patriota'),
  (1, 'David Figueira','david@nossozimbo.ao',   '000111000LA006', '+244 928 123 456', 'Administrador',     NULL,                        '$2y$12$B16GFOp0OH8MMZ75dbSyyO7Ykiy9R1Jjal4tvce0UUFMKZ.irOfKK', 1, '1983-01-30', 'Angolana', 'Rangel, Luanda',      '2013-07-11', '2028-07-11', 'SAE Patriota');

-- ============================================================================
-- 5. MORADORES (12 moradores)
--    Senha para todos: morador123
-- ============================================================================
INSERT INTO morador (nome, email, numbi, telefone, senha_hash, nasc, nacionalidade, morada_anterior, emissao_bi, validade_bi, locale_bi, estado_conta) VALUES
  ('Francisco Neves',    'francisco@email.ao',  '000111222LA010', '+244 931 111 222', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1980-04-12', 'Angolana', 'Viana, Luanda',         '2014-06-20', '2029-06-20', 'SAE Patriota', 'Activo'),
  ('Lurdes Gomes',       'lurdes@email.ao',     '000222333LA011', '+244 932 222 333', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1987-08-30', 'Angolana', 'Ingombota, Luanda',     '2016-11-15', '2031-11-15', 'SAE Patriota', 'Activo'),
  ('Oscar Rodrigues',    'oscar@email.ao',      '000333444LA012', '+244 933 333 444', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1993-12-05', 'Angolana', 'Kilamba, Luanda',       '2018-09-10', '2033-09-10', 'SAE Patriota', 'Activo'),
  ('Justina Ferreira',   'justina@email.ao',    '000444555LA013', '+244 934 444 555', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1991-01-18', 'Angolana', 'Manuelas, Luanda',      '2017-04-22', '2032-04-22', 'SAE Patriota', 'Activo'),
  ('Manuel Teixeira',    'manuel@email.ao',     '000555666LA014', '+244 935 555 666', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1975-06-22', 'Angolana', 'Cacuaco, Luanda',       '2012-03-18', '2027-03-18', 'SAE Patriota', 'Activo'),
  ('Helena Pinto',       'helena@email.ao',     '000666777LA015', '+244 936 666 777', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1983-02-14', 'Angolana', 'Palanca, Luanda',       '2015-08-25', '2030-08-25', 'SAE Patriota', 'Activo'),
  ('Paulo Cardoso',      'paulo@email.ao',      '000777888LA016', '+244 937 777 888', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1978-09-10', 'Angolana', 'Sambizanga, Luanda',    '2011-12-05', '2026-12-05', 'SAE Patriota', 'Activo'),
  ('Rosa Andrade',       'rosa@email.ao',       '000888999LA017', '+244 938 888 999', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1990-11-28', 'Angolana', 'Benfica, Luanda',       '2019-05-30', '2034-05-30', 'SAE Patriota', 'Activo'),
  ('Tiago Baptista',     'tiago@email.ao',      '000999111LA018', '+244 939 999 111', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1988-07-04', 'Angolana', 'Petrangol, Luanda',     '2016-04-14', '2031-04-14', 'SAE Patriota', 'Activo'),
  ('Beatriz Sousa',      'beatriz@email.ao',    '001000222LA019', '+244 940 000 222', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1996-03-19', 'Angolana', 'Rocha Pinto, Luanda',   '2020-01-22', '2035-01-22', 'SAE Patriota', 'Activo'),
  ('Renato Cunha',       'renato@email.ao',     '001111333LA020', '+244 941 111 333', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1982-10-07', 'Angolana', 'Ngola Kiluanje, Luanda', '2014-09-09', '2029-09-09', 'SAE Patriota', 'Activo'),
  ('Carla Monteiro',     'carla@email.ao',      '001222444LA021', '+244 942 222 444', '$2y$12$JrkgAlgHaT9MhUWh3HSQCuPNE8diBxddXjat97V1f2ikgurQcfnMa', '1994-05-25', 'Angolana', 'Samba, Luanda',         '2021-06-15', '2036-06-15', 'SAE Patriota', 'Activo');

-- ============================================================================
-- 6. ASSOCIAR MORADORES A APARTAMENTOS
-- ============================================================================
-- Morador 1 (Francisco)  → A-101 (id=1)
-- Morador 2 (Lurdes)     → A-102 (id=2)
-- Morador 3 (Oscar)      → A-201 (id=4)
-- Morador 4 (Justina)    → A-202 (id=5)
-- Morador 5 (Manuel)     → A-301 (id=7)
-- Morador 6 (Helena)     → B-101 (id=9)
-- Morador 7 (Paulo)      → B-102 (id=10)
-- Morador 8 (Rosa)       → B-201 (id=12)
-- Morador 9 (Tiago)      → B-301 (id=14)
-- Morador 10 (Beatriz)   → C-101 (id=15)
-- Morador 11 (Renato)    → C-201 (id=17)
-- Morador 12 (Carla)     → C-202 (id=18)
INSERT INTO morador_apartamento (id_morador, id_apartamento, data_entrada, data_saida, activo) VALUES
  (1,  1,  '2022-01-15', NULL, 1),
  (2,  2,  '2021-06-10', NULL, 1),
  (3,  4,  '2022-09-01', NULL, 1),
  (4,  5,  '2023-03-20', NULL, 1),
  (5,  7,  '2020-11-05', NULL, 1),
  (6,  9,  '2023-08-14', NULL, 1),
  (7,  10, '2021-02-28', NULL, 1),
  (8,  12, '2024-01-10', NULL, 1),
  (9,  14, '2022-07-22', NULL, 1),
  (10, 15, '2023-11-01', NULL, 1),
  (11, 17, '2021-05-15', NULL, 1),
  (12, 18, '2024-03-05', NULL, 1);

-- Actualizar estado dos apartamentos ocupados
UPDATE apartamento SET estado = 'Ocupado' WHERE id IN (1,2,4,5,7,9,10,12,14,15,17,18);

-- ============================================================================
-- 7. MENSALIDADES — Últimos 3 meses para todos os moradores
-- ============================================================================

-- Abril 2026
INSERT INTO mensalidade (id_morador, id_apartamento, servico, mes, ano, valor, vencimento, estado) VALUES
  (1,1,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (2,2,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (3,4,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (4,5,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (5,7,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (6,9,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (7,10,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (8,12,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (9,14,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (10,15,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (11,17,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago'),
  (12,18,'Quota Condominal',4,2026,140000.00,'2026-04-30','pago');

-- Maio 2026
INSERT INTO mensalidade (id_morador, id_apartamento, servico, mes, ano, valor, vencimento, estado) VALUES
  (1,1,'Quota Condominal',5,2026,140000.00,'2026-05-31','pago'),
  (2,2,'Quota Condominal',5,2026,140000.00,'2026-05-31','pago'),
  (3,4,'Quota Condominal',5,2026,140000.00,'2026-05-31','pago'),
  (4,5,'Quota Condominal',5,2026,140000.00,'2026-05-31','pago'),
  (5,7,'Quota Condominal',5,2026,140000.00,'2026-05-31','atrasado'),
  (6,9,'Quota Condominal',5,2026,140000.00,'2026-05-31','pago'),
  (7,10,'Quota Condominal',5,2026,140000.00,'2026-05-31','pago'),
  (8,12,'Quota Condominal',5,2026,140000.00,'2026-05-31','pago'),
  (9,14,'Quota Condominal',5,2026,140000.00,'2026-05-31','atrasado'),
  (10,15,'Quota Condominal',5,2026,140000.00,'2026-05-31','pago'),
  (11,17,'Quota Condominal',5,2026,140000.00,'2026-05-31','pago'),
  (12,18,'Quota Condominal',5,2026,140000.00,'2026-05-31','pendente');

-- Junho 2026
INSERT INTO mensalidade (id_morador, id_apartamento, servico, mes, ano, valor, vencimento, estado) VALUES
  (1,1,'Quota Condominal',6,2026,140000.00,'2026-06-30','pago'),
  (2,2,'Quota Condominal',6,2026,140000.00,'2026-06-30','pendente'),
  (3,4,'Quota Condominal',6,2026,140000.00,'2026-06-30','pendente'),
  (4,5,'Quota Condominal',6,2026,140000.00,'2026-06-30','pendente'),
  (5,7,'Quota Condominal',6,2026,140000.00,'2026-06-30','atrasado'),
  (6,9,'Quota Condominal',6,2026,140000.00,'2026-06-30','pago'),
  (7,10,'Quota Condominal',6,2026,140000.00,'2026-06-30','pago'),
  (8,12,'Quota Condominal',6,2026,140000.00,'2026-06-30','pendente'),
  (9,14,'Quota Condominal',6,2026,140000.00,'2026-06-30','atrasado'),
  (10,15,'Quota Condominal',6,2026,140000.00,'2026-06-30','pago'),
  (11,17,'Quota Condominal',6,2026,140000.00,'2026-06-30','pendente'),
  (12,18,'Quota Condominal',6,2026,140000.00,'2026-06-30','pendente');

-- ============================================================================
-- 8. PAGAMENTOS CONFIRMADOS (Abril e Maio pagos)
-- ============================================================================
INSERT INTO mensalidade_pagamento (id_mensalidade, valor_pago, metodo, referencia, data_pagamento, estado, confirmado_por) VALUES
  -- Abril (IDs 1-12)
  (1,  140000.00, 'Transferência', 'REF-ABR-001', '2026-04-02', 'confirmado', 1),
  (2,  140000.00, 'Multicaixa',    'REF-ABR-002', '2026-04-03', 'confirmado', 1),
  (3,  140000.00, 'Transferência', 'REF-ABR-003', '2026-04-05', 'confirmado', 1),
  (4,  140000.00, 'Transferência', 'REF-ABR-004', '2026-04-06', 'confirmado', 2),
  (5,  140000.00, 'Dinheiro',      'REF-ABR-005', '2026-04-08', 'confirmado', 2),
  (6,  140000.00, 'Multicaixa',    'REF-ABR-006', '2026-04-10', 'confirmado', 1),
  (7,  140000.00, 'Transferência', 'REF-ABR-007', '2026-04-12', 'confirmado', 1),
  (8,  140000.00, 'Transferência', 'REF-ABR-008', '2026-04-14', 'confirmado', 2),
  (9,  140000.00, 'Multicaixa',    'REF-ABR-009', '2026-04-15', 'confirmado', 1),
  (10, 140000.00, 'Transferência', 'REF-ABR-010', '2026-04-18', 'confirmado', 1),
  (11, 140000.00, 'Dinheiro',      'REF-ABR-011', '2026-04-20', 'confirmado', 2),
  (12, 140000.00, 'Transferência', 'REF-ABR-012', '2026-04-22', 'confirmado', 1),
  -- Maio (IDs 13-24, apenas os pagos: 13,14,15,16,18,19,20,22,23)
  (13, 140000.00, 'Transferência', 'REF-MAI-001', '2026-05-04', 'confirmado', 1),
  (14, 140000.00, 'Multicaixa',    'REF-MAI-002', '2026-05-06', 'confirmado', 1),
  (15, 140000.00, 'Transferência', 'REF-MAI-003', '2026-05-08', 'confirmado', 2),
  (16, 140000.00, 'Transferência', 'REF-MAI-004', '2026-05-09', 'confirmado', 1),
  (18, 140000.00, 'Multicaixa',    'REF-MAI-006', '2026-05-12', 'confirmado', 1),
  (19, 140000.00, 'Transferência', 'REF-MAI-007', '2026-05-14', 'confirmado', 2),
  (20, 140000.00, 'Dinheiro',      'REF-MAI-008', '2026-05-15', 'confirmado', 1),
  (22, 140000.00, 'Transferência', 'REF-MAI-010', '2026-05-18', 'confirmado', 1),
  (23, 140000.00, 'Multicaixa',    'REF-MAI-011', '2026-05-20', 'confirmado', 2),
  -- Junho (apenas pagos: 25,30,31,34)
  (25, 140000.00, 'Transferência', 'REF-JUN-001', '2026-06-02', 'confirmado', 1),
  (30, 140000.00, 'Multicaixa',    'REF-JUN-006', '2026-06-05', 'confirmado', 1),
  (31, 140000.00, 'Transferência', 'REF-JUN-007', '2026-06-08', 'confirmado', 2),
  (34, 140000.00, 'Transferência', 'REF-JUN-010', '2026-06-10', 'confirmado', 1);

-- ============================================================================
-- 9. COMUNICADOS
-- ============================================================================
INSERT INTO comunicado (titulo, conteudo, tipo, criado_por) VALUES
  ('Bem-vindos ao Portal Nosso Zimbo', 'Informamos que o novo portal de gestão condominial está disponível. Podem consultar as vossas mensalidades, registar ocorrências e comunicar com a administração directamente por aqui.', 'informativo', 1),
  ('Manutenção da Piscina — Julho 2026', 'A piscina estará encerrada para manutenção anual entre os dias 5 e 12 de Julho de 2026. Pedimos desculpa pelo incómodo causado.', 'manutencao', 2),
  ('URGENTE: Corte de água programado', 'Informamos que haverá corte de água no dia 28 de Junho entre as 08h00 e as 14h00 para reparação da rede de distribuição. Por favor, prevejam reservas de água.', 'urgente', 1),
  ('Pagamento de Quotas — Junho 2026', 'Lembramos que o prazo para pagamento das quotas de Junho é dia 30 do corrente mês. Quotas em atraso estão sujeitas a multa diária de 500 Kz.', 'informativo', 2),
  ('Assembleia de Condóminos — Julho 2026', 'Convocamos todos os condóminos para a Assembleia Geral Ordinária a realizar-se no dia 15 de Julho de 2026 às 10h00 no salão comunitário do Bloco A.', 'informativo', 1);

-- ============================================================================
-- 10. OCORRÊNCIAS
-- ============================================================================
INSERT INTO ocorrencia (id_morador, id_apartamento, tipo, titulo, descricao, prioridade, estado) VALUES
  (1, 1,  'Avaria',    'Fuga de água na cozinha',          'Torneira da cozinha com fuga de água. Necessita reparação urgente.',                        'Alta',  'aberta'),
  (2, 2,  'Reclamacao','Barulho excessivo nocturno',       'Vizinhos do andar de cima a fazer barulho excessivo nas madrugadas.',                        'Media', 'em_analise'),
  (3, 4,  'Reclamacao','Corredor por limpar',              'Corredor do 2.º andar por limpar há mais de uma semana.',                                   'Baixa', 'aberta'),
  (6, 9,  'Avaria',    'Elevador com anomalia',            'Elevador do Bloco B com anomalia — para às vezes entre andares.',                           'Alta',  'em_analise'),
  (10,15, 'Avaria',    'Portão do parque avariado',        'Portão de acesso ao parque de estacionamento sem funcionar correctamente.',                  'Alta',  'aberta'),
  (7, 10, 'Avaria',    'Ar condicionado avariado',         'Ar condicionado do quarto avariado. Solicitação de técnico.',                               'Media', 'resolvida');
