# 🎯 MAPA RÁPIDO DE REFERÊNCIA

## Acesso por Tipo de Utilizador

```
┌──────────────────────────────────────────────────────────────────────────┐
│                        MATRIZ DE ACESSO                                  │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  MORADOR                    ADMIN                      VISITANTE          │
│  ━━━━━━━━━━━━━━━            ━━━━━━━━                   ━━━━━━━━━━         │
│  • Login via BI             • Login via BI             • Sem login         │
│  • Dashboard pessoal        • Dashboard admin          • Auto-registo      │
│  • Meu perfil               • Gestão de pessoal        • Espera aprovação  │
│  • Minhas mensalidades      • Gestão de moradores                         │
│  • Minhas ocorrências       • Gestão de apartamentos                      │
│  • Agendar visitas          • Gestão financeira                           │
│  • Reservar áreas comuns    • Autorizar visitas                          │
│  • Ver comunicação          • Confirmar pagamentos                       │
│                             • Gerar relatórios                           │
│                                                                           │
└──────────────────────────────────────────────────────────────────────────┘
```

---

## Páginas Disponíveis

### 📱 Estrutura de Ficheiros

```
web/
├── index.html                    ← Página inicial
├── login.html                    ← Portal de login (Morador + Admin)
├── sobre.html                    ← Sobre o condomínio
│
├── api/
│   ├── conexao.php              ← Conexão BD (MySQL)
│   ├── loginmorador.php         ← POST: autenticação morador
│   ├── loginfuncionario.php     ← POST: autenticação admin
│   ├── logout.php               ← GET: terminar sessão
│   ├── registar_morador.php     ← POST: novo morador (visitante)
│   ├── registar_admin.php       ← POST: novo admin ⚠️ SEM PROTEÇÃO
│   ├── api_morador.php          ← GET: dados morador (JSON)
│   ├── api_dashboard.php        ← GET: dados admin (JSON)
│   ├── pagar.php                ← POST: registar pagamento
│   ├── casa.php                 ← POST: criar apartamento
│   ├── vercasa.php              ← GET: listar apartamentos
│   ├── vermoradores.php         ← GET: listar moradores
│   ├── funcionarios.php         ← POST: criar funcionário
│   ├── dadospessoais.php        ← POST: editar dados
│   ├── teste_login.php          ← DEBUG: testar login
│   ├── teste.php                ← DEBUG: testar conexão BD
│   └── condominio_nz.sql        ← SCHEMA da BD
│
├── pages/
│   ├── dashboard_morador.php    ← Dashboard morador
│   ├── dashboard.php            ← Dashboard admin
│   ├── meu_perfil.php           ← Perfil do morador
│   ├── minhas_mensalidades.php  ← Quotas a pagar
│   ├── minhas_ocorrencias.php   ← Avarias/reclamações
│   ├── comunicacao.php          ← Mensagens/avisos
│   ├── visitas.php              ← Agendamento de visitas
│   ├── areas_comuns.php         ← Reserva de áreas (stub)
│   ├── esqueceu-senha.html      ← Recuperação de senha
│   ├── morador-gest.html        ← Gestão morador
│   ├── formulario_funcionario.html ← Registo de staff
│   └── login1.html              ← Alt. login page
│
├── Visitante/
│   └── visitante.html           ← Registo de visitante
│
├── css/
│   ├── admin.css                ← Dashboard admin
│   ├── login.css                ← Página de login
│   ├── morador.css              ← Dashboard morador
│   ├── index.css                ← Página inicial
│   ├── nosso-zimbo-admin.css    ← Sidebar + layout geral
│   ├── sobre.css                ← Página "Sobre"
│   └── [outros]
│
├── js/
│   ├── admin_dashboard.js       ← Scripts dashboard admin
│   ├── admin.js                 ← Scripts gerais admin
│   ├── login.js                 ← Scripts login
│   └── index.js                 ← Scripts página inicial
│
└── img/
    └── [imagens]
```

---

## Endpoints da API

### 📥 POST (Escrita)

| Endpoint | Parâmetros | Ação | Autenticação |
|----------|-----------|------|---|
| `api/loginmorador.php` | numbi, senha | Login morador | ❌ Pública |
| `api/loginfuncionario.php` | numbi, senha | Login admin | ❌ Pública |
| `api/registar_morador.php` | nome, email, numbi, senha, ... | Criar novo morador | ❌ Pública |
| `api/registar_admin.php` | nome, email, numbi, funcao, ... | Criar novo admin | ⚠️ Pública (BUG) |
| `api/pagar.php` | id_mensalidade, valor, metodo | Registar pagamento | ✅ Morador |
| `api/casa.php` | id_bloco, numero, andar, ... | Criar apartamento | ✅ Admin |
| `api/funcionarios.php` | nome, email, telefone, ... | Criar funcionário (legado) | ✅ Admin |
| `api/dadospessoais.php` | [campos] | Editar dados pessoais | ✅ Morador |

