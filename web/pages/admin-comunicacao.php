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
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet" />
</head>
<body>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
    <div>
      <p class="brand-name">Nosso Zimbo</p>
      <p class="brand-sub">Admin — Comunicação</p>
    </div>
  </div>
  <nav class="sidebar-nav">
    <button class="nav-item" onclick="window.location.href='admin-gestao.html'">
      <i class="fa-solid fa-diagram-project"></i><span>Gestão</span>
    </button>
    <button class="nav-item active" onclick="window.location.href='admin-comunicacao.php'">
      <i class="fa-solid fa-comments"></i><span>Comunicação</span>
    </button>
  </nav>
  <div class="sidebar-footer">
    <div class="avatar-admin"><?php echo strtoupper(substr($admin_nome, 0, 2)); ?></div>
    <div style="flex:1;">
      <p class="af-name"><?php echo htmlspecialchars($admin_nome); ?></p>
      <p class="af-role"><?php echo htmlspecialchars($_SESSION['tipo']); ?></p>
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
    </div>
  </header>

  <section class="tab-section active">
    <div class="page-header">
      <h1 class="page-title">📢 Central de Comunicação</h1>
      <p class="page-sub">Comunicados e chat com moradores</p>
    </div>

    <div class="card" style="padding:1rem; margin-bottom:1rem;">
      <div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
        <div style="flex:1; min-width:260px;">
          <label style="display:block; font-weight:700; color:var(--text-muted); font-size:.85rem;">Destinatário (morador)</label>
          <select id="morador-destino" style="width:100%; padding:.7rem; border:1px solid var(--border); border-radius:10px;">
            <option value="">— Selecione —</option>
          </select>
        </div>
        <div style="flex:2; min-width:320px;">
          <label style="display:block; font-weight:700; color:var(--text-muted); font-size:.85rem;">Escrever mensagem</label>
          <div style="display:flex; gap:.6rem;">
            <input id="chat-input" type="text" placeholder="Escreva a sua mensagem..." style="flex:1; padding:.7rem; border:1px solid var(--border); border-radius:10px; background:var(--surface); color:var(--text);" />
            <button class="btn-primary" id="btn-enviar-msg" style="white-space:nowrap; height:44px; padding:0 1rem;">
              <i class="fa-solid fa-paper-plane"></i> Enviar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1.4fr; gap:20px;">

      <!-- COMUNICADOS -->
      <div>
        <div class="card">
          <div class="card-head">
            <p class="card-title"><i class="fa-solid fa-bullhorn"></i> Comunicados</p>
          </div>
          <div style="padding:1rem;">
            <form id="form-comunicado" style="display:grid; gap:.75rem;">
              <div>
                <label style="display:block; font-weight:700; color:var(--text-muted); font-size:.85rem;">Título</label>
                <input name="titulo" type="text" required style="width:100%; padding:.7rem; border:1px solid var(--border); border-radius:10px; background:var(--surface); color:var(--text);" />
              </div>
              <div>
                <label style="display:block; font-weight:700; color:var(--text-muted); font-size:.85rem;">Conteúdo</label>
                <textarea name="conteudo" required rows="4" style="width:100%; padding:.7rem; border:1px solid var(--border); border-radius:10px; background:var(--surface); color:var(--text);"></textarea>
              </div>
              <div style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
                <div style="min-width:220px; flex:1;">
                  <label style="display:block; font-weight:700; color:var(--text-muted); font-size:.85rem;">Tipo</label>
                  <select name="tipo" style="width:100%; padding:.7rem; border:1px solid var(--border); border-radius:10px; background:var(--surface); color:var(--text);">
                    <option value="informativo">informativo</option>
                    <option value="urgente">urgente</option>
                    <option value="manutencao">manutencao</option>
                  </select>
                </div>
                <button class="btn-secondary" type="submit" style="white-space:nowrap;">
                  <i class="fa-solid fa-square-plus"></i> Publicar
                </button>
              </div>
            </form>

            <hr style="border:none; border-top:1px solid var(--border); margin:1rem 0;" />

            <div id="comunicados-list" style="display:grid; gap:0.8rem;">
              <p style="text-align:center; color:var(--text-muted);">Carregando comunicados...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- CHAT -->
      <div>
        <div class="card">
          <div class="card-head">
            <p class="card-title"><i class="fa-solid fa-comments"></i> Chat</p>
          </div>
          <div style="padding:1rem;">
            <div class="chat-container">
              <div class="chat-messages" id="chat-messages">
                <p style="text-align:center; color:var(--text-muted);">Selecione um morador para ver/mandar mensagens.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<!-- TOAST -->
