# 🎫 CHEAT SHEET — SIG-Condominio

## 1️⃣ ACESSO RÁPIDO

```
┌─────────────────────────────────────────────────────────────────┐
│                    PORTAL DE LOGIN                              │
│  URL: http://localhost/SIG-Condominio/web/login.html           │
│                                                                 │
│  ADMIN (Funcionário):         MORADOR:                         │
│  BI: 000000000                BI: (em dados_teste.sql)         │
│  Senha: Admin@2026            Senha: (mesma BD)                │
└─────────────────────────────────────────────────────────────────┘
```

## 2️⃣ MATRIZ DE PÁGINAS

```
MORADOR
├─ dashboard_morador.php     → KPIs, atalhos
├─ meu_perfil.php             → Ver/editar dados
├─ minhas_mensalidades.php    → Listar quotas + pagar
├─ minhas_ocorrencias.php     → Reportar avarias + acompanhar
├─ comunicacao.php            → Avisos do condomínio
├─ visitas.php                → Agendar visitantes
└─ areas_comuns.php           → Reservar piscina/salão (stub)

ADMIN
├─ dashboard.php              → Dashboard principal
│  ├─ Tab: Funcionários       → CRUD staff
│  ├─ Tab: Moradores          → CRUD moradores
│  ├─ Tab: Apartamentos       → CRUD unidades
│  ├─ Tab: Mensalidades       → Gerar quotas
│  ├─ Tab: Pagamentos         → Confirmar/rejeitar
│  ├─ Tab: Visitas            → Autorizar acesso
│  ├─ Tab: Áreas Comuns       → Confirmar reservas
│  └─ Tab: Relatórios         → Estatísticas
```

## 3️⃣ ENDPOINTS API (JSON)

### Morador
```
GET  /api/api_morador.php?acao=perfil
GET  /api/api_morador.php?acao=mensalidades
GET  /api/api_morador.php?acao=resumo_financeiro
GET  /api/api_morador.php?acao=visitas
POST /api/api_morador.php → acao=novo_agendamento_visita
```

### Admin
```
GET  /api/api_dashboard.php?acao=resumo
GET  /api/api_dashboard.php?acao=moradores
GET  /api/api_dashboard.php?acao=mensalidades
POST /api/api_dashboard.php → acao=confirmar_pagamento
```

### Autenticação
```
POST /api/loginmorador.php       → numbi, senha
POST /api/loginfuncionario.php   → numbi, senha
GET  /api/logout.php             → Terminar sessão
```

## 4️⃣ TABELAS CHAVE

```
MORADOR (Id, Nome, Email, BI, Telefone, Estado)
├─ Estado: Activo | Suspenso | Inactivo
└─ Index: email (unique), numbi (unique)

ADMINISTRADOR (Id, Nome, Email, BI, Funcao, Activo)
├─ Funcao: Super Admin | Administrador | RH | Segurança | Técnica
└─ Index: email (unique), numbi (unique)

APARTAMENTO (Id, Bloco, Número, Andar, Tipologia, Estado)
├─ Estado: Disponivel | Ocupado | Manutencao | Reservado
└─ Ligação: morador_apartamento (histórico)

MENSALIDADE (Id, Morador, Apartamento, Mês, Ano, Valor, Estado)
├─ Estado: pendente | pago | atrasado | dispensado
└─ Pagamento: mensalidade_pagamento (1:N)

OCORRENCIA (Id, Morador, Tipo, Título, Descr., Prioridade, Estado)
├─ Tipo: Avaria | Reclamação | Sugestão | Outro
├─ Prioridade: Baixa | Média | Alta | Urgente
└─ Estado: aberta | em_analise | resolvida | encerrada

VISITA (Id, Morador, Apartamento, Nome_Visitante, Data, Estado)
└─ Estado: pendente | autorizado | entrada_reg | saida_reg | negado
```

## 5️⃣ CRUDs POR MÓDULO

| Módulo | Create | Read | Update | Delete |
|--------|--------|------|--------|--------|
| **Morador** | registar_morador.php | dashboard | meu_perfil.php | ❌ (soft delete) |
| **Funcionário** | registar_admin.php ⚠️ | dashboard | - | - |
| **Apartamento** | casa.php | vercasa.php | dashboard | - |
| **Mensalidade** | - (gerar) | minhas_mensalidades.php | pagar.php | - |
| **Pagamento** | pagar.php | dashboard | confirmar (admin) | - |
| **Ocorrência** | minhas_ocorrencias.php | minhas_ocorrencias.php | admin (status) | - |
| **Visita** | visitas.php | visitas.php | admin (status) | - |
| **Área Comum** | areas_comuns.php | areas_comuns.php | admin (status) | - |

## 6️⃣ VALIDAÇÕES (Cliente → Servidor → BD)

### ⚠️ CRÍTICAS
```
□ registar_admin.php → SEM CHECK $_SESSION['tipo']==='admin'
□ api_morador.php    → SQL Injection em linha 78 (interpolação)
□ pagar.php          → Upload sem validação (RCE possível)
□ LOGIN              → Sem rate limiting (brute force)
□ Formulários        → Sem CSRF tokens
```

### 🟡 IMPORTANTES
```
□ Email              → HTML5 validation, sem envio confirmação
□ Telefone           → Aceita qualquer coisa
□ Datas              → Sem validação range (futuro/passado)
□ Upload             → Sem whitelist tipos, sem scan malware
□ Paginação          → SELECT * sem LIMIT
```

### ✅ BOM
```
✓ BI                 → pattern="[A-Za-z0-9]{9,20}", unique
✓ Senha              → password_hash/verify, bcrypt
✓ ENUM               → BD enforcement (tipo, estado, funcao)
✓ Prepared statements → Maioria de queries
```

