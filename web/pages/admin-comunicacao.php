<?php
session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    header("Location: ../login.html?erro=acesso");
    exit;
}
$admin_nome = $_SESSION['nome'] ?? 'Admin';
$admin_id   = $_SESSION['id']  ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin — Comunicação | Nosso Zimbo</title>
  <meta name="description" content="Chat e comunicados da administração do condomínio." />
  <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
  <script src="../js/theme-manager.js"></script>
  <style>
    /* ── Layout ─────────────────────────────────────────────── */
    .com-layout {
      display: grid;
      grid-template-columns: 300px 1fr;
      gap: 22px;
      height: calc(100vh - 150px);
      min-height: 500px;
    }

    /* ── Conversation sidebar ────────────────────────────────── */
    .chat-sidebar {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .chat-sidebar-header {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid var(--border);
      font-weight: 700;
      font-size: .9rem;
      color: var(--text);
      background: var(--bg);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .conversa-list {
      flex: 1;
      overflow-y: auto;
    }
    .conversa-item {
      padding: .9rem 1.25rem;
      border-bottom: 1px solid var(--border);
      cursor: pointer;
      transition: background .15s;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .conversa-item:hover   { background: var(--primary-light, rgba(74,111,165,.08)); }
    .conversa-item.active  { background: var(--primary-light, rgba(74,111,165,.1)); border-left: 3px solid var(--primary); }
    .conv-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: linear-gradient(135deg, #4a6fa5, #2e4b7e);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: .75rem; color: #fff;
      flex-shrink: 0;
    }
    .conv-info { flex: 1; min-width: 0; }
    .conv-name {
      font-weight: 600; font-size: .85rem; color: var(--text);
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 3px;
    }
    .conv-last-msg {
      font-size: .75rem; color: var(--text-muted);
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .unread-badge {
      background: var(--danger, #e74c3c);
      color: #fff;
      font-size: .65rem; font-weight: 700;
      padding: 2px 6px;
      border-radius: 10px;
      flex-shrink: 0;
    }
    .new-chat-area {
      padding: .85rem 1.25rem;
      border-top: 1px solid var(--border);
      background: var(--bg);
    }
    .new-chat-area select {
      width: 100%;
      font-size: .82rem;
    }

    /* ── Chat main panel ─────────────────────────────────────── */
    .chat-main {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .chat-header {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid var(--border);
      background: var(--bg);
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .chat-header-avatar {
      width: 40px; height: 40px; border-radius: 50%;
      background: linear-gradient(135deg, #4a6fa5, #2e4b7e);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: .85rem; color: #fff;
    }
    .chat-header-text h4 { margin: 0; font-size: .92rem; color: var(--text); }
    .chat-header-text span { font-size: .75rem; color: var(--text-muted); }
    .chat-placeholder {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: var(--text-muted);
      gap: 12px;
    }
    .chat-placeholder i { font-size: 3rem; opacity: .12; }

    /* ── Messages ────────────────────────────────────────────── */
    .chat-messages {
      flex: 1;
      padding: 1.4rem 1.5rem;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 10px;
      background: var(--bg);
      scroll-behavior: smooth;
    }
    .msg-row {
      display: flex;
      flex-direction: column;
      max-width: 72%;
    }
    .msg-row.sent     { align-self: flex-end; align-items: flex-end; }
    .msg-row.received { align-self: flex-start; align-items: flex-start; }

    .msg-bubble {
      padding: 10px 14px;
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

    /* ── Footer / input ──────────────────────────────────────── */
    .chat-footer {
      padding: 1rem 1.5rem;
      border-top: 1px solid var(--border);
      background: var(--surface);
    }
    .chat-input-row {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    #chat-input {
      flex: 1;
      border-radius: 24px;
      padding: 10px 16px;
      font-size: .875rem;
    }
    #chat-input:disabled { opacity: .5; cursor: not-allowed; }
    #btn-enviar-msg {
      width: 42px; height: 42px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .sending-indicator {
      font-size: .72rem;
      color: var(--text-muted);
      min-height: 18px;
      padding: 3px 4px 0;
    }

    /* ── Modal comunicado ────────────────────────────────────── */
    /* (uses existing modal-overlay / modal-box classes from CSS) */

    @media (max-width: 900px) {
      .com-layout { grid-template-columns: 1fr; }
      .chat-sidebar { max-height: 260px; }
    }
  </style>
</head>
<body>

<?php include('sidebar_admin.php'); ?>

<main class="main-content">
  <header class="topbar">
    <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
    <span class="topbar-title"><i class="fa-solid fa-comments"></i> Chat &amp; Comunicados</span>
    <div class="topbar-right">
      <button class="btn-primary" onclick="abrirModalComunicado()" style="font-size:.82rem;">
        <i class="fa-solid fa-bullhorn"></i> Novo Comunicado
      </button>
    </div>
  </header>

  <div style="padding: 24px;">
    <div class="page-header" style="margin-bottom: 22px;">
      <h1 class="page-title">Chat com Moradores</h1>
      <p class="page-sub">Selecione um morador na lista para visualizar ou iniciar uma conversa.</p>
    </div>

    <div class="com-layout">

      <!-- ── Sidebar: conversas ──────────────────────────── -->
      <div class="chat-sidebar">
        <div class="chat-sidebar-header">
          <i class="fa-solid fa-inbox"></i> Conversas Ativas
        </div>
        <div class="conversa-list" id="conversa-list">
          <p style="padding:20px; text-align:center; color:var(--text-muted); font-size:.85rem;">
            <i class="fa-solid fa-spinner fa-spin"></i> Carregando...
          </p>
        </div>
        <div class="new-chat-area">
          <select id="morador-novo" onchange="iniciarNovaConversa(this.value)">
            <option value="">＋ Nova conversa...</option>
          </select>
        </div>
      </div>

      <!-- ── Main: chat ──────────────────────────────────── -->
      <div class="chat-main">
        <!-- Empty state / placeholder -->
        <div id="chat-placeholder" class="chat-placeholder">
          <i class="fa-solid fa-comments"></i>
          <p>Selecione um morador para ver as mensagens.</p>
        </div>

        <!-- Active conversation (hidden until selected) -->
        <div id="chat-active" style="display:none; flex:1; flex-direction:column; overflow:hidden; display:none;">
          <div class="chat-header">
            <div class="chat-header-avatar" id="chat-avatar-initials">?</div>
            <div class="chat-header-text">
              <h4 id="chat-target-name">—</h4>
              <span id="chat-target-apt"></span>
            </div>
          </div>
          <div class="chat-messages" id="chat-messages"></div>
          <div class="chat-footer">
            <div class="sending-indicator" id="sending-indicator"></div>
            <div class="chat-input-row">
              <input id="chat-input" type="text" placeholder="Escreva a sua mensagem..." disabled maxlength="1000" />
              <button class="btn-primary" id="btn-enviar-msg" disabled>
                <i class="fa-solid fa-paper-plane"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<!-- Modal: Comunicado ──────────────────────────────────────── -->
<div class="modal-overlay" id="modal-comunicado">
  <div class="modal-box">
    <button class="modal-close" onclick="fecharModalComunicado()"><i class="fa-solid fa-xmark"></i></button>
    <h3 class="modal-title"><i class="fa-solid fa-bullhorn"></i> Publicar Comunicado</h3>
    <form id="form-comunicado" class="form-grid" style="margin-top:1.2rem;">
      <div class="form-group full">
        <label>Título do comunicado</label>
        <input name="titulo" type="text" required placeholder="Ex: Manutenção de elevadores — Bloco A" />
      </div>
      <div class="form-group full">
        <label>Conteúdo / Mensagem</label>
        <textarea name="conteudo" required rows="5" placeholder="Escreva os detalhes..."></textarea>
      </div>
      <div class="form-group">
        <label>Categoria</label>
        <select name="tipo">
          <option value="informativo">📢 Informativo</option>
          <option value="manutencao">🔧 Manutenção</option>
          <option value="urgente">🚨 Urgente</option>
        </select>
      </div>
      <div class="modal-footer" style="grid-column:1/-1; margin-top:1rem;">
        <button type="button" class="btn-secondary" onclick="fecharModalComunicado()">Cancelar</button>
        <button type="submit" class="btn-primary"><i class="fa-solid fa-paper-plane"></i> Publicar para Todos</button>
      </div>
    </form>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API          = '../api/api_comunicacao.php';
const API_MORADORES= '../api/api_moradores.php';

let activeMoradorId   = null;
let activeMoradorNome = '';
let lastMsgId         = 0;
let isPolling         = false;

// ── Sidebar toggle ───────────────────────────────────────────────
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }

// ── Toast ─────────────────────────────────────────────────────────
function showToast(msg, isErr = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show' + (isErr ? ' error' : '');
  setTimeout(() => t.classList.remove('show'), 3500);
}

// ── Modal comunicado ──────────────────────────────────────────────
function abrirModalComunicado() { document.getElementById('modal-comunicado').classList.add('open'); }
function fecharModalComunicado() { document.getElementById('modal-comunicado').classList.remove('open'); }

// ── Utility ───────────────────────────────────────────────────────
function escHtml(str) {
  if (!str) return '';
  return str.toString()
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
function initials(nome) {
  const parts = (nome || '').trim().split(' ');
  return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
}

// ── Load conversations ────────────────────────────────────────────
async function loadConversas() {
  try {
    const res  = await fetch(API + '?acao=listar_conversas');
    const data = await res.json();
    const list = document.getElementById('conversa-list');

    if (!data.sucesso || data.dados.length === 0) {
      list.innerHTML = '<p style="padding:20px;text-align:center;color:var(--text-muted);font-size:.82rem;">Nenhuma conversa ainda.<br>Inicie uma pelo seletor abaixo.</p>';
      return;
    }

    list.innerHTML = data.dados.map(c => {
      const isActive = activeMoradorId == c.morador_id;
      const ini = initials(c.morador_nome);
      return `
        <div class="conversa-item ${isActive ? 'active' : ''}"
             id="conv-item-${c.morador_id}"
             onclick="selectChat(${c.morador_id}, '${escHtml(c.morador_nome)}')">
          <div class="conv-avatar">${ini}</div>
          <div class="conv-info">
            <div class="conv-name">
              <span>${escHtml(c.morador_nome)}</span>
              ${c.nao_lidas > 0 ? `<span class="unread-badge">${c.nao_lidas}</span>` : ''}
            </div>
            <div class="conv-last-msg">${escHtml(c.ultima_msg || 'Sem mensagens')}</div>
          </div>
        </div>`;
    }).join('');
  } catch (e) {
    console.error('Erro ao carregar conversas:', e);
  }
}

// ── Load list of all moradores (for "new chat" select) ────────────
async function loadMoradores() {
  try {
    const res  = await fetch(API_MORADORES + '?acao=listar');
    const data = await res.json();
    const sel  = document.getElementById('morador-novo');
    if (data.sucesso) {
      data.dados.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.id;
        opt.dataset.nome = m.nome;
        opt.textContent = m.nome + (m.apartamento ? ' (' + m.apartamento + ')' : '');
        sel.appendChild(opt);
      });
    }
  } catch (e) {
    console.error('Erro ao carregar moradores:', e);
  }
}

// ── Start / switch conversation ───────────────────────────────────
function iniciarNovaConversa(id) {
  if (!id) return;
  const opt = document.querySelector(`#morador-novo option[value="${id}"]`);
  selectChat(id, opt ? opt.dataset.nome : 'Morador');
  document.getElementById('morador-novo').value = '';
}

function selectChat(id, nome) {
  activeMoradorId   = id;
  activeMoradorNome = nome;
  lastMsgId         = 0; // Reset so messages reload

  // UI: show active panel
  document.getElementById('chat-placeholder').style.display = 'none';
  const activeEl = document.getElementById('chat-active');
  activeEl.style.display = 'flex';
  activeEl.style.flexDirection = 'column';

  document.getElementById('chat-target-name').textContent = nome;
  document.getElementById('chat-avatar-initials').textContent = initials(nome);
  document.getElementById('chat-input').disabled   = false;
  document.getElementById('btn-enviar-msg').disabled = false;
  document.getElementById('chat-input').focus();

  // Show loading state
  document.getElementById('chat-messages').innerHTML =
    '<div style="margin:auto;text-align:center;color:var(--text-muted);font-size:.82rem;"><i class="fa-solid fa-spinner fa-spin"></i> Carregando...</div>';

  loadMessages(true);
  loadConversas();
}

// ── Load messages for active conversation ─────────────────────────
async function loadMessages(isManual = false) {
  if (!activeMoradorId) return;
  if (isPolling && !isManual) return;
  isPolling = true;

  try {
    const res  = await fetch(`${API}?acao=listar_mensagens&id_morador=${activeMoradorId}`);
    const data = await res.json();
    const box  = document.getElementById('chat-messages');

    if (!data.sucesso) {
      isPolling = false;
      return;
    }

    if (data.dados.length === 0) {
      box.innerHTML = `
        <div style="margin:auto;text-align:center;color:var(--text-muted);">
          <i class="fa-solid fa-comments" style="font-size:2rem;opacity:.1;display:block;margin-bottom:8px;"></i>
          <p style="font-size:.85rem;">Ainda não há mensagens.<br>Seja o primeiro a escrever!</p>
        </div>`;
      isPolling = false;
      return;
    }

    const newestId = data.dados[data.dados.length - 1].id;
    if (newestId === lastMsgId && !isManual) {
      isPolling = false;
      return;
    }
    lastMsgId = newestId;

    box.innerHTML = data.dados.map(m => {
      // de_morador = true  → mensagem do morador  → received (no lado do admin)
      // de_morador = false → mensagem do admin     → sent
      const role    = m.de_morador ? 'received' : 'sent';
      const timeStr = new Date(m.enviada_em).toLocaleTimeString('pt-AO', {hour:'2-digit', minute:'2-digit'});
      return `
        <div class="msg-row ${role}">
          <div class="msg-bubble">${escHtml(m.conteudo)}</div>
          <span class="msg-time">${timeStr}${m.de_morador ? '' : ' · Você'}</span>
        </div>`;
    }).join('');

    box.scrollTop = box.scrollHeight;
    // Refresh sidebar to clear unread badge
    loadConversas();
  } catch (e) {
    console.error('Erro ao carregar mensagens:', e);
  }
  isPolling = false;
}

// ── Send message ──────────────────────────────────────────────────
async function sendMsg() {
  const input = document.getElementById('chat-input');
  const btn   = document.getElementById('btn-enviar-msg');
  const ind   = document.getElementById('sending-indicator');
  const msg   = input.value.trim();
  if (!msg || !activeMoradorId) return;

  btn.disabled   = true;
  input.disabled  = true;
  ind.textContent = 'A enviar...';

  try {
    const fd = new FormData();
    fd.append('id_morador', activeMoradorId);
    fd.append('conteudo', msg);

    const res  = await fetch(API + '?acao=enviar_mensagem', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.sucesso) {
      input.value    = '';
      ind.textContent = '';
      await loadMessages(true);
    } else {
      showToast(data.erro || 'Erro ao enviar.', true);
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

// ── Comunicado form ───────────────────────────────────────────────
document.getElementById('form-comunicado').onsubmit = async (e) => {
  e.preventDefault();
  const btn = e.target.querySelector('[type="submit"]');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Publicando...';

  const fd   = new FormData(e.target);
  const res  = await fetch(API + '?acao=enviar_comunicado', { method: 'POST', body: fd });
  const data = await res.json();

  btn.disabled = false;
  btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Publicar para Todos';

  if (data.sucesso) {
    showToast('✅ Comunicado publicado com sucesso!');
    fecharModalComunicado();
    e.target.reset();
  } else {
    showToast(data.erro || 'Erro ao publicar.', true);
  }
};

// ── Event listeners ───────────────────────────────────────────────
document.getElementById('btn-enviar-msg').onclick = sendMsg;
document.getElementById('chat-input').onkeydown = e => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
};

// ── Init ──────────────────────────────────────────────────────────
window.onload = () => {
  loadConversas();
  loadMoradores();

  setInterval(() => {
    loadConversas();
    if (activeMoradorId) loadMessages(false);
  }, 3000);
};
</script>
</body>
</html>
