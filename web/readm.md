🏢 Condomínio Nosso Zimbo — Sistema de Gestão
📋 Índice
Sobre o Sistema

Tecnologias Utilizadas

Requisitos

Instalação

Estrutura do Projeto

Banco de Dados

Credenciais de Acesso

Funcionalidades

Guia de Uso

Solucionar Problemas

Desenvolvedor

📖 Sobre o Sistema
O Condomínio Nosso Zimbo é um sistema completo de gestão condominial desenvolvido para facilitar a administração de condomínios, permitindo:

Moradores: Acessar informações, pagar mensalidades, reportar ocorrências, comunicar-se com a administração, reservar áreas comuns e gerir visitas.

Administradores: Gerir moradores, apartamentos, mensalidades, ocorrências, visitas, áreas comuns e gerar relatórios financeiros.

🛠️ Tecnologias Utilizadas
Tecnologia	Versão	Descrição
PHP	8.0+	Backend e lógica de negócio
MySQL	5.7+ / 8.0+	Banco de dados relacional
HTML5	-	Estrutura das páginas
CSS3	-	Estilização e layout responsivo
JavaScript	ES6+	Interatividade e funcionalidades client-side
Chart.js	4.4.1	Gráficos e visualização de dados
Font Awesome	6.5.0	Ícones vetoriais
XAMPP	2.4.58+	Servidor local (Apache + MySQL + PHP)
⚙️ Requisitos
Hardware
Processador: 1 GHz ou superior

Memória RAM: 2 GB mínimo (4 GB recomendado)

Espaço em disco: 500 MB

Software
Sistema Operacional: Windows 10/11, Linux ou macOS

Servidor Web: Apache 2.4+

PHP: 7.4 ou superior (recomendado 8.0+)

MySQL: 5.7 ou superior (recomendado 8.0+)

Navegador: Chrome, Firefox, Edge ou Safari (últimas versões)

📥 Instalação
Passo 1: Baixar o Projeto
bash
# Clone o repositório ou baixe o ZIP
git clone https://github.com/seu-usuario/condominio-nosso-zimbo.git
# Ou extraia o arquivo ZIP na pasta desejada
Passo 2: Configurar o Servidor
Opção A: Usando XAMPP (Recomendado)
Baixe e instale o XAMPP em apachefriends.org

Copie a pasta do projeto para C:\xampp\htdocs\condominio\

Inicie o XAMPP Control Panel

Ative o Apache e o MySQL

Opção B: Usando PHP Built-in Server
bash
# Navegue até a pasta do projeto
cd C:\Users\us\Downloads\condominio\web
# Inicie o servidor
php -S localhost:8000
Passo 3: Configurar o Banco de Dados
Acesse o phpMyAdmin: http://localhost/phpmyadmin

Clique em "Novo" e crie o banco condominio_nz

Vá para a aba "Importar"

Selecione o arquivo database/condominio_nz.sql

Clique em "Executar"

Ou via MySQL Workbench:
sql
-- Execute o script SQL completo
SOURCE C:/caminho/para/condominio_nz.sql;
Passo 4: Configurar a Conexão
Edite o arquivo api/conexao.php:

php
define('DB_HOST',   'localhost');
define('DB_USER',   'root');
define('DB_PASS',   '');  // Sua senha do MySQL
define('DB_NAME',   'condominio_nz');
📁 Estrutura do Projeto
text
condominio/
├── web/
│   ├── api/
│   │   ├── conexao.php              # Conexão com o banco de dados
│   │   ├── loginmorador.php         # Login de moradores
│   │   ├── loginfuncionario.php     # Login de administradores
│   │   ├── logout.php               # Logout do sistema
│   │   └── ...
│   ├── pages/
│   │   ├── dashboard.php            # Dashboard do administrador
│   │   ├── dashboard_morador.php    # Dashboard do morador
│   │   ├── minhas_ocorrencias.php   # Gestão de ocorrências
│   │   ├── minhas_mensalidades.php  # Gestão de mensalidades
│   │   ├── comunicacao.php          # Central de comunicação
│   │   ├── areas_comuns.php         # Reserva de áreas comuns
│   │   ├── visitas.php              # Gestão de visitas
│   │   └── meu_perfil.php           # Perfil do usuário
│   ├── Css/
│   │   └── nosso-zimbo-admin.css    # Estilos principais
│   ├── login.html                   # Página de login
│   └── index.html                   # Página inicial
├── database/
│   └── condominio_nz.sql           # Script do banco de dados
└── README.md                       # Este arquivo
🗄️ Banco de Dados
Diagrama de Entidades
text
condominio (1) ──┬── bloco (N) ──┬── apartamento (N) ──┬── morador_apartamento (N)
                 │                │                      └── aquisicao (N)
                 │                └── mensalidade (N) ──┬── mensalidade_pagamento (N)
                 │                                      └── ...
                 └── administrador (N)