### 📤 GET (Leitura)

| Endpoint | Parâmetros | Retorna | Autenticação |
|----------|-----------|---------|---|
| `api/logout.php` | - | Termina sessão | ✅ Autenticado |
| `api/api_morador.php?acao=X` | acao | JSON dados morador | ✅ Morador |
| `api/api_dashboard.php?acao=X` | acao | JSON dados admin | ✅ Admin |
| `api/vercasa.php` | - | Lista apartamentos | ❓ Sem proteção |
| `api/vermoradores.php` | - | Lista moradores | ❓ Sem proteção |
| `api/teste.php` | - | Contadores BD | ❌ Debug |
| `api/teste_login.php` | - | Testa login | ❌ Debug |

### 🔀 Ações de `api_morador.php?acao=X`

```
perfil                  → Dados pessoais do morador + apartamento
mensalidades            → Todas as quotas
historico_pagamentos    → Histórico de pagamentos efectuados
resumo_financeiro       → Totais (pendente, pago, meses)
vizinhos                → Moradores do mesmo bloco
visitas                 → Visitas agendadas
novo_agendamento_visita → Agendar visitante (POST)
agendamentos_area       → Reservas de áreas comuns
novo_agendamento_area   → Agendar área comum (POST)
```

### 🔀 Ações de `api_dashboard.php?acao=X`

```
resumo              → KPIs: total moradores, admins, apartamentos, etc
casas               → Lista de todos os apartamentos
moradores           → Lista de todos os moradores
admins              → Lista de funcionários
mensalidades        → Todas as quotas por morador
pagamentos          → Histórico de pagamentos
confirmar_pagamento → Aprovar/rejeitar pagamento (POST)
```

---

## Estados em BD

### 📊 Tabela: `morador`

```
estado_conta ENUM:
  ✓ Activo      → Pode fazer login
  ⚠️ Suspenso    → Login bloqueado
  ❌ Inactivo    → Não é morador
```

### 💰 Tabela: `mensalidade`

```
estado ENUM:
  ⏳ pendente    → Não pago, no prazo
  🔴 atrasado    → Não pago, vencido
  ✅ pago        → Pagamento confirmado
  🎫 dispensado  → Perdão administrativo
```

### 💳 Tabela: `mensalidade_pagamento`

```
estado ENUM:
  ⏳ pendente     → Aguardando confirmação admin
  ✅ confirmado   → Aprovado pela admin
  ❌ rejeitado    → Pagamento recusado
```

### 🚪 Tabela: `apartamento`

```
estado ENUM:
  ✓ Disponivel   → Vago, pronto para ocupação
  🏠 Ocupado      → Tem morador
  🔧 Manutencao   → Em reparação
  🔒 Reservado    → Reservado para futuro
```

### 📢 Tabela: `ocorrencia`

```
estado ENUM:
  📝 aberta      → Acabou de ser reportada
  🔍 em_analise  → Admin está a verificar
  ✅ resolvida   → Problema resolvido
  ✔️ encerrada   → Encerrada (arquivo)

prioridade ENUM:
  🟢 Baixa       → Sem urgência
  🟡 Media       → Normal
  🟠 Alta        → Importante
  🔴 Urgente     → Crítica
```

### 🚗 Tabela: `visita`

```
estado ENUM:
  ⏳ pendente              → Aguardando aprovação
  ✅ autorizado            → Aprovada, pode entrar
  📍 entrada_registada    → Visitante entrou
  📍 saida_registada      → Visitante saiu
  ❌ negado               → Acesso recusado
```

### 📅 Tabela: `agendamento` (Áreas Comuns)

```
estado ENUM:
  ⏳ pendente    → Aguardando confirmação
  ✅ confirmado  → Aprovada
  ❌ cancelado   → Cancelada
```

### 👨‍💼 Tabela: `administrador`

```
funcao ENUM:
  👑 Super Admin         → Acesso total
  👔 Administrador       → Gestão completa
  👥 Recursos Humanos    → Gestão staff
  🚨 Seguranca           → Controlo acesso
  🔧 Area Tecnica        → Manutenção
```

---

## Fluxos Principais

### 1️⃣ Fluxo de Autenticação

