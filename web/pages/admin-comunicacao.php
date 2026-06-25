<?php
session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    header("Location: ../login.html?erro=acesso");
    exit;
}
$admin_nome = $_SESSION['nome'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin — Comunicação</title>
  <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
  <style>
    .com-layout { display: grid; grid-template-columns: 320px 1fr; gap: 24px; height: calc(100vh - 160px); }
    .chat-sidebar { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); display: flex; flex-direction: column; overflow: hidden; }
    .chat-sidebar-header { padding: 1.25rem; border-bottom: 1px solid var(--border); background: var(--bg); font-weight: 700; color: var(--text); }
    .conversa-list { flex: 1; overflow-y: auto; }
    .conversa-item { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); cursor: pointer; transition: all .2s; }
    .conversa-item:hover { background: var(--primary-light); }
    .conversa-item.active { background: var(--primary-light); border-left: 4px solid var(--primary); }
    .conversa-name { font-weight: 600; font-size: .9rem; color: var(--text); margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center; }
    .conversa-msg { font-size: .78rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .unread-badge { background: var(--danger); color: #fff; font-size: .7rem; padding: 2px 6px; border-radius: 10px; font-weight: 700; }

    .chat-main { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); display: flex; flex-direction: column; overflow: hidden; }
    .chat-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); background: var(--bg); display: flex; align-items: center; justify-content: space-between; }
    .chat-messages { flex: 1; padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: var(--bg); }
    .msg { max-width: 75%; padding: 10px 14px; border-radius: 12px; font-size: .9rem; line-height: 1.5; box-shadow: var(--shadow-sm); }
    .msg.sent { align-self: flex-end; background: var(--primary); color: #fff; border-bottom-right-radius: 2px; }
    .msg.received { align-self: flex-start; background: var(--surface); color: var(--text); border-bottom-left-radius: 2px; border: 1px solid var(--border); }
    .chat-foooter { padding: 1.25rem; border-top: 1px solid var(--border); background: var(--surface); }
  </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
    <div>
      <p class="brand-name">Nosso Zimbo</p>
      <p class="brand-sub">Comunicação Admin</p>
    </div>
  </div>
  <nav class="sidebar-nav">
    <button class="nav-item" onclick="window.location.href='../dashboard.php'">
      <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
    </button>
    <button class="nav-item active" onclick="window.location.href='admin-comunicacao.php'">
      <i class="fa-solid fa-comments"></i><span>Comunicação</span>
    </button>
  </nav>
  <div class="sidebar-footer">
    <div class="avatar-admin"><?php echo strtoupper(substr($admin_nome, 0, 2)); ?></div>
    <div style="flex:1;">
      <p class="af-name"><?php echo htmlspecialchars($admin_nome); ?></p>
      <p class="af-role"><?php echo ucfirst(htmlspecialchars($_SESSION['tipo'])); ?></p>
    </div>
    <a href="../api/logout.php" title="Sair" style="color:var(--text-muted); font-size:1rem;"><i class="fa-solid fa-right-from-bracket"></i></a>
  </div>
</aside>

<main class="main-content">
  <header class="topbar">
    <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
    <span class="topbar-title">📢 Central de Comunicação</span>
  </header>

  <div style="padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
      <h1 class="page-title">Chat e Comunicados</h1>
      <button class="btn-primary" onclick="abrirModalComunicado()"><i class="fa-solid fa-plus"></i> Novo Comunicado</button>
    </div>

    <div class="com-layout">
      <!-- SIDEBAR: Conversas -->
      <div class="chat-sidebar">
        <div class="chat-sidebar-header">Conversas Ativas</div>
        <div class="conversa-list" id="conversa-list">
          <p style="padding: 20px; text-align: center; color: var(--text-muted);">Carregando...</p>
        </div>
        <div style="padding: 1rem; border-top: 1px solid var(--border);">
            <select id="morador-novo" style="width:100%;" onchange="selecionarNovoMorador(this.value)">
                <option value="">Iniciar nova conversa...</option>
            </select>
        </div>
      </div>

      <!-- MAIN: Chat -->
      <div class="chat-main" id="chat-panel">
        <div class="chat-header">
          <div id="chat-target-name" style="font-weight: 700; color: var(--text);">Selecione uma conversa</div>
          <div style="font-size: .8rem; color: var(--text-muted);" id="chat-target-apt"></div>
        </div>
        <div class="chat-messages" id="chat-messages">
          <div style="margin: auto; text-align: center; color: var(--text-muted);">
            <i class="fa-solid fa-comments" style="font-size: 3rem; opacity: .1; margin-bottom: 1rem;"></i>
            <p>Selecione um morador para visualizar as mensagens.</p>
          </div>
        </div>
        <div class="chat-foooter">
          <div style="display:flex; gap:12px;">
            <input id="chat-input" type="text" placeholder="Escreva a sua mensagem..." disabled style="flex:1;" />
            <button class="btn-primary" id="btn-enviar-msg" disabled>
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- MODAL COMUNICADO -->
<div class="modal-overlay" id="modal-comunicado">
  <div class="modal-box">
    <button class="modal-close" onclick="fecharModalComunicado()"><i class="fa-solid fa-xmark"></i></button>
    <h3 class="modal-title">Publicar Comunicado</h3>
    <form id="form-comunicado" class="form-grid" style="margin-top: 1rem;">
      <div class="form-group full">
        <label>Título</label>
        <input name="titulo" type="text" required placeholder="Ex: Manutenção de Elevadores" />
      </div>
      <div class="form-group full">
        <label>Conteúdo</label>
        <textarea name="conteudo" required rows="4" placeholder="Detalhes do comunicado..."></textarea>
      </div>
      <div class="form-group full">
        <label>Urgência</label>
        <select name="tipo">
          <option value="informativo">Informativo</option>
          <option value="importante">Importante</option>
          <option value="urgente">Urgente</option>
        </select>
      </div>
      <div class="modal-footer" style="grid-column: 1/-1;">
        <button type="button" class="btn-secondary" onclick="fecharModalComunicado()">Cancelar</button>
        <button type="submit" class="btn-primary">Publicar para Todos</button>
      </div>
    </form>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
  const API = '../api/api_comunicacao.php';
  const API_MORADORES = '../api/api_moradores.php';
  let activeMoradorId = null;

  function abrirToast(m, err=false){
      const t = document.getElementById('toast');
      t.textContent = m;
      t.className = 'toast show ' + (err ? 'error' : '');
      setTimeout(()=>t.classList.remove('show'), 3000);
  }

  function toggleSidebar(){ document.getElementById('sidebar').classList.toggle('open'); }
  function abrirModalComunicado(){ document.getElementById('modal-comunicado').classList.add('open'); }
  function fecharModalComunicado(){ document.getElementById('modal-comunicado').classList.remove('open'); }

  async function loadConversas(){
    const res = await fetch(API + '?acao=listar_conversas');
    const data = await res.json();
    const list = document.getElementById('conversa-list');
    
    if(data.sucesso){
        list.innerHTML = data.dados.map(c => `
            <div class="conversa-item ${activeMoradorId == c.morador_id ? 'active' : ''}" onclick="selectChat(${c.morador_id}, '${c.morador_nome}')">
                <div class="conversa-name">
                    ${c.morador_nome}
                    ${c.nao_lidas > 0 ? `<span class="unread-badge">${c.nao_lidas}</span>` : ''}
                </div>
                <div class="conversa-msg">${c.ultima_msg || 'Sem mensagens'}</div>
            </div>
        `).join('');
    }
  }

  async function loadMoradores(){
      const res = await fetch(API_MORADORES + '?acao=listar');
      const data = await res.json();
      const sel = document.getElementById('morador-novo');
      if(data.sucesso){
          data.dados.forEach(m => {
              const opt = document.createElement('option');
              opt.value = m.id;
              opt.dataset.nome = m.nome;
              opt.textContent = m.nome + ' (' + (m.apartamento || 'S/ Casa') + ')';
              sel.appendChild(opt);
          });
      }
  }

  function selecionarNovoMorador(id){
      if(!id) return;
      const opt = document.querySelector(`#morador-novo option[value="${id}"]`);
      selectChat(id, opt.dataset.nome);
  }

  function selectChat(id, nome){
    activeMoradorId = id;
    document.getElementById('chat-target-name').textContent = nome;
    document.getElementById('chat-input').disabled = false;
    document.getElementById('btn-enviar-msg').disabled = false;
    loadMessages();
    loadConversas();
  }

  async function loadMessages(){
    if(!activeMoradorId) return;
    const res = await fetch(`${API}?acao=listar_mensagens&id_morador=${activeMoradorId}`);
    const data = await res.json();
    const box = document.getElementById('chat-messages');
    
    if(data.sucesso){
        const oldContent = box.innerHTML;
        const newHtml = data.dados.map(m => `
            <div class="msg ${m.remetente == 'funcionario' ? 'sent' : 'received'}">${m.conteudo}</div>
        `).join('');
        
        if(oldContent !== newHtml){
            box.innerHTML = newHtml;
            box.scrollTop = box.scrollHeight;
        }
    }
  }

  async function sendMsg(){
    const input = document.getElementById('chat-input');
    const msg = input.value.trim();
    if(!msg || !activeMoradorId) return;

    const fd = new FormData();
    fd.append('id_morador', activeMoradorId);
    fd.append('conteudo', msg);

    const res = await fetch(API + '?acao=enviar_mensagem', { method:'POST', body: fd });
    const data = await res.json();
    if(data.sucesso){
        input.value = '';
        loadMessages();
        loadConversas();
    }
  }

  document.getElementById('btn-enviar-msg').onclick = sendMsg;
  document.getElementById('chat-input').onkeydown = e => { if(e.key=='Enter') sendMsg(); };

  document.getElementById('form-comunicado').onsubmit = async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      const res = await fetch(API + '?acao=enviar_comunicado', { method:'POST', body: fd });
      const data = await res.json();
      if(data.sucesso){
          abrirToast('Comunicado enviado!');
          fecharModalComunicado();
          e.target.reset();
      } else {
          abrirToast(data.erro || 'Erro!', true);
      }
  };

  window.onload = () => {
    loadConversas();
    loadMoradores();
    setInterval(() => {
        loadConversas();
        if(activeMoradorId) loadMessages();
    }, 3000);
  };
</script>
</body>
</html>

