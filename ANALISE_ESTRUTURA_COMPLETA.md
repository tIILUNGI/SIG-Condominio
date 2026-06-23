# 📋 ANÁLISE COMPLETA — SIG-Condominio

**Projeto**: Sistema Integrado de Gestão — Condomínio Nosso Zimbo  
**Data**: 2026-06-23  
**Banco de Dados**: `condominio_nz` (MySQL)  
**Charset**: UTF-8MB4  

---

## 1️⃣ TIPOS DE UTILIZADORES & PERMISSÕES

| Tipo | Descrição | Tabela BD | Login | Dashboard | Permissões |
|------|-----------|----------|-------|-----------|-----------|
| **MORADOR** | Residente do condomínio | `morador` | `loginmorador.php` | `dashboard_morador.php` | Ver próprio perfil, mensalidades, ocorrências, agendamento de visitas, áreas comuns |
| **FUNCIONÁRIO/ADMIN** | Gestor do condomínio | `administrador` | `loginfuncionario.php` | `dashboard.php` | Gerir moradores, apartamentos, mensalidades, pagamentos, ocorrências, visitas, relatórios |
| **VISITANTE** | Prospect/novo morador | `morador` (criado) | `visitante.html` | Apenas registo | Registar-se no portal, aceder após confirmação |

### Funções de Administrador

```
funcao ENUM:
  - 'Super Admin'      → Acesso total
  - 'Administrador'    → Gestão geral
  - 'Recursos Humanos' → Gestão de staff
  - 'Segurança'        → Controlo de visitas
  - 'Area Tecnica'     → Manutenção
```

---

## 2️⃣ PÁGINAS PRINCIPAIS POR UTILIZADOR

### 🏠 Painel do MORADOR
| Página | Ficheiro | Funcionalidade | CRUD |
|--------|----------|----------------|------|
| **Dashboard** | `pages/dashboard_morador.php` | Widget KPIs (ocorrências, mensalidades) | **R** |
| **Meu Perfil** | `pages/meu_perfil.php` | Ver dados pessoais, apartamento | **R, U** |
| **Mensalidades** | `pages/minhas_mensalidades.php` | Listar e pagar quotas | **R, U** (estado) |
| **Ocorrências** | `pages/minhas_ocorrencias.php` | Criar, listar, acompanhar avarias/reclamações | **C, R, U** (comentários) |
| **Comunicação** | `pages/comunicacao.php` | Mensagens, avisos, anúncios | **R** |
| **Visitas** | `pages/visitas.php` | Agendar visitantes | **C, R, U** (estado) |
| **Áreas Comuns** | `pages/areas_comuns.php` | Reservar espaços (Piscina, Salão, Churrasqueira) | **C, R** |

### 🔧 Painel do ADMIN

| Página | Ficheiro | Funcionalidade | CRUD |
|--------|----------|----------------|------|
| **Dashboard Admin** | `pages/dashboard.php` | KPIs, gráficos, resumo | **R** |
| **Funcionários** | Tab em `dashboard.php` | Registar, editar, listar staff | **C, R, U, D** |
| **Moradores** | Tab em `dashboard.php` | Gerir contas, suspender, ativar | **C, R, U, D** |
| **Apartamentos** | Tab em `dashboard.php` | CRUD completo de unidades | **C, R, U, D** |
| **Mensalidades** | Tab em `dashboard.php` | Gerar, editar quotas | **C, R, U** |
| **Pagamentos** | Tab em `dashboard.php` | Confirmar, rejeitar comprovantes | **U** (estado) |
| **Visitas** | Tab em `dashboard.php` | Autorizar/negar acesso | **U** (estado) |
| **Áreas Comuns** | Tab em `dashboard.php` | Confirmar reservas | **U** (estado) |
| **Relatórios** | Tab em `dashboard.php` | Exportar dados, estatísticas | **R** |

### 👤 Página do VISITANTE

| Página | Ficheiro | Funcionalidade | CRUD |
|--------|----------|----------------|------|
| **Registo** | `Visitante/visitante.html` | Formulário de inscrição | **C** |

---

## 3️⃣ APIs DISPONÍVEIS (Endpoints JSON)

### 🔐 APIs de Autenticação

