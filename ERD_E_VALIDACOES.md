# 📐 DIAGRAMA ER & VALIDAÇÕES DETALHADAS

## 1. Diagrama de Entidades e Relacionamentos (ERD)

### Domínios & Relacionamentos

```
╔════════════════════════════════════════════════════════════════════════════╗
║                    SISTEMA INTEGRADO DE GESTÃO (SIG)                      ║
║                     Condomínio Nosso Zimbo v2.0                           ║
╚════════════════════════════════════════════════════════════════════════════╝

┌─────────────────────────────────────────────────────────────────────────────┐
│ DOMÍNIO 1: NÚCLEO (Estrutura Física)                                        │
└─────────────────────────────────────────────────────────────────────────────┘

                          ┌────────────────┐
                          │  CONDOMINIO    │
                          ├────────────────┤
                          │ PK: id         │
                          │ FN: nome       │
                          │    morada      │
                          │    cidade      │
                          │    nif         │
                          │    mensalidade_│
                          │    base        │
                          │    multa_diaria│
                          │    iban        │
                          │    banco       │
                          └────────┬───────┘
                                   │ 1
                                   │
                                   │ N
                          ┌────────▼───────┐
                          │    BLOCO       │
                          ├────────────────┤
                          │ PK: id         │
                          │ FK: id_cond    │
                          │ UN: (cond,letra)
                          │ FN: letra      │
                          │    descricao   │
                          └────────┬───────┘
                                   │ 1
                                   │
                                   │ N
                          ┌────────▼──────────┐
                          │  APARTAMENTO      │
                          ├───────────────────┤
                          │ PK: id            │
                          │ FK: id_bloco      │
                          │ UN: (bloco,numero)│
                          │ UN: codigo        │
                          │ FN: numero        │
                          │    andar          │
                          │    tipologia      │
                          │    area_m2        │
                          │    estado         │
                          │    obs            │
                          └───────────────────┘


┌─────────────────────────────────────────────────────────────────────────────┐
│ DOMÍNIO 2: PESSOAS & ACESSOS                                               │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌────────────────────────────────┐
    │   ADMINISTRADOR                │
    ├────────────────────────────────┤
    │ PK: id                         │
    │ FK: id_condominio              │
    │ UN: email, numbi               │
    │ FN: nome                       │
    │    telefone                    │
    │    senha_hash                  │
    │    funcao (ENUM)               │
    │    iban                        │
    │    activo (TINYINT)            │
    │    nasc, nacionalidade         │
    │    morada                      │
    │    emissao_bi, validade_bi     │
    │    locale_bi                   │
    │    ultimo_login                │
    └────────────────────────────────┘
              │
              │ resolvida_por
              │
              ↓
    ┌────────────────────────────────┐
    │      OCORRENCIA                │
    └────────────────────────────────┘

    ┌────────────────┐                  ┌────────────────────────────────┐
    │   MORADOR      │◄─ N:1 ────────►  │  MORADOR_APARTAMENTO           │
    ├────────────────┤                  ├────────────────────────────────┤
    │ PK: id         │                  │ PK: id                         │
    │ UN: email      │                  │ FK: id_morador                 │
    │    numbi       │                  │    id_apartamento              │
    │ FN: nome       │                  │ FN: data_entrada               │
    │    telefone    │                  │    data_saida (NULL = activo)  │
    │    senha_hash  │                  │    activo (1 = ocupante)       │
    │    nasc        │                  │    obs                         │
    │    nacionalid. │                  └────────────────────────────────┘
    │    estado_conta│                           │
    │    ultimo_login│                           │
    └────────────────┘                           │
              │                                  │
              │ id_morador                       │ id_apartamento
              │                                  │
              ├─────────────────┬────────────────┘
              │                 │
              ↓                 ↓
       ┌──────────────┐    ┌──────────────────────┐
       │ OCORRENCIA   │    │  AQUISICAO (Contrato)│
       ├──────────────┤    ├──────────────────────┤
       │ PK: id       │    │ PK: id               │
       │ FK: id_morad │    │ FK: id_morador       │
       │    id_apart  │    │    id_apartamento    │
       │    resolvida │    │ FN: tipo (ENUM)      │
       │ UN: -        │    │    valor_total       │
       │ FN: tipo     │    │    data_contrato     │
       │    titulo    │    │    data_inicio       │
       │    descricao │    │    data_fim          │
       │    prioridad │    │    estado            │
       │    estado    │    │    notas             │
       │    data_reso │    └──────────────────────┘
       │    notas     │
       └──────────────┘


┌─────────────────────────────────────────────────────────────────────────────┐
│ DOMÍNIO 3: FINANCEIRO                                                       │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌───────────────────────────────┐
    │    MENSALIDADE (Quotas)       │
    ├───────────────────────────────┤
    │ PK: id                        │
    │ FK: id_morador, id_apartament │
    │ UN: (morador,apt,serv,mês,ano)│
    │ FN: servico (ENUM)            │
    │    mes, ano                   │
    │    valor                      │
    │    vencimento                 │
    │    estado (pendente/pago/etc) │
    └────────┬──────────────────────┘
             │ 1
             │
             │ N
    ┌────────▼────────────────────────────────────────┐
    │    MENSALIDADE_PAGAMENTO (Efectivos)           │
    ├───────────────────────────────────────────────┤
    │ PK: id                                        │
    │ FK: id_mensalidade                            │
    │    confirmado_por (admin)                     │
    │ FN: valor_pago                                │
    │    metodo (Transf/Multicaixa/Dinheiro/TPA)   │
    │    referencia                                 │
    │    comprovativo_url                           │
    │    data_pagamento                             │
    │    estado (pendente/confirmado/rejeitado)    │
    │    notas_admin                                │
    └───────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────┐
│ DOMÍNIO 4: COMUNICAÇÃO                                                      │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌────────────────┐
    │   CONVERSA     │
    ├────────────────┤
    │ PK: id         │
    │ FN: tipo       │
    │    titulo      │
    │    criado_por  │
    │    criado_em   │
    └────────┬───────┘
             │ 1
             │
             │ N
    ┌────────▼──────────────────────────┐
    │  CONVERSA_PARTICIPANTE            │
    ├───────────────────────────────────┤
    │ PK: id                            │
    │ FK: id_conversa                   │
    │ UN: (conversa, tipo_user, id_user)│
    │ FN: tipo_user (morador/admin)     │
    │    id_user (ref polimorfa)        │
    │    entrou_em, saiu_em             │
    └────────┬───────────────────────────┘
             │
             │
    ┌────────▼──────────────┐
    │   MENSAGEM (Chat)     │
    ├───────────────────────┤
    │ PK: id                │
    │ FK: id_conversa       │
    │ FN: tipo_remetente    │
    │    id_remetente       │
    │    conteudo           │
    │    anexo_url          │
    │    lida               │
    │    enviada_em         │
    └───────────────────────┘

    ┌──────────────────────────────────────┐
    │   NOTIFICACAO (Sistema)              │
    ├──────────────────────────────────────┤
    │ PK: id                               │
    │ FN: tipo_destino (morador/admin)     │
    │    id_destino (NULL = broadcast)     │
    │    titulo, mensagem                  │
    │    tipo (info/aviso/alerta/pag/ocor) │
    │    entidade_ref (morador:12,etc)    │
    │    lida                              │
    │    criado_em                         │
    └──────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────┐
│ DOMÍNIO 5: OPERAÇÕES                                                        │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────────┐
    │     VISITA (Acesso)      │
    ├──────────────────────────┤
    │ PK: id                   │
    │ FK: id_morador           │
    │    id_apartamento        │
    │ UN: codigo_acesso        │
    │ FN: nome_visitante       │
    │    numbi_visitante       │
    │    data_prevista         │
    │    hora_prevista         │
    │    estado (ENUM)         │
    │    codigo_acesso         │
    │    criado_em             │
    └──────────────────────────┘

    ┌──────────────────────────────────────┐
    │  AGENDAMENTO (Áreas Comuns)          │
    ├──────────────────────────────────────┤
    │ PK: id                               │
    │ FK: id_morador                       │
    │ FN: area_comum (Pisina/Salao/etc)   │
    │    data_evento                       │
    │    hora_inicio, hora_fim             │
    │    estado (pendente/confirmado/canc) │
    │    notas                             │
    │    criado_em                         │
    └──────────────────────────────────────┘
```