Tabelas Principais
Tabela	Descrição
condominio	Dados gerais do condomínio
bloco	Blocos/torres do condomínio
apartamento	Apartamentos/unidades
morador	Moradores cadastrados
administrador	Funcionários e administradores
morador_apartamento	Associação morador ↔ apartamento
aquisicao	Contratos de compra/arrendamento
mensalidade	Quotas mensais geradas
mensalidade_pagamento	Pagamentos realizados
ocorrencia	Ocorrências/avarias/reclamações
conversa	Conversas e canais de comunicação
mensagem	Mensagens trocadas
visita	Visitas agendadas
agendamento	Reserva de áreas comuns
notificacao	Notificações do sistema
🔑 Credenciais de Acesso
👤 Morador
Campo	Valor
BI (Login)	123456789
Senha	Admin@2026
Email	teste@condominio.com
Apartamento	A-101
👨‍💼 Administrador
Campo	Valor
BI (Login)	000000000
Senha	Admin@2026
Email	admin@nossozimbo.ao
Função	Super Admin
📊 Dados de Teste
O sistema já vem com dados de teste:

3 Blocos: A, B, C

6 Apartamentos: A-101, A-102, A-201, B-101, B-102, C-101

1 Administrador: Super Admin

1 Morador: Morador Teste

🚀 Funcionalidades
Para Moradores
Funcionalidade	Descrição	Status
Dashboard	Visão geral com estatísticas e informações	✅
Ocorrências	Criar, visualizar e acompanhar ocorrências	✅
Mensalidades	Consultar histórico e status de pagamentos	✅
Comunicação	Receber avisos e mensagens do condomínio	✅
Áreas Comuns	Reservar piscina, salão de festas, etc.	🚧
Visitas	Registrar e acompanhar visitantes	🚧
Perfil	Atualizar dados pessoais	🚧
Para Administradores
Funcionalidade	Descrição	Status
Dashboard	Visão completa da gestão	✅
Cadastro de Moradores	Registrar novos moradores	✅
Cadastro de Funcionários	Gerir administradores	✅
Gestão de Casas	Adicionar/editar apartamentos	✅
Mensalidades	Gerir quotas e pagamentos	✅
Ocorrências	Visualizar e responder	✅
Relatórios	Gerar relatórios financeiros	✅
📖 Guia de Uso
1. Acessar o Sistema
text
http://localhost/condominio/login.html
2. Fazer Login
Como Morador:

Selecione a aba "Acesso de Moradores"

Digite o BI: 123456789

Digite a senha: Admin@2026

Clique em "Entrar"

Como Administrador:

Clique em "Sou Funcionário →"

Digite o BI: 000000000

Digite a senha: Admin@2026

Clique em "Entrar"

3. Navegar pelo Sistema
Sidebar: Menu lateral com todas as funcionalidades

Dashboard: Visão geral e estatísticas

Cards: Clique nos cards para acessar cada funcionalidade

Topbar: Relógio e botão de sair

4. Criar uma Ocorrência
Acesse "Ocorrências" no menu

Preencha o formulário:

Título

Descrição

Tipo (Avaria/Reclamação/Sugestão)

Prioridade (Baixa/Média/Alta/Urgente)

Clique em "Criar Ocorrência"

5. Verificar Mensalidades
Acesse "Mensalidades" no menu

Visualize o histórico de pagamentos

Veja o resumo financeiro (pendente/pago)

🐛 Solucionar Problemas
Erro: "Access denied for user 'root'@'localhost'"
Solução:

sql
-- No MySQL Workbench
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
Erro: "Unknown database 'condominio_nz'"
Solução:

sql
CREATE DATABASE condominio_nz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
Erro: "Call to undefined method mysqli_stmt::fetch_assoc()"
Solução:

php
// Errado:
$stmt->fetch_assoc();

// Correto:
$result = $stmt->get_result();
$data = $result->fetch_assoc();
Erro: "Account is locked"
Solução:

sql
ALTER USER 'root'@'localhost' ACCOUNT UNLOCK;
FLUSH PRIVILEGES;
Erro: "Plugin caching_sha2_password could not be loaded"
Solução:
Edite C:\xampp\mysql\bin\my.ini e adicione:

ini
[mysqld]
default_authentication_plugin=mysql_native_password
🔒 Segurança
Recomendações
Alterar senhas padrão após primeira instalação

Usar HTTPS em produção

Fazer backup regular do banco de dados

Validar entradas do usuário

Usar password_hash() para senhas

Senhas em Produção
php
// Nunca use senhas em texto plano!
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Verificar senha
if (password_verify($senha, $senha_hash)) {
    // Login válido
}
👨‍💻 Desenvolvedor
Nome: [Seu Nome]
Email: [seu.email@exemplo.com]
Website: [seu-site.com]
GitHub: [github.com/seu-usuario]

📝 Licença
Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

📞 Suporte
Para suporte, dúvidas ou sugestões:

Email: [seu.email@exemplo.com]

WhatsApp: [+244 900 000 000]

GitHub Issues: [github.com/seu-usuario/condominio/issues]

🔄 Changelog
Versão 2.0 (2026-06-19)
✅ Dashboard do morador com layout similar ao admin

✅ Páginas de comunicação, áreas comuns, visitas e perfil

✅ Correção do login de administrador

✅ Correção do método fetch_assoc()

✅ Melhorias na interface e responsividade

Versão 1.0 (2026-06-15)
✅ Sistema inicial com login de moradores e administradores

✅ Gestão de ocorrências e mensalidades

✅ Relatórios financeiros

🏆 Condomínio Nosso Zimbo — Gestão Inteligente para um Condomínio Melhor!