```
POST /api/loginmorador.php
  Input:  numbi, senha
  Output: Sessão com tipo='morador', id, nome, email, numbi
  
POST /api/loginfuncionario.php
  Input:  numbi, senha
  Output: Sessão com tipo='admin', id, nome, funcao
  
GET /api/logout.php
  Action: Termina sessão
```

### 📊 API do Morador (JSON)

```
GET /api/api_morador.php?acao=ACAO

Ações disponíveis:
  ✓ perfil              → Dados pessoais + apartamento atual
  ✓ mensalidades        → Lista todas as quotas do morador
  ✓ historico_pagamentos → Pagamentos efectuados
  ✓ resumo_financeiro   → Totais (pendente, pago, meses)
  ✓ vizinhos            → Moradores do mesmo bloco
  ✓ visitas             → Visitas agendadas
  ✓ novo_agendamento_visita → Registar visitante
  ✓ agendamentos_area   → Reservas de áreas comuns
  ✓ novo_agendamento_area → Agendar nova área
```

### 📊 API do Dashboard Admin (JSON)

```
GET /api/api_dashboard.php?acao=ACAO

Ações disponíveis:
  ✓ resumo              → KPIs gerais (total moradores, admins, etc)
  ✓ casas               → Todos os apartamentos
  ✓ moradores           → Todos os moradores
  ✓ admins              → Todos os funcionários
  ✓ mensalidades        → Todas as quotas
  ✓ pagamentos          → Histórico de pagamentos
  ✓ confirmar_pagamento → Aprovar/rejeitar pagamento
```

### 📤 APIs de Registo

```
POST /api/registar_morador.php
  Input:  nome, telefone, email, numbi, senha, nasc, nacionalidade,
          morada, emissao, validade, locale
  Action: INSERT INTO morador
  
POST /api/registar_admin.php
  Input:  nome, email, numbi, telefone, funcao, senha, nasc, 
          nacionalidade, morada, emissao, validade, locale, iban
  Action: INSERT INTO administrador
  Proteção: Apenas admins podem registar (comentado)
```

### 💰 APIs de Pagamentos

```
POST /api/pagar.php
  Input:  id_mensalidade, valor_pago, metodo, referencia
  Action: UPDATE mensalidade + INSERT INTO mensalidade_pagamento
  State:  Registar pagamento em pendente (requer confirmação admin)
```

### 🏠 APIs de Gestão

```
POST /api/casa.php
  Input:  id_bloco, numero, andar, tipologia, estado, codigo
  Action: INSERT INTO apartamento
  
POST /api/vercasa.php
  Action: SELECT FROM apartamento (visualizar todas)
  
POST /api/vermoradores.php
  Action: SELECT FROM morador (visualizar todos)
```

### 📋 APIs Auxiliares

```
GET /api/teste.php
  Action: Verificar ligação BD (contadores)
  
GET /api/teste_login.php
  Action: Debug de login
  
POST /api/dadospessoais.php
  Action: Gerir dados pessoais
  
POST /api/funcionarios.php
  Input:  nome, telefone, email, nasc, nacionalidade, morada, 
          numbi, emissao, validade, locale, funcao, iban
  Action: INSERT INTO funcionarios (tabela legado)
```

---

## 4️⃣ MAPA DE CRUDs POR PÁGINA

### Legenda
- 🟢 **C** = Create (INSERT)
- 🔵 **R** = Read (SELECT)
- 🟡 **U** = Update (UPDATE)
- 🔴 **D** = Delete (DELETE)

### Operações por Módulo