---

## 2. Validações Detalhadas por Campo

### 📝 Formulário de LOGIN MORADOR

```
Campo: numbi (Número do BI)
├─ Tipo: VARCHAR(20)
├─ Cliente:
│  ├─ HTML5: pattern="[A-Za-z0-9]{9,20}"
│  ├─ Required: ✓
│  ├─ oninvalid: "Digite o número do BI (9-20 caracteres)"
│  └─ Validação JS: /^[A-Za-z0-9]{9,20}$/
├─ Servidor:
│  ├─ Trim: trim($_POST['numbi'])
│  ├─ Check vazio: if (!$numbi)
│  └─ Query: SELECT WHERE numbi = ?
├─ BD:
│  ├─ Constraint: UNIQUE
│  └─ Index: ✓
└─ Segurança: SQL Injection: ✓ PREPARADA

Campo: senha (Password)
├─ Tipo: VARCHAR(255)
├─ Cliente:
│  ├─ HTML5: type="password"
│  ├─ minlength="6"
│  ├─ Required: ✓
│  └─ oninvalid: "A senha deve ter pelo menos 6 caracteres"
├─ Servidor:
│  ├─ Trim: NÃO (passwords sensíveis a espaços)
│  ├─ Hash: password_hash($senha, PASSWORD_DEFAULT)
│  ├─ Verify: password_verify($senha, $hash)
│  ├─ Fallback: $senha === $hash (texto plano — não recomendado)
│  └─ Proteção: rate_limiting NÃO implementado ⚠️
├─ BD:
│  ├─ Hash: bcrypt (2y$12$...)
│  └─ Max length: 255 chars ✓
└─ Segurança: ✓ Hashed, ⚠️ Sem rate limit
```

