<<<<<<< HEAD
# Condomínio Nosso Zimbo — Sistema de Gestão (SIG)

> Baseado no código existente em `web/` (PHP + MySQL) e na base `condominio_nz`.

## 1) O que o sistema faz

### Portal do Morador
- Login de moradores por **BI** + senha.
- Acompanhar **mensalidades** (pendente/pago/atrasado) e pagar.
- Ver **ocorrências** (manutenções/reclamações/sugestões).
- Acesso a páginas de **comunicação**, **áreas comuns**, **visitas** e **perfil** (dependendo do que já está implementado/estilizado).

### Painel Admin
- Login de administradores/funcionários.
- Criar/gerir **moradores**.
- Criar/gerir **apartamentos** e **blocos**.
- Atribuir apartamento a um morador e marcar apartamento como ocupado.
- Gerar mensalidades automaticamente ao atribuir.
- Confirmar pagamentos e acompanhar receitas.

## 2) Tecnologias
- **PHP**: endpoints em `web/api/*.php`
- **MySQL**: base `condominio_nz`
- **HTML/CSS/JS**: páginas em `web/pages/*` e estilos em `web/css/*`

## 3) Estrutura do projeto (o que existe)
- `web/`
  - `api/`
    - `conexao.php` — conexão com a base
    - `loginmorador.php` — autenticação do morador
    - `loginfuncionario.php` — autenticação do admin
    - `registar_morador.php` — registo (quando usado via formulário/fluxo antigo)
    - `api_moradores.php` — API admin (CRUD de moradores)
    - `api_atribuir_casa.php` — atribuir apartamento a morador (JSON)
    - `api_casas.php`, `api_listar_casas.php` — apartamentos (admin)
    - `pagar.php` — registar pagamento da mensalidade
    - (outros endpoints para dashboard/ocorrências/visitas/áreas comuns, etc.)
  - `pages/`
    - `dashboard_morador.php`, `minhas_mensalidades.php`, `pagar_mensalidade.php`, etc.
  - `css/` (ex.: `nosso-zimbo-admin.css`, `morador.css`, etc.)

## 4) Banco de Dados

### Script SQL
- Arquivo: `web/api/condominio_nz.sql`

### Tabelas principais (conforme o SQL)
- `condominio`, `bloco`, `apartamento`
- `morador`, `administrador`
- `morador_apartamento` (histórico/ocupação)
- `mensalidade`, `mensalidade_pagamento`
- `ocorrencia`
- `visita`, `agendamento`
- (e tabelas auxiliares como `conversa`, `mensagem`, `notificacao`, etc.)

### Estados importantes (usados no código)
- `apartamento.estado`: **'Disponivel' | 'Ocupado' | 'Manutencao' | 'Reservado'**
- `morador.estado_conta`: **'Activo' | 'Suspenso' | 'Inactivo'**
- `mensalidade.estado`: **'pendente' | 'pago' | 'atrasado' | ...**

## 5) Fluxos principais (como funciona)

### 5.1) Admin cria morador e atribui apartamento
Há dois fluxos admin que existem no código:

1) **`registar_morador.php`** (registo “antigo/forma”)
- Insere `morador`
- (no seu código atual, a ocupação depende de como o formulário chama/encaminha)

2) **`api_moradores.php`** (API admin)
- `acao=adicionar`:
  - cria `morador`
  - se `id_apartamento > 0`:
    - valida `apartamento.estado='Disponivel'`
    - cria `morador_apartamento.activo=1`
    - atualiza `apartamento.estado='Ocupado'`
    - gera 12 mensalidades futuras

3) **`api_atribuir_casa.php`** (atribuição via JSON)
- valida permissão admin
- valida `apartamento.estado='Disponivel'`
- desativa associação anterior do morador (activos)
- cria nova associação `morador_apartamento.activo=1`
- marca apartamento como `Ocupado`
- gera mensalidades se ainda não existirem

> Importante: o login do morador exige `morador.estado_conta='Activo'`. O sistema já foi ajustado para garantir esse valor ao criar morador nesses endpoints.

### 5.2) Morador paga uma mensalidade
- Página: `web/pages/pagar_mensalidade.php`
  - encaminha para `web/api/pagar.php`
