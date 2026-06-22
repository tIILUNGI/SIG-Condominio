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