### 📝 Formulário de REGISTO MORADOR (Visitante)

```
Campo: nome
├─ Tipo: VARCHAR(120)
├─ Cliente:
│  ├─ HTML5: type="text", required
│  └─ Min length: 2 caracteres (recomendado)
├─ Servidor:
│  ├─ Trim: trim($_POST['nome'])
│  ├─ Check vazio: if (!$nome)
│  └─ Sanitize: htmlspecialchars() ⚠️ NÃO implementado
├─ BD:
│  ├─ Constraint: NOT NULL
│  └─ Collation: utf8mb4_unicode_ci ✓
└─ Validação: ✓ Básica

Campo: email
├─ Tipo: VARCHAR(120)
├─ Cliente:
│  ├─ HTML5: type="email", required
│  └─ Pattern: HTML5 validation
├─ Servidor:
│  ├─ Trim: trim($_POST['email'])
│  ├─ Check vazio: if (!$email)
│  ├─ Validação: filter_var() ⚠️ NÃO implementado
│  └─ Query: SELECT id FROM morador WHERE email = ?
├─ BD:
│  ├─ Constraint: UNIQUE
│  └─ Index: ✓
└─ Validação: ⚠️ HTML5 apenas (SQL Injection: ✓)

Campo: numbi (Número do BI)
├─ Tipo: VARCHAR(20)
├─ Cliente:
│  ├─ HTML5: pattern="[A-Za-z0-9]{9,20}"
│  └─ Required: ✓
├─ Servidor:
│  ├─ Trim: trim($_POST['numbi'])
│  ├─ Check vazio: if (!$numbi)
│  ├─ Validação regex: ⚠️ NÃO implementada
│  └─ Query: SELECT id FROM morador WHERE numbi = ?
├─ BD:
│  ├─ Constraint: UNIQUE
│  └─ Index: ✓
└─ Validação: ✓ Básica (SQL Injection: ✓)

Campo: telefone
├─ Tipo: VARCHAR(20)
├─ Cliente:
│  ├─ HTML5: type="tel"
│  └─ Required: ✓
├─ Servidor:
│  ├─ Trim: trim($_POST['telefone'])
│  └─ Check vazio: if (!$telefone)
├─ BD:
│  ├─ Constraint: NOT NULL
│  └─ Index: ✗
└─ Validação: ⚠️ Nenhuma (aceita qualquer coisa)

Campo: senha
├─ Tipo: VARCHAR(255)
├─ Cliente:
│  ├─ HTML5: type="password", required
│  └─ minlength="6"
├─ Servidor:
│  ├─ Trim: NÃO (sensível)
│  ├─ Check vazio: if (!$senha)
│  └─ Hash: password_hash($senha, PASSWORD_DEFAULT)
├─ BD:
│  ├─ Constraint: NOT NULL
│  └─ Max length: 255 ✓
└─ Validação: ✓ Hash bcrypt

Campo: data_nascimento (nasc)
├─ Tipo: DATE
├─ Cliente:
│  ├─ HTML5: type="date"
│  └─ Required: ✓
├─ Servidor:
│  ├─ Trim: trim($_POST['nasc'])
│  ├─ Check vazio: if (!$nasc)
│  ├─ Validação data: strtotime() ⚠️ NÃO implementada
│  └─ Default: if vazio → date('Y-m-d', strtotime('-20 years'))
├─ BD:
│  ├─ Type: DATE
│  └─ Range: 1900-01-01 até NOW() ⚠️ SEM CHECK
└─ Validação: ⚠️ Default automático (não ideal)

Campo: nacionalidade
├─ Tipo: VARCHAR(60)
├─ Cliente: input type="text"
├─ Servidor:
│  ├─ Trim: trim($_POST['nacionalidade'])
│  └─ Default: 'Angolana' if vazio
├─ BD: NOT NULL DEFAULT 'Angolana'
└─ Validação: ⚠️ ENUM lista fechada recomendada

Campo: morada
├─ Tipo: VARCHAR(255)
├─ Cliente: input type="text"
├─ Servidor:
│  ├─ Trim: trim($_POST['morada'])
│  └─ Default: 'Luanda' if vazio
├─ BD:
│  ├─ Type: VARCHAR(255)
│  └─ NOT NULL DEFAULT 'Luanda'
└─ Validação: ⚠️ Nenhuma

Campo: emissao_bi
├─ Tipo: DATE
├─ Cliente: input type="date"
├─ Servidor:
│  ├─ Trim: trim($_POST['emissao'])
│  └─ Default: date('Y-m-d') if vazio
├─ BD: DATE DEFAULT CURRENT_DATE
└─ Validação: ⚠️ Sem validação (pode ser futura?)

Campo: validade_bi
├─ Tipo: DATE
├─ Cliente: input type="date"
├─ Servidor:
│  ├─ Trim: trim($_POST['validade'])
│  └─ Default: date('Y-m-d', strtotime('+5 years')) if vazio
├─ BD: DATE
└─ Validação: ⚠️ Sem validação (pode estar expirada?)

Campo: locale_bi
├─ Tipo: VARCHAR(80)
├─ Cliente: input type="text"
├─ Servidor:
│  ├─ Trim: trim($_POST['locale'])
│  └─ Default: 'Luanda' if vazio
├─ BD: VARCHAR(80) NOT NULL DEFAULT 'Luanda'
└─ Validação: ⚠️ Nenhuma
```