```
┌─────────────────────┬─────────────────┬──────────────────┐
│ MÓDULO              │ OPERAÇÃO        │ TABELAS          │
├─────────────────────┼─────────────────┼──────────────────┤
│ AUTENTICAÇÃO        │ C(registar)     │ morador          │
│                     │ R(login)        │ administrador    │
│                     │ U(último login) │                  │
├─────────────────────┼─────────────────┼──────────────────┤
│ PERFIL MORADOR      │ R               │ morador          │
│                     │ U(dados)        │ morador_apartam. │
├─────────────────────┼─────────────────┼──────────────────┤
│ GESTÃO MORADORES    │ C, R, U, D      │ morador          │
│ (ADMIN)             │ C, R, U, D      │ morador_apartam. │
├─────────────────────┼─────────────────┼──────────────────┤
│ APARTAMENTOS        │ C, R, U, D      │ apartamento      │
│                     │ C, R            │ bloco            │
├─────────────────────┼─────────────────┼──────────────────┤
│ MENSALIDADES        │ C(gerar)        │ mensalidade      │
│                     │ R(listar)       │ mensalidade_pgto │
│                     │ U(estado)       │                  │
├─────────────────────┼─────────────────┼──────────────────┤
│ PAGAMENTOS          │ C(registar)     │ mensalidade_pgto │
│                     │ R(ver)          │                  │
│                     │ U(confirmar)    │                  │
├─────────────────────┼─────────────────┼──────────────────┤
│ OCORRÊNCIAS         │ C(reportar)     │ ocorrencia       │
│                     │ R(listar)       │                  │
│                     │ U(status)       │                  │
├─────────────────────┼─────────────────┼──────────────────┤
│ VISITAS             │ C(agendar)      │ visita           │
│                     │ R(ver)          │                  │
│                     │ U(autorizar)    │                  │
├─────────────────────┼─────────────────┼──────────────────┤
│ ÁREAS COMUNS        │ C(agendar)      │ agendamento      │
│                     │ R(ver)          │                  │
│                     │ U(confirmar)    │                  │
├─────────────────────┼─────────────────┼──────────────────┤
│ COMUNICAÇÃO         │ C(mensagem)     │ conversa         │
│                     │ R(listar)       │ conversa_part.   │
│                     │ C               │ mensagem         │
├─────────────────────┼─────────────────┼──────────────────┤
│ NOTIFICAÇÕES        │ C(auto)         │ notificacao      │
│                     │ R               │                  │
│                     │ U(lida)         │                  │
└─────────────────────┴─────────────────┴──────────────────┘
```

---

## 5️⃣ CAMPOS DE FORMULÁRIOS

### 📝 Formulário de LOGIN

```
┌─ MORADOR ─────────────────────────┐
│ numbi (text, 9-20 char, required) │
│ senha (password, 6+ char, req.)   │
│ [Entrar] [Esqueceu Senha]         │
└───────────────────────────────────┘

┌─ FUNCIONÁRIO ─────────────────────┐
│ numbi (text, 9-20 char, required) │
│ senha (password, 6+ char, req.)   │
│ [Entrar] [Esqueceu Senha]         │
└───────────────────────────────────┘
```

### 📝 Formulário de REGISTO MORADOR (Visitante)

```
OBRIGATÓRIOS:
  • nome (VARCHAR 120, required)
  • telefone (VARCHAR 20, required)
  • email (VARCHAR 120, unique, required)
  • numbi (VARCHAR 20, unique, required)
  • senha (VARCHAR 255, 6+ char, hashed)
  • data_nascimento (DATE, required)

OPCIONAIS (com defaults):
  • nacionalidade (default: 'Angolana')
  • morada (default: 'Luanda')
  • morada_anterior (VARCHAR 255)
  • emissao_bi (DATE, default: hoje)
  • validade_bi (DATE, default: +5 anos)
  • locale_bi (VARCHAR 80, default: 'Luanda')
```

### 📝 Formulário de REGISTO ADMIN

```
OBRIGATÓRIOS:
  • nome (VARCHAR 120)
  • email (VARCHAR 120, unique)
  • numbi (VARCHAR 20, unique)
  • telefone (VARCHAR 20)
  • nasc (DATE)
  • morada (VARCHAR 255)
  • emissao (DATE)
  • validade (DATE)
  • locale (VARCHAR 80)
  • funcao (ENUM dropdown)

OPCIONAIS:
  • nacionalidade (default: 'Angolana')
  • iban (VARCHAR 60)
  • senha (default: numbi, depois hashed)
```

### 📝 Formulário de MEU PERFIL (Morador)

```
VISUALIZAÇÃO (read-only):
  ✓ Nome
  ✓ Email
  ✓ BI
  ✓ Telefone
  ✓ Data Nascimento
  ✓ Nacionalidade
  ✓ Apartamento (Bloco + Número)
  ✓ Tipologia
  
EDITÁVEL (update):
  ⚠ Telefone
  ⚠ Email
  ⚠ Morada Anterior
  ⚠ Morada Actual
```

### 📝 Formulário de NOVA OCORRÊNCIA (Morador)

```
OBRIGATÓRIOS:
  • tipo (SELECT)
    - Avaria
    - Reclamacao
    - Sugestao
    - Outro
  • titulo (VARCHAR 120)
  • descricao (TEXT, 100+ chars)
  • prioridade (SELECT)
    - Baixa
    - Media
    - Alta
    - Urgente
```