```
┌─────────────────────┐
│  Visitante na Web   │
└──────────┬──────────┘
           │
           ↓ Acessa /login.html
┌─────────────────────────────────────────┐
│         Portal de Login                 │
│  ┌──────────────────────────────────┐  │
│  │ Morador:        Admin:           │  │
│  │ • BI            • BI             │  │
│  │ • Senha         • Senha          │  │
│  │ [Login]         [Login]          │  │
│  └──────────────────────────────────┘  │
└───────────┬─────────────────────────────┘
            │
    ┌───────┴──────────┐
    ↓                  ↓
┌────────────────┐  ┌──────────────────┐
│ loginmorador.  │  │loginfuncionario. │
│     php        │  │      php         │
└────────┬───────┘  └──────────┬───────┘
         │                     │
         ↓ SELECT morador      ↓ SELECT administrador
    ┌──────────────┐      ┌────────────────┐
    │   password_  │      │   password_    │
    │   verify()   │      │   verify()     │
    └────┬─────────┘      └────────┬───────┘
         │                         │
    ✅ OK?                    ✅ OK?
    │                         │
    ├─ Cria $_SESSION         ├─ Cria $_SESSION
    │  (tipo='morador')        │  (tipo='admin')
    │  (id, nome, email)       │  (id, nome, funcao)
    │  (ultimo_login = NOW())  │  (ultimo_login = NOW())
    │                          │
    ↓                          ↓
  ┌──────────────────┐   ┌──────────────────┐
  │ dashboard_       │   │   dashboard.php  │
  │ morador.php      │   │                  │
  └──────────────────┘   └──────────────────┘
```

### 2️⃣ Fluxo de Pagamento de Mensalidade

```
┌──────────────────────┐
│    Morador login     │
└─────────────┬────────┘
              ↓
        ┌─────────────────────────────────┐
        │ minhas_mensalidades.php         │
        │ • Lista quotas (SELECT)         │
        │ • Mostra status (pendente/etc)  │
        │ • Botão [Pagar]                 │
        └──────────┬──────────────────────┘
                   ↓
        ┌──────────────────────────────┐
        │ Modal / Form de Pagamento    │
        │ • Selecionar qual pagar      │
        │ • Inserir valor (v.pré-fill) │
        │ • Selecionar método:         │
        │   - Transferência            │
        │   - Multicaixa               │
        │   - Dinheiro                 │
        │   - TPA                      │
        │ • Inserir referência (opt)   │
        │ • Upload comprovativo (opt)  │
        │ [Enviar]                     │
        └──────────┬───────────────────┘
                   ↓ POST
        ┌──────────────────────────────┐
        │   pagar.php                  │
        │ • INSERT mensalidade_pgto    │
        │ • estado = 'pendente'        │
        │ • confirmado_por = NULL      │
        └──────────┬───────────────────┘
                   ↓
        ┌──────────────────────────────┐
        │   Morador vê "Aguardando"    │
        │   Admin recebe notificação   │
        └──────────┬───────────────────┘
                   ↓
        ┌──────────────────────────────┐
        │ Admin login / dashboard      │
        │ • Vai a "Pagamentos"         │
        │ • Vê lista de pendentes      │
        │ • Abre comprovativo          │
        │ • Seleciona [Confirmar]      │
        └──────────┬───────────────────┘
                   ↓ POST
        ┌──────────────────────────────┐
        │ api_dashboard.php?acao=      │
        │ confirmar_pagamento          │
        │ • UPDATE estado = 'confirmado'
        │ • confirmado_por = $id_admin │
        │ • notas_admin = [observ]     │
        │ • UPDATE mensalidade estado  │
        │   = 'pago'                   │
        └──────────┬───────────────────┘
                   ↓
        ┌──────────────────────────────┐
        │ Morador notificado           │
        │ Quota marca como ✅ PAGO     │
        └──────────────────────────────┘
```

### 3️⃣ Fluxo de Reportar Ocorrência

