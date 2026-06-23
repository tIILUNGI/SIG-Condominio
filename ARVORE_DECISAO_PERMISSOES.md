# 🌳 ÁRVORE DE DECISÃO — QUEM PODE FAZER O QUÊ

## 1. AUTENTICAÇÃO

```
┌─────────────────────────────────────────────────────────────────────┐
│ Visitante acessa login.html                                         │
└─────────────────┬───────────────────────────────────────────────────┘
                  │
      ┌───────────┴───────────┬──────────────────┐
      │                       │                  │
      ↓                       ↓                  ↓
  Sou Morador          Sou Funcionário       Quero Registar
      │                       │                  │
      ↓                       ↓                  ↓
┌──────────────┐      ┌──────────────┐   ┌──────────────┐
│ BI + Senha   │      │ BI + Senha   │   │ Form completo│
│ /loginmorador│      │ /loginfunc   │   │ /visitante   │
└──────┬───────┘      └──────┬───────┘   └──────┬───────┘
       │                     │                  │
       ↓ OK?                 ↓ OK?              ↓
   SELECT morador        SELECT admin       INSERT morador
       │                     │                  │
   ✅ YES                 ✅ YES              ✅ Criado
       │                     │                  │
       ↓                     ↓                  ↓
  $_SESSION          $_SESSION          Redireciona
  tipo='morador'     tipo='admin'       para login
       │                     │
       ↓                     ↓
  dashboard_             dashboard.php
  morador.php               │
                            │
                 ┌──────────┴──────────────┐
                 │                        │
             👤 Morador          👨‍💼 Admin
```

## 2. OPERAÇÕES POR TIPO DE UTILIZADOR

### 🏠 MORADOR

```
┌────────────────────────────────────────────────────────────────┐
│  MORADOR (Residente Autenticado)                               │
└────────────────────────────────────────────────────────────────┘

┌─ MEU PERFIL ──────────────────────────────────────────────────┐
│ ✓ Ver dados pessoais                                         │
│ ✓ Ver apartamento atual                                      │
│ ✓ Editar telefone, email, morada                             │
│ ✗ Mudar de apartamento                                       │
│ ✗ Deletar conta                                              │
└───────────────────────────────────────────────────────────────┘

┌─ MENSALIDADES ────────────────────────────────────────────────┐
│ ✓ Listar todas as quotas                                     │
│ ✓ Ver status (pendente, pago, atrasado, dispensado)         │
│ ✓ Pagar (registar comprovante)                               │
│ ✓ Ver histórico de pagamentos                                │
│ ✗ Gerar quotas                                               │
│ ✗ Editar valor                                               │
│ ✗ Deletar quotas                                             │
└───────────────────────────────────────────────────────────────┘

┌─ OCORRÊNCIAS (Avarias/Reclamações) ────────────────────────────┐
│ ✓ Criar nova (tipo, título, descr., prioridade)              │
│ ✓ Listar todas as minhas                                      │
│ ✓ Ver status (aberta, em_análise, resolvida, encerrada)     │
│ ✓ Ver comentários do admin                                   │
│ ✗ Editar (apenas criar)                                      │
│ ✗ Deletar                                                     │
│ ✗ Atribuir a outro técnico                                   │
└───────────────────────────────────────────────────────────────┘

┌─ VISITAS (Agendamento) ────────────────────────────────────────┐
│ ✓ Agendar nova visita (nome, data, hora, BI opt)            │
│ ✓ Listar minhas visitas agendadas                            │
│ ✓ Ver status (pendente, autorizado, entrada, saída, negado) │
│ ✓ Receber código de acesso                                   │
│ ✗ Autorizar manualmente                                      │
│ ✗ Deletar agendamento                                        │
└───────────────────────────────────────────────────────────────┘

┌─ ÁREAS COMUNS (Reservas) ──────────────────────────────────────┐
│ ✓ Agendar piscina, salão, churrasqueira, campo                │
│ ✓ Ver datas disponíveis                                       │
│ ✓ Ver minhas reservas (com status)                            │
│ ✗ Confirmar reservas (apenas admin)                           │
│ ✗ Deletar reservas de outros                                  │
└───────────────────────────────────────────────────────────────┘

┌─ COMUNICAÇÃO ──────────────────────────────────────────────────┐
│ ✓ Ler avisos do condomínio                                   │
│ ✓ Receber notificações                                       │
│ ✗ Enviar avisos (apenas admin)                               │
│ ✗ Criar anúncios                                             │
└───────────────────────────────────────────────────────────────┘

┌─ DADOS SENSÍVEIS ──────────────────────────────────────────────┐
│ ✗ Ver dados de outros moradores                              │
│ ✗ Ver dados financeiros de outros                            │
│ ✗ Ver pagamentos realizados por outros                       │
└───────────────────────────────────────────────────────────────┘
```