<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toast-msg"></span></div>

<style>
  .chat-container{
    display:flex; flex-direction:column; height:460px;
    background: var(--dark3); border-radius:12px; border:1px solid var(--border); overflow:hidden;
  }
  .chat-messages{ flex:1; padding:16px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; }
  .msg{ max-width:78%; padding:10px 14px; border-radius:15px; font-size:.9rem; line-height:1.4; }
  .msg.sent{ align-self:flex-end; background:var(--gold); color:#000; border-bottom-right-radius:2px; }
  .msg.received{ align-self:flex-start; background:var(--dark4); color:var(--text); border-bottom-left-radius:2px; border:1px solid var(--border); }
</style>

<script>
  const API_MORADORES = '../api/api_moradores.php';
  const API_CHAT = '../api/api_comunicacao.php';

  let destinatarioId = '';

  function abrirToast(msg, isErr=false){
    const t = document.getElementById('toast');
    const el = document.getElementById('toast-msg');
    el.textContent = msg;
    t.className = 'toast' + (isErr ? ' error' : '') + ' show';
    setTimeout(()=>t.classList.remove('show'), 3200);
  }

  function toggleSidebar(){ document.getElementById('sidebar').classList.toggle('open'); }

  function escapeHtml(str){
    return String(str)
      .replaceAll('&','&amp;')
      .replaceAll('<','<')
      .replaceAll('>','>')
      .replaceAll('"','"')
      .replaceAll("'",'&#039;');
  }

  function clock(){
    const el = document.getElementById('clock-display');
    if(!el) return;
    el.textContent = new Date().toLocaleTimeString('pt-AO');
  }

  async function carregarMoradores(){
    const sel = document.getElementById('morador-destino');
    sel.innerHTML = '<option value="">— Selecione —</option>';

    const res = await fetch(API_MORADORES + '?acao=listar');
    const data = await res.json();
    if(!data.sucesso){
      abrirToast(data.erro || 'Erro ao carregar moradores', true);
      return;
    }

    (data.dados||[]).forEach(m=>{
      const texto = `${m.nome || '—'} (BI: ${m.numbi || '—'})`;
      const apt = m.apartamento ? ` - ${m.apartamento}` : '';
      const opt = document.createElement('option');
      opt.value = m.id;
      opt.textContent = texto + apt;
      sel.appendChild(opt);
    });
  }

  async function loadMessages(id_morador){
    const chat = document.getElementById('chat-messages');
    chat.innerHTML = '<p style="text-align:center; color:var(--text-muted);">Carregando mensagens...</p>';

    const url = API_CHAT + '?acao=listar_mensagens&id_morador=' + encodeURIComponent(id_morador);
    const res = await fetch(url);
    const data = await res.json();

    if(!data.sucesso){
      chat.innerHTML = '<p style="text-align:center; color:var(--text-muted);">'+escapeHtml(data.erro || 'Erro ao carregar')+'</p>';
      return;
    }

    if(!data.dados || data.dados.length === 0){
      chat.innerHTML = '<p style="text-align:center; color:var(--text-muted);">Sem mensagens ainda.</p>';
      return;
    }

    chat.innerHTML = data.dados.map(m=>{
      const sent = (m.remetente === 'funcionario');
      return `<div class="msg ${sent ? 'sent' : 'received'}">${escapeHtml(m.conteudo || '')}</div>`;
    }).join('');

    chat.scrollTop = chat.scrollHeight;
  }

  function getInput(){ return document.getElementById('chat-input'); }

  async function enviarMensagem(){
    const input = getInput();
    const msg = input.value.trim();
    if(!destinatarioId){ abrirToast('Selecione um morador', true); return; }
    if(!msg){ abrirToast('Mensagem vazia', true); return; }

    const fd = new FormData();
    fd.append('id_morador', destinatarioId);
    fd.append('conteudo', msg);

    const res = await fetch(API_CHAT + '?acao=enviar_mensagem', { method:'POST', body: fd });
    const data = await res.json();

    if(!data.sucesso){
      abrirToast(data.erro || 'Erro ao enviar mensagem', true);
      return;
    }

    input.value = '';
    await loadMessages(destinatarioId);
  }

  async function loadComunicados(){
    const list = document.getElementById('comunicados-list');
    list.innerHTML = '<p style="text-align:center; color:var(--text-muted);">Carregando comunicados...</p>';

    const res = await fetch(API_CHAT + '?acao=listar_comunicados');
    const data = await res.json();

    if(!data.sucesso){
      list.innerHTML = '<p style="text-align:center; color:var(--text-muted);">Erro ao carregar</p>';
      return;
    }

    if(!data.dados || data.dados.length === 0){
      list.innerHTML = '<p style="text-align:center; color:var(--text-muted);">Nenhum comunicado.</p>';
      return;
    }

    list.innerHTML = data.dados.map(c=>{
      const cor = c.tipo === 'urgente' ? 'var(--danger)' : (c.tipo === 'manutencao' ? '#3498db' : 'var(--gold)');
      return `
        <div class="comunicado-card ${escapeHtml(c.tipo||'')}" style="border-left-color:${cor};">
          <h3 style="margin:0 0 8px; color:${cor}; font-size:1rem;">${escapeHtml(c.titulo||'')}</h3>
          <p style="margin:0 0 10px; color:var(--text-muted); white-space:pre-wrap;">${escapeHtml(c.conteudo||'')}</p>
          <span style="font-size:12px;color:var(--text-muted);"><i class="fa-regular fa-clock"></i> ${c.criado_em ? new Date(c.criado_em).toLocaleString('pt-AO') : ''}</span>
        </div>
      `;
    }).join('');
  }

  document.getElementById('morador-destino').addEventListener('change', async (e)=>{
    destinatarioId = e.target.value;
    if(destinatarioId){
      await loadMessages(destinatarioId);
    } else {
      document.getElementById('chat-messages').innerHTML = '<p style="text-align:center; color:var(--text-muted);">Selecione um morador para ver/mandar mensagens.</p>';
    }
  });

  document.getElementById('btn-enviar-msg').addEventListener('click', enviarMensagem);
  document.getElementById('chat-input').addEventListener('keydown', (e)=>{
    if(e.key === 'Enter'){
      e.preventDefault();
      enviarMensagem();
    }
  });

  document.getElementById('form-comunicado').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const form = e.target;
    const fd = new FormData(form);

    const res = await fetch(API_CHAT + '?acao=enviar_comunicado', { method:'POST', body: fd });
    const data = await res.json();

    if(data.sucesso){
      abrirToast('Comunicado publicado!');
      form.reset();
      await loadComunicados();
    } else {
      abrirToast(data.erro || 'Erro ao publicar comunicado', true);
    }
  });

  window.onload = async ()=>{
    clock();
    setInterval(clock, 1000);
    await carregarMoradores();
    await loadComunicados();
    // polling mensagens
    setInterval(()=>{
      if(destinatarioId) loadMessages(destinatarioId);
    }, 5000);
  };
</script>
</body>
</html>