### 📝 Formulário de NOVA VISITA (Morador)

```
OBRIGATÓRIOS:
  • nome_visitante (VARCHAR 120)
  • numbi_visitante (VARCHAR 20, opcional)
  • data_prevista (DATE)
  • hora_prevista (TIME, opcional)
  
AUTO-PREENCHIDO:
  • codigo_acesso (gerado automaticamente)
  • estado (default: 'pendente' → 'autorizado')
```

### 📝 Formulário de AGENDAMENTO ÁREA COMUM (Morador)

```
OBRIGATÓRIOS:
  • area_comum (SELECT)
    - Pisina
    - Salao de Festas
    - Churrasqueira
    - Campo Jogos
  • data_evento (DATE)
  • hora_inicio (TIME)
  • hora_fim (TIME)
  • notas (TEXT, opcional)
  
AUTO-PREENCHIDO:
  • estado (default: 'pendente')
```

### 📝 Formulário de PAGAMENTO DE MENSALIDADE (Morador)

```
OBRIGATÓRIOS:
  • id_mensalidade (SELECT)
  • valor_pago (DECIMAL 12,2)
  • metodo (SELECT)
    - Transferência
    - Multicaixa
    - Dinheiro
    - TPA
    - Outro

OPCIONAIS:
  • referencia (VARCHAR 80, ex: banco ref)
  • comprovativo_url (file upload)
  
AUTO-PREENCHIDO:
  • data_pagamento (NOW())
  • estado (default: 'pendente')
  • confirmado_por (NULL até admin confirmar)
```

### 📝 Formulário de CRIAR APARTAMENTO (Admin)

```
OBRIGATÓRIOS:
  • id_bloco (SELECT)
  • numero (VARCHAR 10, ex: "101", "202B")
  • andar (TINYINT)
  • tipologia (VARCHAR 10, ex: "V3", "T2")
  • estado (SELECT)
    - Disponivel
    - Ocupado
    - Manutencao
    - Reservado

OPCIONAIS:
  • area_m2 (DECIMAL 8,2)
  • codigo (VARCHAR 20, unique)
  • obs (TEXT)
```

### 📝 Formulário de CRIAR FUNCIONÁRIO (Admin)

```
Mesmo do registo admin (ver acima)
```

---

## 6️⃣ ESTRUTURA DA BASE DE DADOS

### 📊 Domínio 1: NÚCLEO (Estrutura Física)

```
┌── CONDOMINIO
│   id (PK)
│   nome, morada, cidade, pais
│   nif, telefone, email
│   mensalidade_base (140000 Kz padrão)
│   multa_diaria (500 Kz padrão)
│   iban, banco
│   criado_em, actualizado_em
│
├── BLOCO
│   id (PK)
│   id_condominio (FK)
│   letra (A, B, C, etc)
│   descricao
│
└── APARTAMENTO
    id (PK)
    id_bloco (FK)
    numero (101, 202B, etc)
    andar
    tipologia (V3, T2, etc)
    area_m2
    estado (Disponivel, Ocupado, Manutencao, Reservado)
    codigo (A-101, B-202, etc)
    obs
```

### 👤 Domínio 2: PESSOAS & ACESSOS

```
┌── ADMINISTRADOR
│   id (PK)
│   id_condominio (FK)
│   nome, email (unique), numbi (unique)
│   telefone, funcao
│   senha_hash
│   iban, activo
│   nasc, nacionalidade, morada
│   emissao_bi, validade_bi, locale_bi
│   ultimo_login
│   criado_em, actualizado_em
│
├── MORADOR
│   id (PK)
│   nome, email (unique), numbi (unique)
│   telefone, senha_hash
│   nasc, nacionalidade, morada_anterior
│   emissao_bi, validade_bi, locale_bi
│   estado_conta (Activo, Suspenso, Inactivo)
│   ultimo_login
│   criado_em, actualizado_em
│
└── MORADOR_APARTAMENTO
    id (PK)
    id_morador (FK)
    id_apartamento (FK)
    data_entrada
    data_saida (NULL = ocupa ainda)
    activo (1 = ocupante actual)
    obs
```

### 💰 Domínio 3: FINANCEIRO