### 👨‍💼 ADMIN (Funcionário/Administrador)

```
┌────────────────────────────────────────────────────────────────┐
│  ADMIN (Staff Autenticado)                                     │
└────────────────────────────────────────────────────────────────┘

┌─ DASHBOARD ────────────────────────────────────────────────────┐
│ ✓ Ver KPIs: total moradores, admins, apartamentos            │
│ ✓ Ver mensalidades pendentes                                 │
│ ✓ Ver receitas do mês                                        │
│ ✓ Ver gráficos (Chart.js)                                    │
│ ✓ Acesso rápido a todos os módulos                           │
└───────────────────────────────────────────────────────────────┘

┌─ MORADORES (CRUD) ────────────────────────────────────────────┐
│ ✓ Criar novo (form completo)                                 │
│ ✓ Listar todos (com filtros)                                 │
│ ✓ Ver detalhes (dados, apartamento, histórico)              │
│ ✓ Editar (dados, estado_conta, apartamento)                 │
│ ✓ Suspender (estado_conta = 'Suspenso')                      │
│ ✓ Reativar (estado_conta = 'Activo')                         │
│ ✗ Deletar (soft delete, não completo)                        │
│ ✗ Ver senhas em texto plano                                  │
└───────────────────────────────────────────────────────────────┘

┌─ FUNCIONÁRIOS (CRUD) ──────────────────────────────────────────┐
│ ✓ Criar novo funcionário (form completo)              ⚠️ BUG │
│ ✓ Listar todos                                        ⚠️ BUG │
│ ✓ Ver detalhes (dados, funcao, status login)                 │
│ ✓ Editar (dados, funcao, status)                             │
│ ✗ Deletar (não implementado)                                  │
│ ⚠️ Sem proteção de criação (qualquer um cria!) — BUG          │
└───────────────────────────────────────────────────────────────┘

┌─ APARTAMENTOS (CRUD) ──────────────────────────────────────────┐
│ ✓ Criar novo (bloco, número, andar, tipologia, estado)       │
│ ✓ Listar todos (com filtros por bloco, estado)              │
│ ✓ Ver detalhes (ocupante, tipologia, área, etc)             │
│ ✓ Editar estado (Disponivel, Ocupado, Manutenção, Reservado)│
│ ✓ Associar morador (criar morador_apartamento)              │
│ ✓ Ver histórico de ocupação (datas entrada/saída)          │
│ ✗ Deletar (hard delete não recomendado)                     │
└───────────────────────────────────────────────────────────────┘

┌─ MENSALIDADES (CRUD) ──────────────────────────────────────────┐
│ ✓ Gerar automaticamente (batch mensal)                       │
│ ✓ Gerar manualmente (morador específico)                     │
│ ✓ Listar todas (com filtros por mês, ano, estado)          │
│ ✓ Ver detalhes (valor, vencimento, pagamentos)             │
│ ✓ Editar valor (ajuste administrativo)                       │
│ ✓ Marcar como dispensada (perdão administrativo)             │
│ ✗ Deletar (apenas soft delete com estado)                    │
└───────────────────────────────────────────────────────────────┘

┌─ PAGAMENTOS (Revisão & Confirmação) ───────────────────────────┐
│ ✓ Listar todos os pagamentos realizados                      │
│ ✓ Ver pagamentos pendentes de confirmação                    │
│ ✓ Ver comprovante (file upload)                             │
│ ✓ Confirmar pagamento (estado = 'confirmado')               │
│ ✓ Rejeitar pagamento (estado = 'rejeitado')                 │
│ ✓ Adicionar notas administrativas                            │
│ ✓ Gerar relatório de receitas                               │
│ ✗ Deletar pagamentos confirmados                             │
└───────────────────────────────────────────────────────────────┘

┌─ OCORRÊNCIAS (Gestão) ────────────────────────────────────────┐
│ ✓ Listar todas (com filtros por estado, prioridade)         │
│ ✓ Ver detalhes do reportado                                  │
│ ✓ Mudar status (aberta → em_análise → resolvida)            │
│ ✓ Adicionar notas técnicas                                   │
│ ✓ Designar técnico responsável                               │
│ ✓ Registar data de resolução                                │
│ ✗ Deletar ocorrências abertas                                │
│ ✗ Esconder ocorrências                                       │
└───────────────────────────────────────────────────────────────┘

┌─ VISITAS (Controlo de Acesso) ────────────────────────────────┐
│ ✓ Listar todas as visitas agendadas                         │
│ ✓ Ver status (pendente, autorizado, entrada, saída)        │
│ ✓ Autorizar manualmente (estado = 'autorizado')            │
│ ✓ Negar acesso (estado = 'negado')                          │
│ ✓ Registar entrada (estado = 'entrada_registada')           │
│ ✓ Registar saída (estado = 'saida_registada')               │
│ ✓ Gerar código de acesso                                     │
│ ✓ Exportar lista de visitantes                              │
│ ✗ Deletar agendamentos completos                             │
└───────────────────────────────────────────────────────────────┘

┌─ ÁREAS COMUNS (Confirmação) ────────────────────────────────────┐
│ ✓ Listar todos os agendamentos                              │
│ ✓ Ver status (pendente, confirmado, cancelado)              │
│ ✓ Confirmar reserva (estado = 'confirmado')                 │
│ ✓ Cancelar reserva (estado = 'cancelado')                   │
│ ✓ Ver disponibilidade por data                              │
│ ✓ Gerar calendário de ocupação                              │
│ ✗ Deletar agendamentos                                       │
└───────────────────────────────────────────────────────────────┘

┌─ COMUNICAÇÃO (Broadcasting) ───────────────────────────────────┐
│ ✓ Criar avisos do condomínio                                │
│ ✓ Enviar notificações em massa                              │
│ ✓ Ver histórico de comunicações                             │
│ ✓ Enviar mensagens diretas a moradores                      │
│ ✗ Deletar mensagens enviadas                                 │
└───────────────────────────────────────────────────────────────┘

┌─ RELATÓRIOS ──────────────────────────────────────────────────┐
│ ✓ Relatório Mensal (receitas, despesas)                      │
│ ✓ Relatório de Inadimplência (quem deve)                    │
│ ✓ Relatório de Ocupação (apartamentos)                      │
│ ✓ Exportar para CSV/PDF                                      │
│ ✓ Gráficos de tendências                                     │
│ ✗ Editar relatórios gerados                                  │
└───────────────────────────────────────────────────────────────┘

┌─ SEGURANÇA ────────────────────────────────────────────────────┐
│ ✓ Ver logs de último login (próprio)                         │
│ ✓ Alterar senha (própria)                                    │
│ ✗ Ver senhas de outros admins                                │
│ ✗ Deletar logs                                               │
└───────────────────────────────────────────────────────────────┘
```