### 💰 Formulário de PAGAMENTO

```
Campo: id_mensalidade
├─ Tipo: INT UNSIGNED
├─ Cliente: SELECT dropdown (populado via JS/API)
├─ Servidor:
│  ├─ Type cast: intval($_POST['id_mensalidade'])
│  ├─ Check: SELECT * FROM mensalidade WHERE id = ? AND id_morador = ?
│  └─ Validação: Pertence ao morador logado ✓
├─ BD:
│  ├─ FK constraint: ✓
│  └─ NOT NULL
└─ Validação: ✓ Validação de ownership

Campo: valor_pago
├─ Tipo: DECIMAL(12,2)
├─ Cliente:
│  ├─ HTML5: input type="number", step="0.01"
│  └─ Min: 0, Max: 999999999.99
├─ Servidor:
│  ├─ Type: floatval($_POST['valor_pago'])
│  ├─ Check: if ($valor <= 0) { error }
│  └─ Validação: Não deve exceder valor_mensalidade ⚠️ NÃO implementada
├─ BD:
│  ├─ Type: DECIMAL(12,2)
│  └─ NOT NULL
└─ Validação: ⚠️ Parcial

Campo: metodo
├─ Tipo: ENUM('Transferência','Multicaixa','Dinheiro','TPA','Outro')
├─ Cliente:
│  ├─ HTML5: SELECT com 5 opções
│  └─ Required: ✓
├─ Servidor:
│  ├─ Value: $_POST['metodo']
│  └─ Check: if (!in_array($metodo, $allowed)) { error }
├─ BD:
│  ├─ Type: ENUM
│  └─ Constraint: CHECK
└─ Validação: ✓ ENUM lista fechada

Campo: referencia
├─ Tipo: VARCHAR(80)
├─ Cliente: input type="text", optional
├─ Servidor:
│  ├─ Trim: trim($_POST['referencia'])
│  └─ Max length: 80 chars ✓
├─ BD: VARCHAR(80) NULL
└─ Validação: ✓ Opcional, length check

Campo: comprovativo_url
├─ Tipo: VARCHAR(255)
├─ Cliente: input type="file", optional
├─ Servidor:
│  ├─ Validação: ⚠️ NÃO implementada
│  ├─ Tipos aceitos: ⚠️ SEM WHITELIST
│  ├─ Tamanho: ⚠️ SEM LIMITE
│  └─ Path: /uploads/proofs/{ano}/{mes}/{id}.{ext}
├─ BD: VARCHAR(255) NULL
└─ Validação: 🔴 CRÍTICA: Sem validação de tipo/tamanho/path
```