```
┌── AQUISICAO (Contratos)
│   id (PK)
│   id_morador (FK)
│   id_apartamento (FK)
│   tipo (Renda, Compra, Reserva)
│   valor_total
│   data_contrato, data_inicio, data_fim
│   estado (Activo, Concluido, Cancelado)
│   notas
│
├── MENSALIDADE (Quotas geradas)
│   id (PK)
│   id_morador (FK)
│   id_apartamento (FK)
│   servico (Renda Mensal, Quota Condominal, Manutencao, Multa)
│   mes, ano
│   valor, vencimento
│   estado (pendente, pago, atrasado, dispensado)
│   unique: (id_morador, id_apartamento, servico, mes, ano)
│
└── MENSALIDADE_PAGAMENTO (Efectivos)
    id (PK)
    id_mensalidade (FK)
    valor_pago, metodo
    referencia, comprovativo_url
    data_pagamento
    estado (pendente, confirmado, rejeitado)
    confirmado_por (FK admin)
    notas_admin
```

### 📢 Domínio 4: COMUNICAÇÃO

```
┌── CONVERSA
│   id (PK)
│   tipo (privada, grupo, anuncio)
│   titulo (para grupos/anúncios)
│   criado_por (FK morador/admin)
│   criado_em
│
├── CONVERSA_PARTICIPANTE
│   id (PK)
│   id_conversa (FK)
│   tipo_user (morador, administrador)
│   id_user (referência a morador/admin)
│   entrou_em, saiu_em
│   unique: (id_conversa, tipo_user, id_user)
│
└── MENSAGEM
    id (PK)
    id_conversa (FK)
    tipo_remetente (morador, administrador)
    id_remetente
    conteudo
    anexo_url
    lida (0/1)
    enviada_em
```

### 🚨 Domínio 5: OPERAÇÕES

```
┌── OCORRENCIA
│   id (PK)
│   id_morador (FK)
│   id_apartamento (FK, nullable)
│   tipo (Avaria, Reclamacao, Sugestao, Outro)
│   titulo, descricao
│   prioridade (Baixa, Media, Alta, Urgente)
│   estado (aberta, em_analise, resolvida, encerrada)
│   resolvida_por (FK admin)
│   data_resolucao, notas_admin
│
├── NOTIFICACAO
│   id (PK)
│   tipo_destino (morador, administrador, todos)
│   id_destino (NULL = broadcast)
│   titulo, mensagem
│   tipo (info, aviso, alerta, pagamento, ocorrencia)
│   entidade_ref (ex: mensalidade:12, ocorrencia:3)
│   lida
│
├── VISITA
│   id (PK)
│   id_morador (FK)
│   id_apartamento (FK)
│   nome_visitante, numbi_visitante
│   data_prevista, hora_prevista
│   estado (pendente, autorizado, entrada_registada, saida_reg, negado)
│   codigo_acesso (único, gerado)
│
└── AGENDAMENTO (Áreas comuns)
    id (PK)
    id_morador (FK)
    area_comum (Pisina, Salao de Festas, Churrasqueira, Campo Jogos)
    data_evento, hora_inicio, hora_fim
    estado (pendente, confirmado, cancelado)
    notas
```

---

## 7️⃣ MAPA VISUAL: UTILIZADOR → PÁGINA → API → BD

### 🏠 Fluxo do MORADOR

```
MORADOR
├─ Login (loginmorador.php) ─────────────────→ SELECT morador
│
├─ Dashboard Morador
│  ├─ api_morador.php?acao=resumo_financeiro ──→ SELECT mensalidade SUM
│  ├─ api_morador.php?acao=perfil ─────────────→ SELECT morador
│  └─ COUNT ocorrências ────────────────────────→ SELECT ocorrencia
│
├─ Meu Perfil
│  ├─ Ver dados ────────────────────────────────→ SELECT morador
│  └─ Editar dados ──────────────────────────────→ UPDATE morador
│
├─ Mensalidades
│  ├─ Listar ────────────────────────────────────→ SELECT mensalidade
│  ├─ Pagar ─────────────────────────────────────→ INSERT mensalidade_pagto
│  └─ Ver status ────────────────────────────────→ SELECT mensalidade_pagto
│
├─ Ocorrências
│  ├─ Criar ─────────────────────────────────────→ INSERT ocorrencia
│  ├─ Listar ────────────────────────────────────→ SELECT ocorrencia
│  └─ Ver detalhe ──────────────────────────────→ SELECT ocorrencia
│
├─ Visitas
│  ├─ Agendar ───────────────────────────────────→ INSERT visita
│  └─ Listar ────────────────────────────────────→ SELECT visita
│
├─ Áreas Comuns
│  ├─ Agendar ───────────────────────────────────→ INSERT agendamento
│  └─ Ver reservas ──────────────────────────────→ SELECT agendamento
│
└─ Comunicação
   ├─ Ver avisos ──────────────────────────────→ SELECT notificacao
   └─ Enviar msg ──────────────────────────────→ INSERT mensagem
```