### 👥 COMPARAÇÃO RÁPIDA

```
┌──────────────────┬──────────┬──────────┬──────────┐
│ Ação             │ Morador  │ Admin    │ Visitante│
├──────────────────┼──────────┼──────────┼──────────┤
│ Fazer login      │ ✓        │ ✓        │ ✗        │
│ Ver próprios     │ ✓        │ ✓        │ ✗        │
│ dados            │          │          │          │
│ Ver dados        │ ✗        │ ✓        │ ✗        │
│ de outros        │          │          │          │
│ Criar conta      │ 1x       │ ✗        │ ✓ (auto) │
│ Pagar mensalidad │ ✓        │ ✗        │ ✗        │
│ Reportar avaria  │ ✓        │ ✓        │ ✗        │
│ Agendar visita   │ ✓        │ ✓        │ ✗        │
│ Autorizar visita │ ✗        │ ✓        │ ✗        │
│ Confirmar paga.  │ ✗        │ ✓        │ ✗        │
│ Gerar relatórios │ ✗        │ ✓        │ ✗        │
│ Deletar dados    │ ✗        │ ✗ (soft) │ ✗        │
└──────────────────┴──────────┴──────────┴──────────┘
```

## 3. FLUXO DE PAGAMENTO (Quem faz o quê)