### 📋 Formulário de NOVA OCORRÊNCIA

```
Campo: tipo
├─ Tipo: ENUM('Avaria','Reclamacao','Sugestao','Outro')
├─ Cliente:
│  ├─ SELECT com 4 opções
│  └─ Required: ✓
├─ Servidor:
│  ├─ Value: $_POST['tipo']
│  └─ Validação: in_array() check ✓
├─ BD:
│  ├─ ENUM constraint: ✓
│  └─ Default: 'Outro'
└─ Validação: ✓ ENUM

Campo: titulo
├─ Tipo: VARCHAR(120)
├─ Cliente:
│  ├─ input type="text"
│  ├─ Required: ✓
│  └─ maxlength="120"
├─ Servidor:
│  ├─ Trim: trim($_POST['titulo'])
│  ├─ Sanitize: htmlspecialchars() ⚠️ NÃO
│  └─ Check vazio: if (!$titulo) { error }
├─ BD: VARCHAR(120) NOT NULL
└─ Validação: ⚠️ Básica

Campo: descricao
├─ Tipo: TEXT
├─ Cliente:
│  ├─ textarea
│  ├─ Required: ✓
│  └─ minlength="10"
├─ Servidor:
│  ├─ Trim: trim($_POST['descricao'])
│  ├─ Check minimo: if (strlen($desc) < 10) { error } ⚠️ NÃO
│  └─ Sanitize: htmlspecialchars() ⚠️ NÃO
├─ BD: TEXT NOT NULL
└─ Validação: ⚠️ Nenhuma

Campo: prioridade
├─ Tipo: ENUM('Baixa','Media','Alta','Urgente')
├─ Cliente:
│  ├─ SELECT com 4 opções
│  └─ Required: ✓
├─ Servidor:
│  ├─ Value: $_POST['prioridade']
│  └─ Validação: in_array() ✓
├─ BD:
│  ├─ ENUM constraint: ✓
│  └─ Default: 'Media'
└─ Validação: ✓ ENUM
```

---

## 3. Resumo de Validações por Severidade

### 🔴 CRÍTICAS (SQL Injection / RCE)

| Campo | Ficheiro | Problema | Solução |
|-------|----------|----------|---------|
| Múltiplos | `api_morador.php:78-86` | Interpolação direta em SQL | Usar prepared statements |
| comprovativo_url | `pagar.php` | Sem validação de upload | Whitelist tipos, scan malware |
| numbi | `registar_admin.php` | Sem autenticação | Verificar $_SESSION['tipo']==='admin' |
| todos | Múltiplos | Sem sanitização HTML | htmlspecialchars() + strip_tags() |

### 🟡 IMPORTANTES (OWASP Top 10)

| Campo | Ficheiro | Problema | Solução |
|-------|----------|----------|---------|
| senha | `loginmorador.php` | Sem rate limiting | Implementar throttling (ex: max 5 tentativas/min) |
| CSRF | Todos formulários | Sem tokens CSRF | Gerar token único por sessão |
| email | `registar_morador.php` | Sem verificação | Enviar email de confirmação |
| valor_pago | `pagar.php` | Sem validação máximo | Verificar contra valor_mensalidade |
| todos | Múltiplos | Sem logs de auditoria | INSERT audit_log para Create/Update/Delete |