### 🔧 Fluxo do ADMIN

```
ADMIN
├─ Login (loginfuncionario.php) ────────────→ SELECT administrador
│
├─ Dashboard Admin
│  ├─ api_dashboard.php?acao=resumo ────────→ COUNT * FROM várias tabelas
│  ├─ KPIs (moradores, apartamentos, etc.) ─→ SELECT COUNT()
│  └─ Gráficos ────────────────────────────→ SELECT (financeiro)
│
├─ Funcionários (Gestão)
│  ├─ Registar ──────────────────────────────→ INSERT administrador
│  ├─ Listar ────────────────────────────────→ SELECT administrador
│  ├─ Editar ────────────────────────────────→ UPDATE administrador
│  └─ Deletar ───────────────────────────────→ DELETE administrador
│
├─ Moradores (Gestão)
│  ├─ Registar ──────────────────────────────→ INSERT morador
│  ├─ Listar ────────────────────────────────→ SELECT morador
│  ├─ Suspender ────────────────────────────→ UPDATE morador.estado
│  └─ Ver histórico ────────────────────────→ SELECT morador_apartamento
│
├─ Apartamentos
│  ├─ Criar ────────────────────────────────→ INSERT apartamento
│  ├─ Listar ────────────────────────────────→ SELECT apartamento
│  ├─ Editar estado ────────────────────────→ UPDATE apartamento.estado
│  └─ Associar morador ─────────────────────→ INSERT morador_apartamento
│
├─ Mensalidades
│  ├─ Gerar (batch) ────────────────────────→ INSERT mensalidade (automático/manual)
│  ├─ Listar ────────────────────────────────→ SELECT mensalidade
│  ├─ Editar valor ────────────────────────→ UPDATE mensalidade.valor
│  └─ Marcar pago ──────────────────────────→ UPDATE mensalidade.estado
│
├─ Pagamentos
│  ├─ Listar comprovantes ──────────────────→ SELECT mensalidade_pagto
│  ├─ Confirmar ────────────────────────────→ UPDATE mensalidade_pagto.estado
│  ├─ Rejeitar ──────────────────────────────→ UPDATE mensalidade_pagto.estado
│  └─ Gerar relatório ──────────────────────→ SELECT SUM(valor_pago)
│
├─ Visitas
│  ├─ Listar pendentes ────────────────────→ SELECT visita WHERE estado='pendente'
│  ├─ Autorizar ─────────────────────────────→ UPDATE visita.estado
│  ├─ Negar acesso ──────────────────────────→ UPDATE visita.estado
│  └─ Registar entrada/saída ───────────────→ UPDATE visita.estado
│
├─ Áreas Comuns
│  ├─ Listar agendamentos ──────────────────→ SELECT agendamento
│  ├─ Confirmar ────────────────────────────→ UPDATE agendamento.estado
│  └─ Cancelar ──────────────────────────────→ UPDATE agendamento.estado
│
└─ Relatórios
   ├─ Relatório Mensal ───────────────────→ SELECT (financeiro por período)
   ├─ Inadimplência ──────────────────────→ SELECT mensalidade WHERE atrasado
   ├─ Ocupação ────────────────────────────→ SELECT COUNT(estado='Ocupado')
   └─ Exportar (CSV/PDF) ─────────────────→ Output dados
```

### 👤 Fluxo do VISITANTE

```
VISITANTE
└─ Registo (visitante.html) ────────────────→ INSERT morador
   └─ Redirecciona para login
```

---

## 8️⃣ VALIDAÇÕES IMPLEMENTADAS

### 🔒 Segurança da Autenticação