- Endpoint: `web/api/pagar.php`
  - lê `id` da mensalidade
  - `UPDATE mensalidade SET estado='pago' ...`
  - insere em `mensalidade_pagamento`
- Resultado esperado:
  - `minhas_mensalidades.php` muda a visualização (porque ele soma por `estado`)

## 6) Requisitos para rodar localmente (XAMPP)
1. Copie o projeto para `C:\xampp\htdocs\...`.
2. Inicie **Apache** e **MySQL**.
3. Importe `web/api/condominio_nz.sql` para criar o banco `condominio_nz`.
4. Verifique `web/api/conexao.php`:
   - `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.

## 7) Segurança (nota importante)
- Senhas devem ser sempre armazenadas em **hash** via `password_hash()`.
- Login deve sempre usar `password_verify()` (sem fallback para texto plano).

## 8) Teste rápido (recomendado)
1. Criar blocos e apartamentos no admin.
2. Criar morador e atribuir apartamento.
3. Verificar no admin:
   - apartamento fica **Ocupado**
   - morador aparece como associado
4. Entrar como morador e pagar uma mensalidade.
5. Voltar em “minhas mensalidades” e confirmar que o estado mudou para **pago**.

=======
# 🏛️ Condomínio Nosso Zimbo — Sistema de Gestão

## Descrição do Projeto

Sistema web completo de gestão de condomínio com:
- ✅ Portal de autenticação (Admin e Moradores)
- ✅ Dashboard administrativo com KPIs e relatórios
- ✅ Gestão de moradores, apartamentos e mensalidades
- ✅ Controle de pagamentos e visitas
- ✅ Reserva de áreas comuns
- ✅ API REST para integração

---

## 📋 Requisitos

- **PHP** 7.4+
- **MySQL** 5.7+
- **XAMPP** (ou servidor local equivalente)
- **Navegador** moderno (Chrome, Firefox, Edge)

---

## 🚀 Instalação

### 1️⃣ Clonar/Baixar o Projeto
```bash
cd c:\xampp\htdocs
# Projeto já em: SIG-Condominio/web/
```

### 2️⃣ Criar a Base de Dados
```bash
# Abrir phpMyAdmin (http://localhost/phpmyadmin)
# OU executar no terminal MySQL:
mysql -u root -p < condominio_nz.sql
```

### 3️⃣ Popular com Dados de Teste
```bash
# Executar o script de dados de teste
mysql -u root -p condominio_nz < api/dados_teste.sql
```

### 4️⃣ Verificar Permissões
```bash
# Assegurar que pasta 'web/' é acessível
# URL: http://localhost/SIG-Condominio/web/
```

---

## 📝 Dados de Teste

### 👤 Login Admin
- **BI:** `000123456LA001`
- **Senha:** `admin123`
- **Nome:** João Silva (Super Admin)
- **Acesso:** http://localhost/SIG-Condominio/web/login.html

### 👤 Login Morador
- **BI:** `000111222LA010`
- **Senha:** `morador123`
- **Nome:** Francisco Neves
- **Apartamento:** A-102

### 👤 Outro Morador
- **BI:** `000222333LA011`
- **Senha:** `morador123`
- **Nome:** Lurdes Gomes
- **Apartamento:** A-202

---

## 📚 Estrutura do Projeto

```
web/
├── index.html                    # Página inicial
├── login.html                    # Formulário de login
├── api/                          # Endpoints PHP
│   ├── conexao.php              # Configuração de BD
│   ├── loginmorador.php         # Login de moradores
│   ├── loginfuncionario.php     # Login de admin
│   ├── registar_morador.php     # Registo público
│   ├── registar_admin.php       # Registo de admin
│   ├── api_dashboard.php        # API de dados
│   ├── logout.php               # Encerrar sessão
│   └── condominio_nz.sql        # Schema do BD
├── js/
│   ├── login.js                 # Script de validação login
│   ├── admin_dashboard.js       # Dashboard admin
│   └── index.js                 # Scripts gerais
├── css/
│   ├── login.css                # Estilo login
│   ├── admin.css                # Estilo dashboard admin
│   └── ... (outros estilos)
├── pages/
│   ├── dashboard.php            # Painel admin (proteção: sessão)
│   ├── dashboard_morador.php    # Painel morador (proteção: sessão)
│   └── ... (outras páginas)
└── Visitante/
    └── visitante.php           # Formulário de registo