## 7️⃣ FLUXOS PRINCIPAIS

### Login Morador
```
login.html 
  ↓ POST BI + Senha
api/loginmorador.php
  ↓ SELECT morador WHERE numbi
  ↓ password_verify()
  ↓ UPDATE ultimo_login
  ↓ $_SESSION['tipo']='morador'
  ↓ Redirect
dashboard_morador.php
```

### Pagar Mensalidade
```
minhas_mensalidades.php
  ↓ SELECT mensalidade WHERE id_morador
  ↓ [Clica Pagar]
  ↓ Form: valor, metodo, referencia
  ↓ POST pagar.php
  ↓ INSERT mensalidade_pagamento (estado='pendente')
  ↓ Admin vê em dashboard → confirmar_pagamento
  ↓ UPDATE estado='confirmado'
  ↓ Morador vê "✅ Pago"
```

### Reportar Ocorrência
```
minhas_ocorrencias.php
  ↓ [Nova Ocorrência]
  ↓ Form: tipo, titulo, descricao, prioridade
  ↓ POST minhas_ocorrencias.php
  ↓ INSERT ocorrencia (estado='aberta')
  ↓ Admin notificado
  ↓ Admin clica → edita estado
  ↓ UPDATE ocorrencia (estado='resolvida')
  ↓ Morador vê status atualizado
```

## 8️⃣ QUERIES PRINCIPAIS

### Morador Logado
```sql
-- Dados pessoais
SELECT m.*, a.numero, bl.letra 
FROM morador m
LEFT JOIN morador_apartamento ma ON m.id = ma.id_morador AND ma.activo=1
LEFT JOIN apartamento a ON ma.id_apartamento = a.id
LEFT JOIN bloco bl ON a.id_bloco = bl.id
WHERE m.id = ?

-- Mensalidades pendentes
SELECT * FROM mensalidade 
WHERE id_morador = ? AND estado='pendente'
ORDER BY vencimento ASC

-- Ocorrências abertas
SELECT * FROM ocorrencia 
WHERE id_morador = ? AND estado != 'encerrada'
ORDER BY criado_em DESC
```

### Admin Dashboard
```sql
-- KPIs
SELECT COUNT(*) FROM morador
SELECT COUNT(*) FROM administrador WHERE activo=1
SELECT COUNT(*) FROM apartamento WHERE estado='Disponivel'
SELECT COUNT(*) FROM mensalidade WHERE estado='pendente'

-- Mensalidades atrasadas
SELECT m.*, mor.nome, a.codigo
FROM mensalidade m
JOIN morador mor ON m.id_morador = mor.id
JOIN apartamento a ON m.id_apartamento = a.id
WHERE m.estado='atrasado'
ORDER BY m.vencimento ASC

-- Pagamentos pendentes de confirmação
SELECT mp.*, men.mes, men.ano, mor.nome
FROM mensalidade_pagamento mp
JOIN mensalidade men ON mp.id_mensalidade = men.id
JOIN morador mor ON men.id_morador = mor.id
WHERE mp.estado='pendente'
ORDER BY mp.data_pagamento DESC
```

## 9️⃣ ESTRUTURA DE FICHEIROS

```
web/
├── api/
│   ├── conexao.php              ← MySQL
│   ├── loginmorador.php         ← POST login
│   ├── loginfuncionario.php     ← POST login admin
│   ├── registar_morador.php     ← POST criar morador
│   ├── registar_admin.php       ← POST criar admin ⚠️
│   ├── api_morador.php          ← GET JSON morador
│   ├── api_dashboard.php        ← GET JSON admin
│   ├── pagar.php                ← POST pagamento
│   └── condominio_nz.sql        ← SCHEMA
│
├── pages/
│   ├── dashboard_morador.php    ← Home morador
│   ├── dashboard.php            ← Home admin
│   ├── meu_perfil.php
│   ├── minhas_mensalidades.php
│   ├── minhas_ocorrencias.php
│   ├── comunicacao.php
│   ├── visitas.php
│   └── areas_comuns.php
│
├── css/
│   ├── admin.css
│   ├── nosso-zimbo-admin.css    ← Sidebar + layout
│   ├── login.css
│   └── [outros]
│
├── js/
│   ├── admin_dashboard.js
│   └── [outros]
│
└── Visitante/
    └── visitante.html           ← Registo visitante
```

## 🔟 NEXT STEPS RECOMENDADOS

### 1. Segurança (CRÍTICO)
- [ ] Adicionar `if (!admin) die()` em `registar_admin.php`
- [ ] Converter `api_morador.php` para prepared statements
- [ ] Implementar CSRF tokens

### 2. Funcionalidades (EM PROGRESSO)
- [ ] Completar UI de Áreas Comuns
- [ ] Implementar Comunicação real
- [ ] Adicionar Notificações

### 3. Qualidade
- [ ] Paginação em listas
- [ ] Rate limiting em login
- [ ] Validação de email (enviar confirmação)
- [ ] Soft delete (activo=0)

---

## 💾 Ficheiros de Documentação

| Ficheiro | Tamanho | Secções |
|----------|---------|---------|
| ANALISE_ESTRUTURA_COMPLETA.md | 15KB | 10 secções (tipos, páginas, APIs, CRUDs, campos, BD, fluxos) |
| MAPA_RAPIDO_REFERENCIA.md | 12KB | Matriz acesso, páginas, endpoints, fluxos visuais, checklist |
| ERD_E_VALIDACOES.md | 14KB | Diagrama ER, validações por campo, snippets segurança |
| CHEAT_SHEET (este) | 6KB | Resumo executivo, queries principais |

---

**Data**: 2026-06-23  
**Versão**: 1.0  
**Status**: ✅ Análise Completa
