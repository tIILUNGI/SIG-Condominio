<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id   = $_SESSION['id'];
$morador_nome = $_SESSION['nome'];
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comunicação — Nosso Zimbo</title>
    <meta name="description" content="Central de comunicação e chat com a administração do condomínio." />
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
    <script src="../js/theme-manager.js"></script>
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        /* ── Layout ─────────────────────────────────────────────── */
        .com-grid {
            display: grid;
            grid-template-columns: 1fr 1.6fr;
            gap: 22px;
            align-items: start;
        }

        /* ── Comunicados ─────────────────────────────────────────── */
        .comunicado-card {
            background: var(--dark4, #1e2330);
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 14px;
            border-left: 4px solid var(--gold, #f0c040);
            transition: transform .15s;
        }
        .comunicado-card:hover { transform: translateX(3px); }
        .comunicado-card.urgente    { border-left-color: var(--danger, #e74c3c); }
        .comunicado-card.manutencao { border-left-color: #3498db; }
        .comunicado-card h3 { margin: 0 0 8px; font-size: .95rem; }
        .comunicado-card p  { margin: 0 0 10px; font-size: .85rem; color: var(--text-muted); line-height: 1.5; }
        .comunicado-meta    { font-size: .75rem; color: var(--text-muted); display: flex; gap: 10px; align-items: center; }
        .tipo-badge {
            font-size: .68rem; font-weight: 700; padding: 2px 7px;
            border-radius: 20px; text-transform: uppercase; letter-spacing: .04em;
        }
        .tipo-badge.informativo { background: rgba(240,192,64,.15); color: var(--gold, #f0c040); }
        .tipo-badge.urgente     { background: rgba(231,76,60,.15);  color: var(--danger, #e74c3c); }
        .tipo-badge.manutencao  { background: rgba(52,152,219,.15); color: #3498db; }

        /* ── Chat container ──────────────────────────────────────── */
        .chat-wrap {
            display: flex;
            flex-direction: column;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            height: 520px;
        }
        .chat-header-bar {
            padding: 14px 20px;
            background: var(--bg);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .chat-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4a6fa5, #3a5a8a);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .8rem; color: #fff;
            flex-shrink: 0;
        }
        .chat-header-info h4 { margin: 0; font-size: .9rem; color: var(--text); }
        .chat-header-info span { font-size: .75rem; color: #27ae60; }
        .online-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #27ae60;
            display: inline-block; margin-right: 4px;
        }

        /* ── Messages ────────────────────────────────────────────── */
        .chat-messages {
            flex: 1;
            padding: 18px 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: var(--bg);
            scroll-behavior: smooth;
        }
        .chat-empty {
            margin: auto;
            text-align: center;
            color: var(--text-muted);
        }
        .chat-empty i { font-size: 2.5rem; opacity: .15; margin-bottom: 10px; display: block; }

        .msg-row {
            display: flex;
            flex-direction: column;
            max-width: 76%;
        }
        .msg-row.sent   { align-self: flex-end; align-items: flex-end; }
        .msg-row.received { align-self: flex-start; align-items: flex-start; }

        .msg-bubble {
            padding: 10px 15px;
            border-radius: 16px;
            font-size: .875rem;
            line-height: 1.5;
            word-break: break-word;
        }
        .msg-row.sent .msg-bubble {
            background: var(--primary, #4a6fa5);
            color: #fff;
            border-bottom-right-radius: 4px;
        }
        .msg-row.received .msg-bubble {
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            border-bottom-left-radius: 4px;
        }
        .msg-time {
            font-size: .68rem;
            color: var(--text-muted);
            margin-top: 4px;
            padding: 0 4px;
        }

        /* ── Input area ──────────────────────────────────────────── */
        .chat-input-area {
            padding: 14px 18px;
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .chat-input {
            flex: 1;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px 16px;
            border-radius: 24px;
            outline: none;
            font-family: inherit;
            font-size: .875rem;
            transition: border-color .2s;
        }
        .chat-input:focus { border-color: var(--primary, #4a6fa5); }
        .btn-send {
            background: var(--primary, #4a6fa5);
            color: #fff;
            border: none;
            width: 42px; height: 42px;
            border-radius: 50%;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: .9rem;
            transition: background .2s, transform .1s;
            flex-shrink: 0;
        }
        .btn-send:hover   { background: #3a5a8a; }
        .btn-send:active  { transform: scale(.93); }
        .btn-send:disabled { opacity: .5; cursor: not-allowed; }

        /* ── Typing indicator ─────────────────────────────────────── */
        .sending-indicator {
            font-size: .75rem; color: var(--text-muted);
            padding: 0 4px; min-height: 18px;
        }

        @media (max-width: 768px) {
            .com-grid { grid-template-columns: 1fr; }
        }
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
        <button class="nav-item" onclick="window.location.href='morador_portal.php'">
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
            <div class="avatar-admin" style="width:34px;height:34px;background:#4a6fa5;color:#fff;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <div style="padding: 24px;">
        <div class="page-header">
            <h1 class="page-title">📢 Central de Comunicação</h1>
            <p class="page-sub">Mensagens, avisos e comunicados do condomínio</p>
        </div>

        <div class="com-grid">
            <!-- COLUNA ESQUERDA: COMUNICADOS -->
            <div>
                <div class="card">
                    <div class="card-head">
                        <p class="card-title"><i class="fa-solid fa-bullhorn"></i> Comunicados Oficiais</p>
                    </div>
                    <div id="comunicados-list" style="padding: 20px; max-height: 460px; overflow-y: auto;">
                        <p style="text-align:center; color:var(--text-muted);">
                            <i class="fa-solid fa-spinner fa-spin"></i> Carregando...
                        </p>
                    </div>
                </div>
            </div>

            <!-- COLUNA DIREITA: CHAT -->
            <div>
                <div class="card" style="padding: 0; overflow: hidden;">
                    <div class="chat-wrap">
                        <!-- Header do chat -->
                        <div class="chat-header-bar">
                            <div class="chat-avatar">NZ</div>
                            <div class="chat-header-info">
                                <h4>Administração do Condomínio</h4>
                                <span><span class="online-dot"></span>Em serviço</span>
                            </div>
                        </div>

                        <!-- Mensagens -->
                        <div class="chat-messages" id="chat-messages">
                            <div class="chat-empty">
                                <i class="fa-solid fa-comments"></i>
                                <p>Inicie uma conversa com a administração.</p>
                            </div>
                        </div>

                        <!-- Indicador de envio -->
                        <div style="padding: 0 20px;">
                            <div class="sending-indicator" id="sending-indicator"></div>
                        </div>

                        <!-- Input -->
                        <form class="chat-input-area" id="chat-form" onsubmit="sendMessage(event)">
                            <input
                                type="text"
                                class="chat-input"
                                id="chat-input"
                                placeholder="Escreva a sua mensagem..."
                                autocomplete="off"
                                maxlength="1000"
                            />
                            <button type="submit" class="btn-send" id="btn-send">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="toast" id="toast"></div>

<script>
const API = '../api/api_comunicacao.php';
let lastMsgId = 0;
let isPolling = false;

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

// ── Toast ────────────────────────────────────────────────────────
function showToast(msg, isErr = false) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show' + (isErr ? ' error' : '');
    setTimeout(() => t.classList.remove('show'), 3500);
}

// ── Clock ─────────────────────────────────────────────────────────
function updateClock() {
    const el = document.getElementById('clock-display');
    if (el) el.textContent = new Date().toLocaleTimeString('pt-AO');
}

// ── Comunicados ───────────────────────────────────────────────────
async function loadComunicados() {
    try {
        const res  = await fetch(API + '?acao=listar_comunicados');
        const data = await res.json();
        const list = document.getElementById('comunicados-list');

        if (!data.sucesso || data.dados.length === 0) {
            list.innerHTML = '<p style="text-align:center;color:var(--text-muted);">Nenhum comunicado disponível.</p>';
            return;
        }

        list.innerHTML = data.dados.map(c => {
            const tipoColor = c.tipo === 'urgente' ? 'var(--danger)' : (c.tipo === 'manutencao' ? '#3498db' : 'var(--gold)');
            const data_fmt = new Date(c.criado_em).toLocaleDateString('pt-AO', {
                day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
            });
            return `
                <div class="comunicado-card ${c.tipo}">
                    <h3 style="color:${tipoColor};">${escHtml(c.titulo)}</h3>
                    <p>${escHtml(c.conteudo)}</p>
                    <div class="comunicado-meta">
                        <span class="tipo-badge ${c.tipo}">${c.tipo}</span>
                        <i class="fa-regular fa-clock"></i> ${data_fmt}
                        ${c.autor ? '· <i class="fa-solid fa-user-tie"></i> ' + escHtml(c.autor) : ''}
                    </div>
                </div>`;
        }).join('');
    } catch (e) {
        console.error('Erro ao carregar comunicados:', e);
    }
}

// ── Messages ──────────────────────────────────────────────────────
async function loadMessages(isManual = false) {
    if (isPolling && !isManual) return;
    isPolling = true;

    try {
        const res  = await fetch(API + '?acao=listar_mensagens');
        const data = await res.json();
        const box  = document.getElementById('chat-messages');

        if (!data.sucesso) {
            isPolling = false;
            return;
        }

        if (data.dados.length === 0) {
            if (lastMsgId === 0) {
                box.innerHTML = `
                    <div class="chat-empty">
                        <i class="fa-solid fa-comments"></i>
                        <p>Inicie uma conversa com a administração.</p>
                    </div>`;
            }
            isPolling = false;
            return;
        }

        const newestId = data.dados[data.dados.length - 1].id;
        if (newestId === lastMsgId) {
            isPolling = false;
            return; // Sem mensagens novas
        }
        lastMsgId = newestId;

        box.innerHTML = data.dados.map(m => {
            const role     = m.de_morador ? 'sent' : 'received';
            const timeStr  = new Date(m.enviada_em).toLocaleTimeString('pt-AO', {hour:'2-digit', minute:'2-digit'});
            return `
                <div class="msg-row ${role}">
                    <div class="msg-bubble">${escHtml(m.conteudo)}</div>
                    <span class="msg-time">${timeStr}</span>
                </div>`;
        }).join('');

        box.scrollTop = box.scrollHeight;
    } catch (e) {
        console.error('Erro ao carregar mensagens:', e);
    }
    isPolling = false;
}

// ── Send message ──────────────────────────────────────────────────
async function sendMessage(e) {
    e.preventDefault();
    const input  = document.getElementById('chat-input');
    const btn    = document.getElementById('btn-send');
    const ind    = document.getElementById('sending-indicator');
    const msg    = input.value.trim();
    if (!msg) return;

    btn.disabled  = true;
    input.disabled = true;
    ind.textContent = 'A enviar...';

    try {
        const fd = new FormData();
        fd.append('conteudo', msg);

        const res  = await fetch(API + '?acao=enviar_mensagem', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.sucesso) {
            input.value    = '';
            ind.textContent = '';
            await loadMessages(true);
        } else {
            showToast(data.erro || 'Erro ao enviar mensagem.', true);
            ind.textContent = '';
        }
    } catch (err) {
        showToast('Erro de rede. Tente novamente.', true);
        ind.textContent = '';
    } finally {
        btn.disabled   = false;
        input.disabled  = false;
        input.focus();
    }
}

// ── Utility ───────────────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '';
    return str.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Allow Enter to send ───────────────────────────────────────────
document.getElementById('chat-input').addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chat-form').dispatchEvent(new Event('submit'));
    }
});

// ── Init ──────────────────────────────────────────────────────────
window.onload = () => {
    updateClock();
    setInterval(updateClock, 1000);

    loadComunicados();
    loadMessages(true);

    // Polling: comunicados a cada 60s, mensagens a cada 3s
    setInterval(loadComunicados, 60000);
    setInterval(() => loadMessages(false), 3000);
};
</script>
</body>
</html>