```

---

## 🔐 Segurança Implementada

### ✅ Autenticação
- Hash de senha com `password_hash()` (bcrypt)
- Fallback para senhas em texto plano (legacy)
- Proteção de sessão em páginas admin

### ✅ Validação
- Validação de email em cliente e servidor
- Verificação de força de senha (mín. 6 caracteres)
- BI com padrão alfanumérico (9-20 caracteres)

### ✅ Proteção
- Redirecionamento automático para login se não autenticado
- Escapagem HTML em outputs (`htmlspecialchars`)
- Prepared statements em queries (proteção SQL injection)

---

## 🎯 Funcionalidades

### 👨‍💼 Admin
- Dashboard com KPIs
- Gestão de moradores (CRUD)
- Gestão de apartamentos (CRUD)
- Gestão de funcionários
- Confirmação de pagamentos
- Relatórios financeiros
- Agendamento de visitas e áreas comuns

### 👨‍👩‍👧 Morador
- Dashboard pessoal
- Visualizar apartamento
- Ver mensalidades
- Submeter pagamentos
- Reportar ocorrências
- Agendar visitas
- Reservar áreas comuns

---

## 🔧 Melhorias Recentes (2026-06-23)

### ✅ Corrigidas
1. **Login.js** — Agora faz submit real dos formulários
2. **loginfuncionario.php** — Correção de tabela (admin instead of funcionarios)
3. **loginmorador.php** — Refatorado com comentários estruturados
4. **conexao.php** — Melhorado com documentação
5. **Todos arquivos API** — Adicionados comentários em português

### ✅ Criadas
1. **admin_dashboard.js** — Novo script para dashboard com chamadas reais à API
2. **dados_teste.sql** — Script para popular BD com dados de teste
3. **README.md** — Esta documentação

---

## 📊 Base de Dados

### Principais Tabelas
- `condominio` — Dados do condomínio
- `administrador` — Funcionários e admins
- `morador` — Residentes
- `apartamento` — Frações do condomínio
- `bloco` — Blocos/torres
- `mensalidade` — Quotas mensais
- `mensalidade_pagamento` — Histórico de pagamentos
- `morador_apartamento` — Associação com histórico

---

## 🐛 Troubleshooting

### ❌ "Erro de ligação à base de dados"
```bash
# Verificar se MySQL está running
# Verificar credenciais em: api/conexao.php
# Verificar se base 'condominio_nz' existe
```

### ❌ Login não funciona
```bash
# Verificar se dados de teste foram importados
mysql -u root -p -e "SELECT COUNT(*) FROM condominio_nz.administrador;"
# Se vazio, executar: dados_teste.sql
```

### ❌ Dashboard não carrega dados
```bash
# Verificar console do navegador (F12 → Console)
# Verificar php_errors.log do XAMPP
# Testar API diretamente: http://localhost/SIG-Condominio/web/api/api_dashboard.php?acao=resumo
```

---

## 📞 Suporte

Para problemas técnicos:
1. Verifique os logs em `c:\xampp\apache\logs\error.log`
2. Abra console do navegador (F12)
3. Teste endpoints de API diretamente

---

## 📄 Licença

Projeto interno — Condomínio Nosso Zimbo (2026)

---

## 🎓 Notas para Desenvolvimento

### Convenções Usadas
- **Comentários em Português** — Facilita manutenção local
- **Prepared Statements** — Todas queries protegidas
- **Session-based Auth** — Não usa JWT/tokens
- **Timestamps em UTC+01:00** — Hora de Angola

### Próximas Melhorias Sugeridas
- [ ] Implementar 2FA (two-factor authentication)
- [ ] Adicionar logs de auditoria
- [ ] Sistema de notificações email
- [ ] Backup automático da BD
- [ ] API REST com tokens JWT
- [ ] Progressive Web App (PWA)

---

**Última atualização:** 23 de junho de 2026
**Status:** ✅ Sistema Funcional
>>>>>>> 49edb5e (Ajustes baiscos)