```
┌──────────────────────────────────────────────────────────────────┐
│ MORADOR                                                          │
│  1. Vê mensalidades em "Minhas Mensalidades"                    │
│  2. Clica [Pagar] numa quota pendente                           │
│  3. Preenche: valor, metodo, referência (opt), comprovante (opt)│
│  4. Submit → POST pagar.php                                     │
│  5. Inserido em mensalidade_pagamento (estado='pendente')       │
│  6. Morador vê "Aguardando confirmação do admin"                │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│ ADMIN                                                            │
│  1. Vai a "Pagamentos" no dashboard                             │
│  2. Vê lista de pendentes (com comprovante)                    │
│  3. Abre comprovante (file preview)                            │
│  4. Verifica se está OK                                        │
│  5. Clica [Confirmar] ou [Rejeitar]                            │
│  6. POST api_dashboard.php?acao=confirmar_pagamento            │
│  7. UPDATE mensalidade_pagamento.estado = 'confirmado'         │
│  8. UPDATE mensalidade.estado = 'pago'                         │
│  9. UPDATE mensalidade_pagamento.confirmado_por = $id_admin    │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│ MORADOR (Notificado)                                             │
│  1. Vê quota com ✅ "PAGO"                                      │
│  2. Recebe notificação (opcional)                              │
│  3. Pode fazer download de recibo (futuro)                     │
└──────────────────────────────────────────────────────────────────┘
```

## 4. PERMISSÕES POR FUNÇÃO

```
┌──────────────────────────────────────────────────────────────┐
│ SUPER ADMIN                                                  │
├──────────────────────────────────────────────────────────────┤
│ ✓ Todas as permissões de Admin                              │
│ ✓ Configurações do sistema                                  │
│ ✓ Resetar senhas de outros admins                           │
│ ✓ Ver logs de todas as operações                            │
│ ✓ Backup/Restore BD                                         │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│ ADMINISTRADOR                                                │
├──────────────────────────────────────────────────────────────┤
│ ✓ Gestão completa de moradores                              │
│ ✓ Gestão completa de apartamentos                           │
│ ✓ Gestão completa de mensalidades                           │
│ ✓ Confirmação de pagamentos                                 │
│ ✓ Gestão de ocorrências                                     │
│ ✓ Autorização de visitas                                    │
│ ✓ Confirmação de áreas comuns                               │
│ ✓ Geração de relatórios                                     │
│ ✗ Criar outros admins (apenas Super Admin)                  │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│ RECURSOS HUMANOS                                             │
├──────────────────────────────────────────────────────────────┤
│ ✓ Criar/editar funcionários                                 │
│ ✓ Ver dados pessoais de staff                               │
│ ✓ Gerar folha de pagamento (futuro)                         │
│ ✗ Ver dados financeiros de moradores                        │
│ ✗ Confirmar pagamentos                                      │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│ SEGURANÇA                                                    │
├──────────────────────────────────────────────────────────────┤
│ ✓ Autorizar/negar visitas                                   │
│ ✓ Registar entrada/saída                                    │
│ ✓ Ver histórico de visitas                                  │
│ ✓ Gerar relatórios de acesso                                │
│ ✗ Editar dados de moradores                                 │
│ ✗ Confirmar pagamentos                                      │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│ AREA TECNICA                                                 │
├──────────────────────────────────────────────────────────────┤
│ ✓ Gerir ocorrências (avarias)                               │
│ ✓ Atualizar status de reparações                            │
│ ✓ Ver histórico de manutenção                               │
│ ✗ Editar dados financeiros                                  │
│ ✗ Autorizar visitas                                         │
└──────────────────────────────────────────────────────────────┘
```

## 5. REGRAS DE ACESSO (if-checks)

```php
// REGRA 1: Apenas Morador Autenticado
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}

// REGRA 2: Apenas Admin Autenticado
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.html?erro=acesso");
    exit;
}

// REGRA 3: Apenas Super Admin (a implementar)
if ($_SESSION['funcao'] !== 'Super Admin') {
    http_response_code(403);
    die("Acesso negado");
}

// REGRA 4: Morador só vê seus dados (Ownership Check)
$id_morador = intval($_SESSION['id']);
$stmt = $conexao->prepare(
    "SELECT * FROM mensalidade WHERE id = ? AND id_morador = ?"
);
$stmt->bind_param("ii", $id_mensalidade, $id_morador);
// Se não encontrar, negada permissão
```

---

**Última atualização**: 2026-06-23  
**Versão**: 1.0