```
✓ Comparação de senha: password_verify() + fallback text plano
✓ Hashing: password_hash(PASSWORD_DEFAULT) recomendado
✓ Sessão: $_SESSION['tipo'] === 'morador' | 'admin'
✓ Verificação estado conta: estado_conta = 'Activo'
✓ Último login atualizado em SELECT success
✓ Proteção de acesso: header() redirect se sem autenticação
```

### ✅ Validação de Formulários (Cliente)

```
MORADOR LOGIN:
  ✓ BI: padrão [A-Za-z0-9]{9,20}
  ✓ Senha: mínimo 6 caracteres

REGISTO MORADOR:
  ✓ Email: validação HTML5
  ✓ Telefone: formato obrigatório
  ✓ BI: único (CHECK BD)
  ✓ Email: único (CHECK BD)
  ✓ Datas: input type=date

PAGAMENTO:
  ✓ Valor: DECIMAL 12,2 (max 10 dígitos + 2 casas)
  ✓ Referência: length <= 80
  ✓ Método: ENUM (4 opções)
```

### ⚠️ Validação de Dados (Servidor)

```php
// Exemplo: registar_morador.php
if (!$nome || !$telefone || !$email || !$numbi || !$senha) {
    header("Location: ../login.html?erro=campos");
    exit;
}

// Verificar duplicatas
$chk = $conexao->prepare("SELECT id FROM morador WHERE numbi = ? OR email = ?");

// Hash da senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
```

---

## 9️⃣ ESTADO DA IMPLEMENTAÇÃO

| Funcionalidade | Status | Notas |
|---|---|---|
| **Autenticação Morador** | ✅ Completo | Login/logout, verificação estado conta |
| **Autenticação Admin** | ✅ Completo | Com logging de debug |
| **Dashboard Morador** | ✅ Completo | KPIs básicos, widgets |
| **Dashboard Admin** | ✅ Completo | Tabs, gráficos Chart.js |
| **Gestão Moradores** | ⚠️ Parcial | Read/Update, Delete comentado |
| **Gestão Apartamentos** | ⚠️ Parcial | CRUD em API, não em UI |
| **Mensalidades** | ✅ Completo | Geração, listagem, status |
| **Pagamentos** | ⚠️ Parcial | Registo, confirmação básica |
| **Ocorrências** | ✅ Completo | CRUD morador, visualização admin |
| **Visitas** | ✅ Completo | Agendamento, autorização |
| **Áreas Comuns** | ⚠️ Parcial | UI stub (botão "em dev"), API existe |
| **Comunicação** | ⚠️ Parcial | UI stub, tabelas BD criadas |
| **Relatórios** | ⚠️ Parcial | API básica, UI não final |
| **Notificações** | ⚠️ Parcial | Tabela criada, não integrada |

---

## 🔟 MELHORIAS RECOMENDADAS

### 🔴 Críticas
1. **Comentar proteção admin em `registar_admin.php`** — Qualquer um pode criar admin!
   ```php
   if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
       http_response_code(403);
       exit;
   }
   ```

2. **Melhorar validações de entrada** — Usar sanitização com `htmlspecialchars()`, `strip_tags()`

3. **Prepared statements em todas as queries** — `api_morador.php` tem SQL injection em linha 78-86 (directo)

4. **Adicionar CSRF tokens** — Formulários sem proteção

5. **Logs de auditoria** — INSERT/UPDATE/DELETE sem rastreio

### 🟡 Importantes
6. **Paginação** — Listas sem limite (SELECT * sem LIMIT)

7. **Rate limiting** — Login sem proteção contra brute force

8. **Confirmação por email** — Registo directo sem validação

9. **Backup automático** — Nenhuma menção a backup

10. **Soft delete** — DELETE completo, preferir flag `activo=0`

### 🟢 Melhorias Futuras
11. Notificações em tempo real (WebSocket/Polling)
12. Two-factor authentication (2FA)
13. API REST completa (não apenas dashboard)
14. Mobile app (iOS/Android)
15. Integração bancária automática de pagamentos

---

## 📞 SUPORTE TÉCNICO

**Base de Dados**: `condominio_nz` em `localhost` (XAMPP)  
**Servidor Web**: `http://localhost/SIG-Condominio/web`  
**Documentação SQL**: Ver [condominio_nz.sql](./api/condominio_nz.sql)  
**Dados de Teste**: Ver [dados_teste.sql](./api/dados_teste.sql)  

---

**Gerado em**: 2026-06-23  
**Versão BD**: 2.0  
**Charset**: UTF-8MB4
