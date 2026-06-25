<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = $_SESSION['id'];
$morador_nome = $_SESSION['nome'];
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comunicação - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
    <script src="../js/theme-manager.js"></script>
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 400px;
            background: var(--dark3);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .msg {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        .msg.sent {
            align-self: flex-end;
            background: var(--gold);
            color: #000;
            border-bottom-right-radius: 2px;
        }
        .msg.received {
            align-self: flex-start;
            background: var(--dark4);
            color: var(--text);
            border-bottom-left-radius: 2px;
            border: 1px solid var(--border);
        }
        .chat-input-area {
            padding: 15px;
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
        }
        .chat-input {
            flex: 1;
            background: var(--dark3);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px 15px;
            border-radius: 8px;
            outline: none;
        }
        .btn-send {
            background: var(--gold);
            color: #000;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .comunicado-card {
            background: var(--dark4);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--gold);
        }
        .comunicado-card.urgente { border-left-color: var(--danger); }
        .comunicado-card.manutencao { border-left-color: #3498db; }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Comunicação</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu Principal</p>
        <button class="nav-item" onclick="window.location.href='dashboard_morador.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item" onclick="window.location.href='minhas_ocorrencias.php'">
            <i class="fa-solid fa-exclamation-triangle"></i><span>Ocorrências</span>
        </button>
        <button class="nav-item" onclick="window.location.href='minhas_mensalidades.php'">
            <i class="fa-solid fa-credit-card"></i><span>Mensalidades</span>
        </button>
        <button class="nav-item active">
            <i class="fa-solid fa-comments"></i><span>Comunicação</span>
        </button>
        <button class="nav-item" onclick="window.location.href='visitas.php'">
            <i class="fa-solid fa-user-plus"></i><span>Visitas</span>
        </button>
        <p class="nav-section">Configurações</p>
        <button class="nav-item" onclick="window.location.href='meu_perfil.php'">
            <i class="fa-solid fa-user"></i><span>Meu Perfil</span>
        </button>
    </nav>
    <div class="sidebar-footer">
        <div class="avatar-admin"><?php echo strtoupper(substr($morador_nome, 0, 2)); ?></div>
        <div style="flex:1;">
            <p class="af-name"><?php echo htmlspecialchars($morador_nome); ?></p>
            <p class="af-role">Morador</p>
        </div>
        <a href="../api/logout.php" title="Sair" style="color:var(--text-muted); font-size:1rem;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo — Comunicação</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active">
        <div class="page-header">
            <h1 class="page-title">📢 Central de Comunicação</h1>
            <p class="page-sub">Mensagens, avisos e comunicados do condomínio</p>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap: 20px;">
            <!-- COLUNA ESQUERDA: COMUNICADOS -->
            <div>
                <div class="card">
                    <div class="card-head">
                        <p class="card-title"><i class="fa-solid fa-bullhorn"></i> Comunicados Oficiais</p>
                    </div>
                    <div id="comunicados-list" style="padding:20px;">
                        <p style="text-align:center; color:var(--text-muted);">Carregando comunicados...</p>
                    </div>
                </div>
            </div>

            <!-- COLUNA DIREITA: CHAT -->
            <div>
                <div class="card">
                    <div class="card-head">
                        <p class="card-title"><i class="fa-solid fa-comments"></i> Conversa com a Administração</p>
                    </div>
                    <div style="padding:20px;">
                        <div class="chat-container">
                            <div class="chat-messages" id="chat-messages">
                                <p style="text-align:center; color:var(--text-muted);">Carregando mensagens...</p>
                            </div>
                            <form class="chat-input-area" id="chat-form">
                                <input type="text" class="chat-input" id="chat-input" placeholder="Escreva a sua mensagem..." required>
                                <button type="submit" class="btn-send"><i class="fa-solid fa-paper-plane"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

function loadComunicados() {
    fetch('../api/api_comunicacao.php?acao=listar_comunicados')
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('comunicados-list');
        list.innerHTML = '';
        if (data.sucesso && data.dados.length > 0) {
            data.dados.forEach(c => {
                const div = document.createElement('div');
                div.className = 'comunicado-card ' + c.tipo;
                div.innerHTML = `
                    <h3 style="color:${c.tipo === 'urgente' ? 'var(--danger)' : 'var(--gold)'};">${c.titulo}</h3>
                    <p style="color:var(--text-muted);margin:10px 0;">${c.conteudo}</p>
                    <span style="font-size:12px;color:var(--text-muted);"><i class="fa-regular fa-clock"></i> ${new Date(c.criado_em).toLocaleString()}</span>
                `;
                list.appendChild(div);
            });
        } else {
            list.innerHTML = '<p style="text-align:center; color:var(--text-muted);">Nenhum comunicado encontrado.</p>';
        }
    });
}

function loadMessages() {
    fetch('../api/api_comunicacao.php?acao=listar_mensagens')
    .then(r => r.json())
    .then(data => {
        const chat = document.getElementById('chat-messages');
        chat.innerHTML = '';
        if (data.sucesso && data.dados.length > 0) {
            data.dados.forEach(m => {
                const div = document.createElement('div');
                div.className = 'msg ' + (m.remetente === 'morador' ? 'sent' : 'received');
                div.textContent = m.conteudo;
                chat.appendChild(div);
            });
            chat.scrollTop = chat.scrollHeight;
        } else {
            chat.innerHTML = '<p style="text-align:center; color:var(--text-muted);">Inicie uma conversa com a equipa do condomínio.</p>';
        }
    });
}

document.getElementById('chat-form').onsubmit = function(e) {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const msg = input.value.trim();
    if (!msg) return;

    const fd = new FormData();
    fd.append('conteudo', msg);

    fetch('../api/api_comunicacao.php?acao=enviar_mensagem', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) {
            input.value = '';
            loadMessages();
        }
    });
};

function clock() {
    const now = new Date();
    const el = document.getElementById('clock-display');
    if (el) el.textContent = now.toLocaleTimeString('pt-AO');
}

window.onload = function() {
    clock();
    setInterval(clock, 1000);
    loadComunicados();
    loadMessages();
    setInterval(loadMessages, 3000); // Polling 3s
};
</script>
</body>
</html>