```
┌──────────────────────────────┐
│  Morador login               │
└───────────┬──────────────────┘
            ↓
   ┌────────────────────────────────────┐
   │ minhas_ocorrencias.php             │
   │ • Lista ocorrências reportadas     │
   │ • Mostra status (aberta/resolvida) │
   │ • Botão [Nova Ocorrência]          │
   └────────────┬─────────────────────┘
                ↓
   ┌────────────────────────────────────┐
   │ Form de Nova Ocorrência            │
   │ • Tipo:                            │
   │   - Avaria                         │
   │   - Reclamação                     │
   │   - Sugestão                       │
   │   - Outro                          │
   │ • Título (VARCHAR 120)             │
   │ • Descrição (TEXT)                 │
   │ • Prioridade:                      │
   │   - Baixa                          │
   │   - Média                          │
   │   - Alta                           │
   │   - Urgente                        │
   │ [Submeter]                         │
   └────────────┬─────────────────────┘
                ↓ POST
   ┌────────────────────────────────────┐
   │ minhas_ocorrencias.php (POST)      │
   │ • INSERT ocorrencia                │
   │ • estado = 'aberta'                │
   │ • id_morador = $SESSION['id']     │
   └────────────┬─────────────────────┘
                ↓
   ┌────────────────────────────────────┐
   │ Morador vê "Enviado com sucesso!"  │
   │ Admin notificado via BD            │
   └────────────┬─────────────────────┘
                ↓
   ┌────────────────────────────────────┐
   │ Admin login / dashboard            │
   │ • Vai a módulo de Ocorrências      │
   │ • Vê lista de abertas              │
   │ • Clica para abrir detalhe         │
   │ • Vê: morador, tipo, descr., prio │
   │ • Seleciona [Em Análise] ou        │
   │          [Resolvida]               │
   │ • Adiciona notas_admin (opt)       │
   │ [Guardar]                          │
   └────────────┬─────────────────────┘
                ↓
   ┌────────────────────────────────────┐
   │ UPDATE ocorrencia                  │
   │ • estado = 'em_analise'|'resolvida'│
   │ • resolvida_por = $id_admin       │
   │ • data_resolucao = NOW()           │
   │ • notas_admin = [texto]            │
   └────────────┬─────────────────────┘
                ↓
   ┌────────────────────────────────────┐
   │ Morador vê ocorrência com status   │
   │ ✅ Resolvida / 🔍 Em análise       │
   └────────────────────────────────────┘
```

---

## 🔑 Passwords de Teste

### Admin Padrão

```
Email: admin@nossozimbo.ao
BI:    000000000
Senha: Admin@2026
Hash:  $2y$12$eT3hWJz.vv.sXW3p5oF4OuWdRV3CfbW5v8y6hJ2K9mL1nP7qR0sOe
```

---

## ⚠️ Problemas Conhecidos & TODOs

| ID | Problema | Severidade | Status |
|---|---|---|---|
| BUG001 | `registar_admin.php` sem autenticação | 🔴 CRÍTICA | ❌ NÃO RESOLVIDO |
| BUG002 | SQL Injection em `api_morador.php` (linha 78-86) | 🔴 CRÍTICA | ❌ NÃO RESOLVIDO |
| BUG003 | Sem CSRF tokens em formulários | 🔴 CRÍTICA | ❌ NÃO RESOLVIDO |
| BUG004 | DELETE completo (sem soft delete) | 🟡 IMPORTANTE | ❌ NÃO RESOLVIDO |
| BUG005 | Sem validação HTML/input sanitization | 🟡 IMPORTANTE | ❌ NÃO RESOLVIDO |
| BUG006 | Login sem rate limiting (brute force) | 🟡 IMPORTANTE | ❌ NÃO RESOLVIDO |
| FEAT001 | Áreas comuns UI stub (botão "em dev") | 🟢 MENOR | ⏳ IN PROGRESS |
| FEAT002 | Comunicação UI stub | 🟢 MENOR | ⏳ IN PROGRESS |
| FEAT003 | Paginação em listas | 🟢 MENOR | ❌ NÃO INICIADO |
| FEAT004 | Notificações em tempo real | 🟡 IMPORTANTE | ❌ NÃO INICIADO |
| FEAT005 | 2FA (Two-Factor Authentication) | 🟡 IMPORTANTE | ❌ NÃO INICIADO |
| FEAT006 | Recuperação de senha | 🟡 IMPORTANTE | ⏳ STUB |

---

## 🚀 Como Começar

### 1. Verificar Conexão BD
```bash
cd web/api
php teste.php
# Deve mostrar contadores de moradores e admins
```

### 2. Fazer Login de Teste
```
URL: http://localhost/SIG-Condominio/web/login.html
BI:   000000000
Senha: Admin@2026
```

### 3. Explorar Dashboard
- Como Admin: Acesso total aos KPIs
- Como Morador: Listar mensalidades, criar ocorrências

### 4. Testar API
```bash
curl "http://localhost/SIG-Condominio/web/api/api_dashboard.php?acao=resumo"
# Deve retornar JSON com resumo
```

---

**Última atualização**: 2026-06-23  
**Responsável**: Sistema Integrado de Gestão  
**Versão**: 1.0