### 🟢 MELHORIAS (Best Practice)

| Campo | Ficheiro | Problema | Solução |
|-------|----------|----------|---------|
| nasc, emissao, validade | Múltiplos | Datas com defaults automáticos | Deixar usuário preencher |
| estado_conta | Todas | Sem soft delete | Usar activo=0 em vez de DELETE |
| paginação | `api_dashboard.php` | SELECT * sem LIMIT | Implementar LIMIT 100 + offset |
| notificações | Sistema | Tabelas criadas mas não usadas | Integrar notificacao em CRUD |

---

## 4. Padrões de Validação (Code Snippets)

### ✅ SEGURO - Prepared Statement

```php
// ✓ BOM: Prepared statement com bind_param
$stmt = $conexao->prepare("SELECT id FROM morador WHERE numbi = ? LIMIT 1");
$stmt->bind_param("s", $numbi);
$stmt->execute();
$res = $stmt->get_result();
```

### ❌ INSEGURO - SQL Injection

```php
// ✗ RUIM: Interpolação direta
$sql = "SELECT COALESCE(SUM(valor),0) FROM mensalidade 
        WHERE id_morador=$id_morador AND estado='pendente'";
$total = mysqli_fetch_row(mysqli_query($conexao, $sql))[0];
```

### ✅ SEGURO - Sanitização HTML

```php
// ✓ BOM: Escapar para HTML
echo htmlspecialchars($morador_nome, ENT_QUOTES, 'UTF-8');
```

### ❌ INSEGURO - XSS

```php
// ✗ RUIM: Sem sanitização
echo "Olá, " . $_POST['nome'];
```

### ✅ SEGURO - Hash de Senha

```php
// ✓ BOM: password_hash + password_verify
$hash = password_hash($senha, PASSWORD_DEFAULT);
if (password_verify($input_senha, $hash)) {
    // Login OK
}
```

### ❌ INSEGURO - Texto Plano

```php
// ✗ RUIM: Comparação direta
if ($senha === $morador['senha_hash']) {
    // Senha em texto plano!
}
```

### ✅ SEGURO - Validação ENUM

```php
// ✓ BOM: Lista branca
$funcoes_validas = ['Administrador', 'RH', 'Seguranca', 'Area Tecnica'];
if (!in_array($funcao, $funcoes_validas)) {
    die("Função inválida");
}
```

### ❌ INSEGURO - Sem Validação

```php
// ✗ RUIM: Aceita qualquer valor
INSERT INTO administrador (..., funcao) VALUES (..., '$funcao')
```

---

## 5. Checklist de Validação para Novos Campos

Quando adicionar um novo campo a um formulário:

```
□ Cliente (HTML5)
  □ type="..." correto (text, email, date, number, etc)
  □ required / optional definido
  □ pattern / min / max / minlength definidos
  □ placeholder ou label claro
  □ Mensagens de validação HTML5

□ Servidor (PHP)
  □ trim() aplicado (exceto passwords)
  □ Verificar se campo vazio
  □ Type cast correto (intval, floatval, etc)
  □ Sanitização (htmlspecialchars, strip_tags)
  □ Validação lógica (ex: data não no futuro)
  □ Check de duplicatas (se UNIQUE)
  □ Check de ownership (se FK a utilizador)

□ BD (SQL)
  □ Type correto (VARCHAR, INT, DATE, ENUM)
  □ Constraint (NOT NULL, UNIQUE, DEFAULT, CHECK)
  □ Index (PRIMARY KEY, UNIQUE, FOREIGN KEY)
  □ Collation utf8mb4_unicode_ci
  □ Max length de VARCHAR apropriado

□ Segurança
  □ Não é SQL Injectable (prepared statement)
  □ Não é XSS (htmlspecialchars output)
  □ Não é CSRF (CSRF token validado)
  □ Rate limiting (se autenticação)
  □ Soft delete (se delete)
  □ Audit log (se Create/Update/Delete)
```

---

**Última atualização**: 2026-06-23  
**Versão**: 1.0
