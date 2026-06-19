function load() {
  try { allRegs = JSON.parse(localStorage.getItem('nz_registos') || '[]'); } catch(e){ allRegs=[]; }
  try { allHouses = JSON.parse(localStorage.getItem('nz_casas') || '[]'); } catch(e){ allHouses=[]; }
  try { allPays = JSON.parse(localStorage.getItem('nz_pagamentos') || '[]'); } catch(e){ allPays=[]; }
  try { allMorPays = JSON.parse(localStorage.getItem('nz_mor_pays') || '[]'); } catch(e){ allMorPays=[]; }
}
function save() {
  localStorage.setItem('nz_registos', JSON.stringify(allRegs));
  localStorage.setItem('nz_casas', JSON.stringify(allHouses));
  localStorage.setItem('nz_pagamentos', JSON.stringify(allPays));
  localStorage.setItem('nz_mor_pays', JSON.stringify(allMorPays));
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// INIT
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
window.onload = () => {
  load();
  clock();
  setInterval(clock, 1000);

  const now = new Date();
  document.getElementById('dash-date').textContent = now.toLocaleDateString('pt-AO', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
  document.getElementById('rel-mes').value = now.getMonth();
  document.getElementById('rel-ano').value = now.getFullYear().toString();

  // Hide month/year selectors initially (day mode)
  document.getElementById('rel-mes').style.display = 'none';
  document.getElementById('rel-ano').style.display = 'none';

  seedDemoData();
  renderDashboard();
  renderPedidos();
  updateBadgePedidos();
  renderRegistos();
  renderHouses();
  renderPays();
  renderMorPays();
  renderPayHistory();
  buildReport();
  initCharts();
};

function seedDemoData() {
  if (allHouses.length === 0) {
    const blocos = ['A','B','C'];
    let id = 1;
    blocos.forEach(b => {
      for (let i = 1; i <= 4; i++) {
        allHouses.push({ id: id++, bloco: b, rua: 'Rua ' + b, numero: String(i * 10 + Math.floor(Math.random()*5)), tipo: 'V3', andar: 'R/C', zona: 'Zona 1', estado: i<=2 ? 'disponivel' : 'ocupada', obs: '' });
      }
    });
    save();
  }
  if (allMorPays.length === 0) {
    const moradores = ['JoÃ£o Manuel â€“ Apt 4B', 'Ana Fernandes â€“ Casa 12', 'Pedro Lopes â€“ Apt 2A'];
    const tipos = ['Renda Mensal','Cota de CondomÃ­nio','PrestaÃ§Ã£o de Compra'];
    const vals = [150000, 14000, 1000000];
    moradores.forEach((m, i) => {
      allMorPays.push({ id: Date.now()+i, nome: m.split('â€“')[0].trim(), apt: m.split('â€“')[1].trim(), tipo: tipos[i], valor: vals[i], metodo: 'TransferÃªncia BancÃ¡ria', data: new Date(2025, 4, i+3).toLocaleDateString('pt-AO'), estado: i===0 ? 'pendente':'confirmado' });
    });
    save();
  }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// NAVIGATION
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function switchTab(id, btn) {
  document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  if (btn) btn.classList.add('active');
  if (id === 'relatorio') buildReport();
  if (id === 'pedidos') renderPedidos();
  if (id === 'moradores') { renderMorPays(); renderPayHistory(); }
}
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// CLOCK
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function clock() {
  const now = new Date();
  document.getElementById('clock-display').textContent = now.toLocaleTimeString('pt-AO');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// DASHBOARD
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function renderDashboard() {
  load();
  document.getElementById('ds-total-reg').textContent = allRegs.length;
  const totalPay = [...allPays, ...allMorPays].reduce((s,p) => s + (p.total||p.valor||0), 0);
  document.getElementById('ds-receitas').textContent = fmt(totalPay) + ' Kz';
  document.getElementById('ds-pendentes').textContent = allRegs.filter(r => r.status === 'pendente').length;
  const disp = allHouses.filter(h => h.estado === 'disponivel').length;
  document.getElementById('ds-casas').textContent = disp;
  document.getElementById('badge-reg').textContent = allRegs.filter(r=>r.status==='pendente').length;
  document.getElementById('badge-pay').textContent = allPays.filter(p=>p.status==='pendente').length;
  updateBadgePedidos();

  // Recent table
  const tbody = document.getElementById('recent-tbody');
  const recent = allRegs.slice(-6).reverse();
  if (!recent.length) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem registos</td></tr>'; return; }
  tbody.innerHTML = recent.map(r => `
    <tr>
      <td>${r.nome}</td>
      <td>${r.servico === 'aluguel' ? 'Arrendamento' : 'Compra'}</td>
      <td>${fmt(r.total)} Kz</td>
      <td>${r.data}</td>
      <td><span class="badge ${r.status}">${r.status}</span></td>
    </tr>
  `).join('');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// CHARTS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
let chartR, chartS;
function initCharts() {
  const months = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
  const now = new Date();
  const labels = [];
  const data = [];
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    labels.push(months[d.getMonth()]);
    const mo = d.getMonth(), yr = d.getFullYear();
    const sum = [...allPays,...allMorPays].filter(p => {
      const dd = new Date(p.data || p.hora || '2025');
      return dd.getMonth()===mo && dd.getFullYear()===yr;
    }).reduce((s,p)=>s+(p.total||p.valor||0),0);
    data.push(sum || Math.floor(Math.random()*3000000 + 500000));
  }

  const ctx1 = document.getElementById('chartReceitas').getContext('2d');
  if (chartR) chartR.destroy();
  chartR = new Chart(ctx1, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Receitas (Kz)',
        data,
        borderColor: '#c9a84c',
        backgroundColor: 'rgba(201,168,76,0.1)',
        borderWidth: 2.5,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#c9a84c',
        pointRadius: 5,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8a8070' } },
        y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8a8070', callback: v => (v/1000000).toFixed(1)+'M' } }
      }
    }
  });

  const alug = allRegs.filter(r=>r.servico==='aluguel').length;
  const comp = allRegs.filter(r=>r.servico==='compra').length;
  const ctx2 = document.getElementById('chartServicos').getContext('2d');
  if (chartS) chartS.destroy();
  chartS = new Chart(ctx2, {
    type: 'doughnut',
    data: {
      labels: ['Arrendamento', 'Compra', 'Casas Livres'],
      datasets: [{
        data: [alug||2, comp||1, allHouses.filter(h=>h.estado==='disponivel').length||5],
        backgroundColor: ['#c9a84c','#4caf7d','#5299e0'],
        borderColor: '#111', borderWidth: 3,
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: true,
      plugins: {
        legend: { position: 'bottom', labels: { color: '#8a8070', padding: 14, font: { size: 12 } } }
      },
      cutout: '62%',
    }
  });
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// REGISTOS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
let regFilter = 'todos';
function filterRegs(f, btn) {
  regFilter = f;
  document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  renderRegistos();
}
function renderRegistos() {
  load();
  const tbody = document.getElementById('regs-tbody');
  let regs = allRegs;
  if (regFilter !== 'todos') regs = regs.filter(r => r.status === regFilter || r.servico === regFilter);
  if (!regs.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem registos</td></tr>'; return; }
  tbody.innerHTML = regs.reverse().map(r => `
    <tr>
      <td><span class="house-tag">${r.codigo||'â€”'}</span></td>
      <td>${r.nome}</td>
      <td style="font-size:.8rem; font-family:monospace;">${r.bi||'â€”'}</td>
      <td>${r.servico==='aluguel'?'Arrendamento':'Compra'}</td>
      <td>${fmt(r.total)} Kz</td>
      <td>${r.data}</td>
      <td><span class="badge ${r.status}">${r.status}</span></td>
      <td>
        <button class="btn-secondary btn-sm" onclick="viewReg(${r.id})"><i class="fa-solid fa-eye"></i></button>
        <button class="btn-success btn-sm" style="margin-left:4px;" onclick="openAssignHouse(${r.id})" title="Atribuir Casa"><i class="fa-solid fa-house-circle-check"></i></button>
      </td>
    </tr>
  `).join('');
}

function viewReg(id) {
  const r = allRegs.find(x=>x.id===id);
  if (!r) return;
  currentRegId = id;
  const h = r.house || {};
  document.getElementById('modal-reg-body').innerHTML = `
    <div class="recibo-box">
      <div class="recibo-header">
        <div><i class="fa-solid fa-user" style="font-size:1.5rem;color:#000;"></i></div>
        <div><h3>${r.nome}</h3><p>Ref: ${r.ref||r.id}</p></div>
      </div>
      <div class="recibo-body">
        <div class="recibo-row"><span class="rk">BI</span><span class="rv">${r.bi||'â€”'}</span></div>
        <div class="recibo-row"><span class="rk">Telefone</span><span class="rv">${r.tel||'â€”'}</span></div>
        <div class="recibo-row"><span class="rk">Email</span><span class="rv">${r.email||'â€”'}</span></div>
        <div class="recibo-row"><span class="rk">Morada</span><span class="rv">${r.morada||'â€”'}</span></div>
        <div class="recibo-row"><span class="rk">ServiÃ§o</span><span class="rv gold">${r.servico==='aluguel'?'Arrendamento':'Compra'}</span></div>
        <div class="recibo-row"><span class="rk">Valor</span><span class="rv gold">${fmt(r.total)} Kz</span></div>
        <div class="recibo-row"><span class="rk">MÃ©todo</span><span class="rv">${r.metodo||'â€”'}</span></div>
        <div class="recibo-row"><span class="rk">Casa AtribuÃ­da</span><span class="rv">${h.bloco?`Bloco ${h.bloco} Â· Rua ${h.rua} Â· NÂº ${h.numero}`:'NÃ£o atribuÃ­da'}</span></div>
        <div class="recibo-row"><span class="rk">CÃ³digo de Acesso</span><span class="rv" style="font-family:monospace;font-size:1.2rem;color:var(--gold);font-weight:900;">${r.codigo||'â€”'}</span></div>
        <div class="recibo-row"><span class="rk">Data</span><span class="rv">${r.data} ${r.hora||''}</span></div>
        <div class="recibo-row"><span class="rk">Estado</span><span class="rv"><span class="badge ${r.status}">${r.status}</span></span></div>
      </div>
    </div>
  `;
  document.getElementById('modal-reg').classList.add('open');
}

function approveReg() {
  const idx = allRegs.findIndex(x=>x.id===currentRegId);
  if (idx>=0) { allRegs[idx].status = 'aprovado'; save(); }
  closeModal('modal-reg');
  renderRegistos();
  renderDashboard();
  showToast('Registo aprovado com sucesso!');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// CASAS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function addHouse() {
  const bloco = document.getElementById('h-bloco').value.trim();
  const rua = document.getElementById('h-rua').value.trim();
  const numero = document.getElementById('h-numero').value.trim();
  if (!bloco || !rua || !numero) { showToast('Preencha Bloco, Rua e NÃºmero', true); return; }
  const house = {
    id: Date.now(), bloco, rua, numero, tipo: 'V3',
    andar: document.getElementById('h-andar').value || 'â€”',
    zona: document.getElementById('h-zona').value || 'â€”',
    estado: document.getElementById('h-estado').value,
    obs: document.getElementById('h-obs').value
  };
  allHouses.push(house);
  save();
  renderHouses();
  renderDashboard();
  showToast('Casa adicionada com sucesso!');
  ['h-bloco','h-rua','h-numero','h-andar','h-zona','h-obs'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('h-tipo').value = 'V3';
}
function renderHouses() {
  const tbody = document.getElementById('houses-tbody');
  if (!allHouses.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem casas</td></tr>'; return; }
  tbody.innerHTML = allHouses.map(h => `
    <tr>
      <td><span class="house-tag">${h.bloco}</span></td>
      <td>${h.rua}</td>
      <td>${h.numero}</td>
      <td>${h.tipo}</td>
      <td><span class="badge ${h.estado === 'disponivel' ? 'pago' : h.estado === 'reservada' ? 'pendente' : 'vencido'}">${h.estado}</span></td>
      <td><button class="btn-danger btn-sm" onclick="removeHouse(${h.id})"><i class="fa-solid fa-trash"></i></button></td>
    </tr>
  `).join('');
}
function removeHouse(id) {
  allHouses = allHouses.filter(h=>h.id!==id);
  save(); renderHouses();
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ASSIGN HOUSE
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
let pendingAssignRegId = null;
function openAssignHouse(regId) {
  pendingAssignRegId = regId;
  const r = allRegs.find(x=>x.id===regId);
  document.getElementById('attr-visitante').value = r ? r.nome : 'â€”';
  const sel = document.getElementById('attr-casa-select');
  sel.innerHTML = '<option value="">â€” Seleccione â€”</option>';
  allHouses.filter(h=>h.estado==='disponivel').forEach(h => {
    sel.innerHTML += `<option value="${h.id}">Bloco ${h.bloco} Â· Rua ${h.rua} Â· NÂº ${h.numero} Â· ${h.tipo}</option>`;
  });
  document.getElementById('attr-house-preview').style.display = 'none';
  sel.onchange = () => {
    if (sel.value) {
      const c = String(Math.floor(1000 + Math.random()*9000));
      document.getElementById('attr-codigo-display').textContent = c;
      document.getElementById('attr-house-preview').style.display = 'block';
      sel._codigo = c;
    } else {
      document.getElementById('attr-house-preview').style.display = 'none';
    }
  };
  document.getElementById('modal-house').classList.add('open');
}
function assignHouse() {
  const sel = document.getElementById('attr-casa-select');
  if (!sel.value) { showToast('Seleccione uma casa', true); return; }
  const house = allHouses.find(h=>h.id===parseInt(sel.value));
  const codigo = sel._codigo || String(Math.floor(1000+Math.random()*9000));
  if (house) { house.estado = 'ocupada'; }
  const idx = allRegs.findIndex(x=>x.id===pendingAssignRegId);
  if (idx>=0) {
    allRegs[idx].house = { bloco: house.bloco, rua: house.rua, numero: house.numero, tipo: house.tipo, andar: house.andar, zona: house.zona };
    allRegs[idx].codigo = codigo;
    allRegs[idx].status = 'aprovado';
    allRegs[idx].aprovadoEm = new Date().toLocaleString('pt-AO');
  }
  // Save for visitante page to read
  localStorage.setItem('nz_pending_house', JSON.stringify({ bloco: house.bloco, rua: house.rua, numero: house.numero, tipo: house.tipo, andar: house.andar, zona: house.zona, codigo }));
  save();
  renderHouses(); renderRegistos(); renderDashboard();
  closeModal('modal-house');
  showToast('Casa atribuÃ­da! CÃ³digo enviado ao visitante.');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// PAGAMENTOS VISITANTES
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function renderPays() {
  const tbody = document.getElementById('pays-tbody');
  const pays = [...allPays].reverse();
  if (!pays.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem pagamentos registados</td></tr>'; return; }
  tbody.innerHTML = pays.map(p => `
    <tr>
      <td style="font-size:.78rem;font-family:monospace;">${p.ref||p.id}</td>
      <td>${p.nome}</td>
      <td>${p.servico==='aluguel'?'Arrendamento':'Compra'}</td>
      <td><strong>${fmt(p.total)} Kz</strong></td>
      <td>${p.metodo||'â€”'}</td>
      <td>${p.data}</td>
      <td><span class="badge ${p.status||'pendente'}">${p.status||'pendente'}</span></td>
      <td><button class="btn-success btn-sm" onclick="confirmPay(${p.id})"><i class="fa-solid fa-check"></i> Confirmar</button></td>
    </tr>
  `).join('');
}
function confirmPay(id) {
  const idx = allPays.findIndex(p=>p.id===id);
  if (idx>=0) { allPays[idx].status = 'pago'; save(); renderPays(); showToast('Pagamento confirmado!'); }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// PAGAMENTOS MORADORES
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function addMoradorPay() {
  const nome = document.getElementById('mor-nome').value.trim();
  const apt = document.getElementById('mor-apt').value.trim();
  const valor = parseFloat(document.getElementById('mor-valor').value);
  if (!nome || !apt || !valor) { showToast('Preencha os campos obrigatÃ³rios', true); return; }
  const pay = {
    id: Date.now(), nome, apt,
    tipo: document.getElementById('mor-tipo').value,
    valor,
    metodo: document.getElementById('mor-metodo').value,
    data: document.getElementById('mor-data').value || new Date().toLocaleDateString('pt-AO'),
    dataISO: document.getElementById('mor-data').value || new Date().toISOString().split('T')[0],
    estado: 'confirmado'
  };
  allMorPays.push(pay);
  save(); renderMorPays(); renderPayHistory(); renderDashboard();
  showToast('Pagamento do morador guardado!');
  ['mor-nome','mor-apt','mor-valor','mor-data'].forEach(id=>document.getElementById(id).value='');
}
function renderMorPays() {
  const tbody = document.getElementById('mor-tbody');
  const pays = [...allMorPays].reverse();
  if (!pays.length) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">Sem pagamentos de moradores</td></tr>'; return; }
  tbody.innerHTML = pays.map(p => `
    <tr>
      <td>${p.nome}</td>
      <td><span class="house-tag">${p.apt}</span></td>
      <td>${p.tipo}</td>
      <td><strong>${fmt(p.valor)} Kz</strong></td>
      <td>${p.metodo}</td>
      <td>${p.data}</td>
      <td><span class="badge ${p.estado==='confirmado'?'pago':'pendente'}">${p.estado}</span></td>
    </tr>
  `).join('');
}
function renderPayHistory() {
  const container = document.getElementById('pay-history-container');
  const countEl = document.getElementById('hist-count');
  if (!container) return;
  const pays = [...allMorPays].reverse();
  if (countEl) countEl.textContent = pays.length + ' reg.';
  if (!pays.length) {
    container.innerHTML = '<div class="empty-state" style="padding:2rem 0;"><i class="fa-solid fa-receipt"></i><p>Sem pagamentos</p></div>';
    return;
  }
  const tipoIcon = { renda:'fa-home', cota:'fa-building', multa:'fa-triangle-exclamation', prestacao:'fa-coins', Renda: 'fa-home' };
  container.innerHTML = pays.map(p => {
    const iconKey = Object.keys(tipoIcon).find(k => (p.tipo||'').toLowerCase().includes(k.toLowerCase())) || 'renda';
    const icon = tipoIcon[iconKey] || 'fa-circle-dollar-to-slot';
    return `
    <div class="pay-hist-item ${p.estado||'confirmado'}">
      <div class="phi-top">
        <span class="phi-nome"><i class="fa-solid ${icon}" style="color:var(--gold);margin-right:.35rem;font-size:.8rem;"></i>${p.nome}</span>
        <span class="phi-valor">${fmt(p.valor)} Kz</span>
      </div>
      <div class="phi-bottom">
        <span><i class="fa-solid fa-house-circle-check"></i> ${p.apt}</span>
        <span><i class="fa-solid fa-tag"></i> ${p.tipo}</span>
        <span><i class="fa-solid fa-calendar-day"></i> ${p.data}</span>
        <span style="color:${p.estado==='confirmado'?'var(--success)':'var(--warn)'};">
          <i class="fa-solid fa-${p.estado==='confirmado'?'circle-check':'clock'}"></i> ${p.estado}
        </span>
      </div>
    </div>`;
  }).join('');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// RELATÃ“RIO â€” DIÃRIO + MENSAL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
let relTipo = 'dia'; // 'dia' | 'mes'

function setRelTipo(tipo) {
  relTipo = tipo;
  const btnDia = document.getElementById('rel-tipo-dia');
  const btnMes = document.getElementById('rel-tipo-mes');
  const selMes = document.getElementById('rel-mes');
  const selAno = document.getElementById('rel-ano');
  if (tipo === 'dia') {
    btnDia.style.background = 'var(--gold)'; btnDia.style.color = '#000'; btnDia.style.fontWeight = '600';
    btnMes.style.background = 'transparent'; btnMes.style.color = 'var(--text-muted)'; btnMes.style.fontWeight = '400';
    selMes.style.display = 'none'; selAno.style.display = 'none';
  } else {
    btnMes.style.background = 'var(--gold)'; btnMes.style.color = '#000'; btnMes.style.fontWeight = '600';
    btnDia.style.background = 'transparent'; btnDia.style.color = 'var(--text-muted)'; btnDia.style.fontWeight = '400';
    selMes.style.display = ''; selAno.style.display = '';
  }
  buildReport();
}

function buildReport() {
  if (relTipo === 'dia') buildDayReport();
  else buildMonthReport();
}

function buildDayReport() {
  const today = new Date();
  const todayStr = today.toLocaleDateString('pt-AO');
  const todayISO = today.toISOString().split('T')[0];

  function isToday(p) {
    const d = p.data || '';
    return d === todayStr || d === todayISO || (p.dataISO && p.dataISO === todayISO);
  }

  const rVis = allPays.filter(isToday);
  const rMor = allMorPays.filter(isToday);
  const totalVis = rVis.reduce((s,p)=>s+(p.total||0),0);
  const totalMor = rMor.reduce((s,p)=>s+(p.valor||0),0);
  const totalDia = totalVis + totalMor;

  // Pagamentos do mÃªs tambÃ©m
  const mes = today.getMonth(), ano = today.getFullYear();
  const rVisMes = allPays.filter(p => { try { const d = new Date(p.data||''); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;} });
  const rMorMes = allMorPays.filter(p => { try { const d = new Date(p.data||''); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;} });
  const totalMes = rVisMes.reduce((s,p)=>s+(p.total||0),0) + rMorMes.reduce((s,p)=>s+(p.valor||0),0);
  const mesNome = ['Janeiro','Fevereiro','MarÃ§o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'][mes];

  document.getElementById('relatorio-content').innerHTML = `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-calendar-day"></i> RelatÃ³rio do Dia â€” ${todayStr}</p>
      <div class="day-summary-bar">
        <div class="day-kpi"><div class="dk-val">${fmt(totalDia)} Kz</div><div class="dk-label">Total Recebido Hoje</div></div>
        <div class="day-kpi"><div class="dk-val">${rVis.length + rMor.length}</div><div class="dk-label">TransacÃ§Ãµes Hoje</div></div>
        <div class="day-kpi"><div class="dk-val" style="color:var(--info);">${fmt(totalMes)} Kz</div><div class="dk-label">Total do MÃªs (${mesNome})</div></div>
      </div>
    </div>

    ${(rVis.length || rMor.length) ? `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-receipt"></i> Pagamentos Recebidos Hoje</p>
      <div class="card" style="overflow:auto; margin-bottom:1rem;">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-users"></i> Moradores â€” ${rMor.length} transacÃ§Ãµes</p></div>
        <table class="data-table">
          <thead><tr><th>Morador</th><th>Apt</th><th>Tipo</th><th>Valor</th><th>MÃ©todo</th><th>Estado</th></tr></thead>
          <tbody>
            ${rMor.length ? rMor.map(p=>`<tr><td><strong>${p.nome}</strong></td><td><span style="font-family:monospace;font-size:.8rem;">${p.apt}</span></td><td>${p.tipo}</td><td><strong style="color:var(--success);">${fmt(p.valor)} Kz</strong></td><td>${p.metodo}</td><td><span class="badge ${p.estado==='confirmado'?'pago':'pendente'}">${p.estado}</span></td></tr>`).join('') : '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:1rem;">Sem pagamentos de moradores hoje</td></tr>'}
          </tbody>
        </table>
      </div>
      <div class="card" style="overflow:auto;">
        <div class="card-head"><p class="card-title"><i class="fa-solid fa-user-clock"></i> Visitantes â€” ${rVis.length} transacÃ§Ãµes</p></div>
        <table class="data-table">
          <thead><tr><th>Cliente</th><th>ServiÃ§o</th><th>Valor</th><th>MÃ©todo</th><th>Estado</th></tr></thead>
          <tbody>
            ${rVis.length ? rVis.map(p=>`<tr><td><strong>${p.nome}</strong></td><td>${p.servico==='aluguel'?'Arrendamento':'Compra'}</td><td><strong style="color:var(--success);">${fmt(p.total)} Kz</strong></td><td>${p.metodo||'â€”'}</td><td><span class="badge ${p.status||'pendente'}">${p.status||'pendente'}</span></td></tr>`).join('') : '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:1rem;">Sem pagamentos de visitantes hoje</td></tr>'}
          </tbody>
        </table>
      </div>
    </div>` : `<div class="empty-state"><i class="fa-solid fa-moon"></i><p>Nenhum pagamento recebido hoje</p></div>`}

    <div style="background:var(--dark3);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;margin-top:1rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:.75rem;">
        <span style="color:var(--text-muted);">Total Recebido Hoje (${todayStr})</span>
        <strong style="font-size:1.3rem;color:var(--gold);">${fmt(totalDia)} Kz</strong>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;">
        <span style="color:var(--text-muted);">Acumulado do MÃªs (${mesNome} ${ano})</span>
        <strong style="font-size:1.1rem;color:var(--info);">${fmt(totalMes)} Kz</strong>
      </div>
      <div style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:.75rem;">
        RelatÃ³rio gerado em ${new Date().toLocaleString('pt-AO')} Â· CondomÃ­nio Nosso Zimbo
      </div>
    </div>
  `;
}

function buildMonthReport() {
  const mes = parseInt(document.getElementById('rel-mes').value);
  const ano = parseInt(document.getElementById('rel-ano').value);
  const mesNome = ['Janeiro','Fevereiro','MarÃ§o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'][mes];

  const rVis = allPays.filter(p => {
    try { const d = new Date(p.data||'2025'); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;}
  });
  const rMor = allMorPays.filter(p => {
    try { const d = new Date(p.data||'2025'); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;}
  });

  const totalVis = rVis.reduce((s,p)=>s+(p.total||0),0);
  const totalMor = rMor.reduce((s,p)=>s+(p.valor||0),0);
  const totalGeral = totalVis + totalMor;

  const reg_mes = allRegs.filter(r => {
    try { const d = new Date(r.data||'2025'); return d.getMonth()===mes && d.getFullYear()===ano; } catch(e){return false;}
  });

  document.getElementById('relatorio-content').innerHTML = `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-calendar-days"></i> Resumo Mensal â€” ${mesNome} ${ano}</p>
      <div class="report-kpi-grid">
        <div class="report-kpi"><div class="kval">${fmt(totalGeral)} Kz</div><div class="klabel">Receita Total do MÃªs</div></div>
        <div class="report-kpi"><div class="kval">${fmt(totalVis)} Kz</div><div class="klabel">Recebido de Visitantes</div></div>
        <div class="report-kpi"><div class="kval">${fmt(totalMor)} Kz</div><div class="klabel">Recebido de Moradores</div></div>
        <div class="report-kpi"><div class="kval">${reg_mes.length}</div><div class="klabel">Novos Registos</div></div>
        <div class="report-kpi"><div class="kval">${rVis.length + rMor.length}</div><div class="klabel">TransacÃ§Ãµes</div></div>
        <div class="report-kpi"><div class="kval">${allHouses.filter(h=>h.estado==='disponivel').length}</div><div class="klabel">Casas DisponÃ­veis</div></div>
      </div>
    </div>

    ${rMor.length ? `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-users"></i> Pagamentos de Moradores â€” ${mesNome}</p>
      <div class="card" style="overflow:auto;">
        <table class="data-table">
          <thead><tr><th>#</th><th>Morador</th><th>Apt</th><th>Tipo</th><th>Valor</th><th>MÃ©todo</th><th>Data</th><th>Estado</th></tr></thead>
          <tbody>
            ${rMor.map((p,i)=>`<tr><td style="color:var(--text-muted);font-size:.78rem;">${i+1}</td><td><strong>${p.nome}</strong></td><td><span style="font-family:monospace;font-size:.8rem;">${p.apt}</span></td><td>${p.tipo}</td><td><strong>${fmt(p.valor)} Kz</strong></td><td>${p.metodo}</td><td>${p.data}</td><td><span class="badge ${p.estado==='confirmado'?'pago':'pendente'}">${p.estado}</span></td></tr>`).join('')}
            <tr style="border-top:2px solid var(--border);"><td colspan="4" style="text-align:right;color:var(--text-muted);font-size:.8rem;font-weight:600;">SUBTOTAL MORADORES</td><td colspan="4"><strong style="color:var(--gold);font-size:.95rem;">${fmt(totalMor)} Kz</strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>` : ''}

    ${reg_mes.length ? `
    <div class="report-section">
      <p class="report-section-title"><i class="fa-solid fa-user-clock"></i> Registos de Visitantes â€” ${mesNome}</p>
      <div class="card" style="overflow:auto;">
        <table class="data-table">
          <thead><tr><th>#</th><th>Nome</th><th>ServiÃ§o</th><th>Valor</th><th>Data</th><th>Estado</th></tr></thead>
          <tbody>
            ${reg_mes.map((r,i)=>`<tr><td style="color:var(--text-muted);font-size:.78rem;">${i+1}</td><td>${r.nome}</td><td>${r.servico==='aluguel'?'Arrendamento':'Compra'}</td><td>${fmt(r.total)} Kz</td><td>${r.data}</td><td><span class="badge ${r.status}">${r.status}</span></td></tr>`).join('')}
            <tr style="border-top:2px solid var(--border);"><td colspan="2" style="text-align:right;color:var(--text-muted);font-size:.8rem;font-weight:600;">SUBTOTAL VISITANTES</td><td colspan="4"><strong style="color:var(--gold);font-size:.95rem;">${fmt(totalVis)} Kz</strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>` : ''}

    ${(!reg_mes.length && !rMor.length) ? '<div class="empty-state"><i class="fa-solid fa-file-circle-xmark"></i><p>Sem dados para o perÃ­odo seleccionado</p></div>' : ''}

    <div style="background:var(--dark3);border:2px solid var(--gold);border-radius:var(--radius);padding:1.25rem;margin-top:1rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:.5rem;">
        <span style="color:var(--text-muted);">Moradores</span>
        <strong style="color:var(--text);">${fmt(totalMor)} Kz</strong>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:.75rem;">
        <span style="color:var(--text-muted);">Visitantes</span>
        <strong style="color:var(--text);">${fmt(totalVis)} Kz</strong>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:700;color:var(--text);">Total Geral â€” ${mesNome} ${ano}</span>
        <strong style="font-size:1.4rem;color:var(--gold);">${fmt(totalGeral)} Kz</strong>
      </div>
      <div style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:.75rem;">
        RelatÃ³rio gerado em ${new Date().toLocaleString('pt-AO')} Â· CondomÃ­nio Nosso Zimbo
      </div>
    </div>
    <div class="pdf-report-footer" style="display:none;margin-top:2rem;padding-top:1rem;border-top:2px solid #c9a84c;font-size:.78rem;color:#666;text-align:center;">
      Este relatÃ³rio Ã© de carÃ¡cter confidencial e destina-se exclusivamente Ã  administraÃ§Ã£o do CondomÃ­nio Nosso Zimbo.<br>
      Emitido em ${new Date().toLocaleString('pt-AO')} Â· PÃ¡gina 1
    </div>
  `;
}

function imprimirRelatorio() {
  // Set print date
  const el = document.getElementById('pdf-gen-date');
  if (el) el.textContent = new Date().toLocaleString('pt-AO');
  window.print();
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// PEDIDOS DE VISITANTES
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
let pedidoFilter = 'todos';
let currentPedidoId = null;
let selectedCasaId = null;
let geradoCodigoPedido = null;

function filterPedidos(f, btn) {
  pedidoFilter = f;
  document.querySelectorAll('#tab-pedidos .filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderPedidos();
}

function loadPedidos() {
  load();
  renderPedidos();
  updateBadgePedidos();
}

function updateBadgePedidos() {
  const pending = allRegs.filter(r => r.status === 'pendente').length;
  const badge = document.getElementById('badge-pedidos');
  if (badge) badge.textContent = pending;
}

function renderPedidos() {
  load();
  const tbody = document.getElementById('pedidos-tbody');
  let regs = [...allRegs].reverse();
  if (pedidoFilter === 'pendente') regs = regs.filter(r => r.status === 'pendente');
  else if (pedidoFilter === 'aprovado') regs = regs.filter(r => r.status === 'aprovado');
  else if (pedidoFilter === 'rejeitado') regs = regs.filter(r => r.status === 'rejeitado');

  if (!regs.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2.5rem;"><i class="fa-solid fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem;color:var(--border);"></i>Nenhum pedido encontrado</td></tr>';
    return;
  }
  tbody.innerHTML = regs.map(r => `
    <tr>
      <td><span class="house-tag" style="font-size:.72rem;">${r.ref || ('NZ-' + r.id)}</span></td>
      <td><strong>${r.nome || 'â€”'}</strong></td>
      <td style="font-family:monospace;font-size:.8rem;">${r.bi || 'â€”'}</td>
      <td>${r.servico === 'aluguel' ? 'ðŸ  Arrendamento' : 'ðŸ”‘ Compra'}</td>
      <td><strong>${fmt(r.total || 0)} Kz</strong></td>
      <td style="font-size:.82rem;">${r.data || 'â€”'}</td>
      <td><span class="badge ${r.status || 'pendente'}">${r.status || 'pendente'}</span></td>
      <td>
        <button class="btn-secondary btn-sm" onclick="abrirModalPedido(${r.id})" title="Rever pedido">
          <i class="fa-solid fa-magnifying-glass"></i> Rever
        </button>
      </td>
    </tr>
  `).join('');
}

function abrirModalPedido(id) {
  load();
  const r = allRegs.find(x => x.id === id);
  if (!r) return;
  currentPedidoId = id;
  selectedCasaId = null;
  geradoCodigoPedido = null;

  // Reset checkboxes
  ['chk-bi','chk-tel','chk-pay','chk-valor','chk-morada','chk-docs'].forEach(c => {
    const el = document.getElementById(c);
    if (el) el.checked = false;
  });
  const notasEl = document.getElementById('pd-notas-admin');
  if (notasEl) notasEl.value = r.notasAdmin || '';

  // Preencher dados pessoais
  document.getElementById('pd-nome-header').textContent = r.nome || 'â€”';
  document.getElementById('pd-ref-header').textContent = 'Ref: ' + (r.ref || r.id);
  const badge = document.getElementById('pd-status-badge');
  badge.textContent = r.status || 'pendente';
  badge.className = 'badge ' + (r.status || 'pendente');

  document.getElementById('pd-nome').textContent = r.nome || 'â€”';
  document.getElementById('pd-bi').textContent = r.bi || 'â€”';
  document.getElementById('pd-nasc').textContent = r.nasc || 'â€”';
  document.getElementById('pd-nac').textContent = r.nac || 'â€”';
  document.getElementById('pd-estado-civil').textContent = r.estado || 'â€”';
  document.getElementById('pd-tel').textContent = r.tel || 'â€”';
  document.getElementById('pd-email').textContent = r.email || 'â€”';
  document.getElementById('pd-morada').textContent = r.morada || 'â€”';
  document.getElementById('pd-profissao').textContent = r.profissao || 'â€”';
  document.getElementById('pd-data').textContent = (r.data || 'â€”') + ' ' + (r.hora || '');

  // Preencher serviÃ§o
  const svcNome = r.servico === 'aluguel' ? 'Arrendamento' : 'Compra de ResidÃªncia';
  document.getElementById('pd-servico-header').textContent = svcNome;
  document.getElementById('pd-servico').textContent = svcNome;
  document.getElementById('pd-modalidade').textContent = r.modalidade || 'â€”';
  document.getElementById('pd-metodo').textContent = r.metodo || 'â€”';
  document.getElementById('pd-valor').textContent = fmt(r.total || 0) + ' Kz';
  document.getElementById('pd-data2').textContent = (r.data || 'â€”') + ' ' + (r.hora || '');
  document.getElementById('pd-codigo').textContent = r.codigo || 'â€”';

  // Preencher select de casas
  const sel = document.getElementById('pd-casa-select');
  sel.innerHTML = '<option value="">â€” Seleccione uma residÃªncia â€”</option>';
  const disponiveis = allHouses.filter(h => h.estado === 'disponivel');
  if (disponiveis.length) {
    disponiveis.forEach(h => {
      sel.innerHTML += `<option value="${h.id}">Bloco ${h.bloco} Â· Rua ${h.rua} Â· NÂº ${h.numero} Â· ${h.tipo} Â· ${h.zona}</option>`;
    });
    document.getElementById('pd-sem-casas').style.display = 'none';
    sel.style.display = 'block';
  } else {
    document.getElementById('pd-sem-casas').style.display = 'block';
    sel.style.display = 'none';
  }
  document.getElementById('pd-casa-preview').style.display = 'none';

  // BotÃµes conforme estado
  const btnAprovar = document.getElementById('btn-aprovar-final');
  const btnRejeitar = document.getElementById('btn-rejeitar');
  const btnEliminar = document.getElementById('btn-eliminar');
  if (r.status === 'aprovado') {
    btnAprovar.disabled = true;
    btnAprovar.innerHTML = '<i class="fa-solid fa-check-double"></i> JÃ¡ aprovado';
    btnRejeitar.style.display = 'none';
    btnEliminar.style.display = 'none';
  } else if (r.status === 'rejeitado') {
    btnAprovar.disabled = true;
    btnAprovar.innerHTML = '<i class="fa-solid fa-ban"></i> Negado';
    btnRejeitar.style.display = 'none';
    btnEliminar.style.display = 'inline-flex';
  } else {
    btnAprovar.disabled = false;
    btnAprovar.innerHTML = '<i class="fa-solid fa-circle-check"></i> Confirmar & Atribuir ResidÃªncia';
    btnRejeitar.style.display = 'inline-flex';
    btnEliminar.style.display = 'inline-flex';
  }

  // Load document images
  function setDocImg(imgId, emptyId, src) {
    const img = document.getElementById(imgId);
    const empty = document.getElementById(emptyId);
    if (src) {
      img.src = src; img.style.display = 'inline-block'; empty.style.display = 'none';
    } else {
      img.src = ''; img.style.display = 'none'; empty.style.display = 'block';
    }
  }
  setDocImg('doc-selfie-img', 'doc-selfie-empty', r.selfie || null);
  setDocImg('doc-bi-frente-img', 'doc-bi-frente-empty', r.biFrenteImg || null);
  setDocImg('doc-bi-verso-img', 'doc-bi-verso-empty', r.biVersoImg || null);
  // Comprovativo â€” could be image or PDF base64
  const compImg = document.getElementById('doc-comprovativo-img');
  const compPdf = document.getElementById('doc-comprovativo-pdf-link');
  const compEmpty = document.getElementById('doc-comprovativo-empty');
  if (r.comprovantivoImg) {
    if (r.comprovantivoImg.startsWith('data:application/pdf')) {
      compImg.style.display = 'none';
      compPdf.style.display = 'block';
      document.getElementById('doc-comprovativo-pdf-a').href = r.comprovantivoImg;
      compEmpty.style.display = 'none';
    } else {
      compImg.src = r.comprovantivoImg; compImg.style.display = 'inline-block';
      compPdf.style.display = 'none'; compEmpty.style.display = 'none';
    }
  } else {
    compImg.src = ''; compImg.style.display = 'none';
    compPdf.style.display = 'none'; compEmpty.style.display = 'block';
  }

  switchPTab('dados');
  document.getElementById('modal-pedido').classList.add('open');
}

function switchPTab(tab) {
  ['dados','docs','servico','casa'].forEach(t => {
    const panel = document.getElementById('ppanel-' + t);
    if (panel) panel.style.display = 'none';
    const btn = document.getElementById('ptab-' + t);
    if (btn) {
      btn.style.background = 'transparent';
      btn.style.color = 'var(--text-muted)';
    }
  });
  const activePanel = document.getElementById('ppanel-' + tab);
  if (activePanel) activePanel.style.display = 'block';
  const activeBtn = document.getElementById('ptab-' + tab);
  if (activeBtn) {
    activeBtn.style.background = 'var(--gold)';
    activeBtn.style.color = '#000';
  }
}

function previewCasaSelecionada() {
  const sel = document.getElementById('pd-casa-select');
  const houseId = parseInt(sel.value);
  if (!houseId) {
    document.getElementById('pd-casa-preview').style.display = 'none';
    selectedCasaId = null;
    return;
  }
  const house = allHouses.find(h => h.id === houseId);
  if (!house) return;
  selectedCasaId = houseId;
  geradoCodigoPedido = String(Math.floor(1000 + Math.random() * 9000));

  document.getElementById('pv-bloco').textContent = house.bloco || 'â€”';
  document.getElementById('pv-rua').textContent = house.rua || 'â€”';
  document.getElementById('pv-numero').textContent = house.numero || 'â€”';
  document.getElementById('pv-tipo').textContent = house.tipo || 'V3';
  document.getElementById('pv-andar').textContent = house.andar || 'â€”';
  document.getElementById('pv-zona').textContent = house.zona || 'â€”';
  document.getElementById('pd-novo-codigo').textContent = geradoCodigoPedido;
  document.getElementById('pd-casa-preview').style.display = 'block';
}

function aprovarPedidoFinal() {
  if (!selectedCasaId) {
    // Ir para aba de casa e avisar
    switchPTab('casa');
    showToast('Seleccione uma residÃªncia para atribuir antes de aprovar', true);
    return;
  }
  const r = allRegs.find(x => x.id === currentPedidoId);
  if (!r) return;
  const house = allHouses.find(h => h.id === selectedCasaId);
  if (!house) { showToast('Casa nÃ£o encontrada', true); return; }

  // Guardar notas do admin
  r.notasAdmin = document.getElementById('pd-notas-admin')?.value || '';

  // Actualizar registo
  const idx = allRegs.findIndex(x => x.id === currentPedidoId);
  allRegs[idx].status = 'aprovado';
  allRegs[idx].codigo = geradoCodigoPedido;
  allRegs[idx].house = { bloco: house.bloco, rua: house.rua, numero: house.numero, tipo: house.tipo, andar: house.andar, zona: house.zona };
  allRegs[idx].notasAdmin = r.notasAdmin;
  allRegs[idx].aprovadoEm = new Date().toLocaleString('pt-AO');

  // Marcar casa como ocupada
  const hIdx = allHouses.findIndex(h => h.id === selectedCasaId);
  if (hIdx >= 0) allHouses[hIdx].estado = 'ocupada';

  // Guardar dados para o portal do visitante
  try {
    localStorage.setItem('nz_pending_house', JSON.stringify({
      bloco: house.bloco, rua: house.rua, numero: house.numero,
      tipo: house.tipo, andar: house.andar, zona: house.zona,
      codigo: geradoCodigoPedido,
      visitante: r.nome,
      aprovadoEm: new Date().toLocaleString('pt-AO')
    }));
  } catch(e) {}

  save();
  closeModal('modal-pedido');

  // Modal de confirmaÃ§Ã£o
  document.getElementById('conf-nome').textContent = r.nome || 'â€”';
  document.getElementById('conf-casa').textContent = `Bloco ${house.bloco} Â· Rua ${house.rua} Â· NÂº ${house.numero}`;
  document.getElementById('conf-codigo').textContent = geradoCodigoPedido;
  document.getElementById('modal-confirmacao').classList.add('open');

  renderPedidos();
  renderRegistos();
  renderDashboard();
  updateBadgePedidos();
}

function abrirNegarPedido() {
  if (!currentPedidoId) return;
  // Reset modal
  document.getElementById('negar-motivo-texto').value = '';
  document.querySelectorAll('.negar-reason-btn').forEach(b => b.classList.remove('selected'));
  closeModal('modal-pedido');
  document.getElementById('modal-negar').classList.add('open');
}

function selecionarMotivo(btn, texto) {
  document.querySelectorAll('.negar-reason-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('negar-motivo-texto').value = texto;
  document.getElementById('negar-motivo-texto').focus();
}

function confirmarNegar() {
  const motivo = document.getElementById('negar-motivo-texto').value.trim();
  if (!motivo) {
    document.getElementById('negar-motivo-texto').style.borderColor = 'var(--danger)';
    document.getElementById('negar-motivo-texto').focus();
    showToast('Escreva ou seleccione o motivo da negaÃ§Ã£o', true);
    return;
  }
  if (!currentPedidoId) return;
  const idx = allRegs.findIndex(x => x.id === currentPedidoId);
  if (idx >= 0) {
    allRegs[idx].status = 'rejeitado';
    allRegs[idx].motivoRejeicao = motivo;
    allRegs[idx].rejeitadoEm = new Date().toLocaleString('pt-AO');
    allRegs[idx].notasAdmin = document.getElementById('pd-notas-admin')?.value || '';
    // Guardar no localStorage para o visitante ler
    try {
      const myId = allRegs[idx].id;
      const notifKey = 'nz_notif_' + myId;
      localStorage.setItem(notifKey, JSON.stringify({
        status: 'rejeitado',
        motivo: motivo,
        rejeitadoEm: allRegs[idx].rejeitadoEm
      }));
    } catch(e) {}
  }
  save();
  closeModal('modal-negar');
  renderPedidos();
  renderDashboard();
  updateBadgePedidos();
  showToast('Pedido negado. O visitante verÃ¡ o motivo no seu portal.');
}

// Legacy alias
function rejeitarPedido() { abrirNegarPedido(); }

// â”€â”€ LIGHTBOX â”€â”€
function openLightbox(src, label) {
  if (!src) return;
  document.getElementById('lightbox-img').src = src;
  document.getElementById('lightbox-label').textContent = label || 'Documento';
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.getElementById('lightbox-img').src = '';
  document.body.style.overflow = '';
}
// ESC to close lightbox
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeLightbox();
    closeModal('modal-negar');
  }
});

function eliminarPedido() {
  if (!currentPedidoId) return;
  if (!confirm('Tem a certeza que deseja ELIMINAR permanentemente este pedido? Esta acÃ§Ã£o nÃ£o pode ser revertida.')) return;
  allRegs = allRegs.filter(x => x.id !== currentPedidoId);
  // Remove from payments too
  allPays = allPays.filter(x => x.id !== currentPedidoId && x.id !== currentPedidoId + 1);
  save();
  closeModal('modal-pedido');
  renderPedidos();
  renderRegistos();
  renderDashboard();
  updateBadgePedidos();
  showToast('Pedido eliminado permanentemente.');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// HELPERS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function fmt(n) { return new Intl.NumberFormat('pt-AO').format(n||0); }
function showToast(msg, err) {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast' + (err?' error':'') + ' show';
  setTimeout(()=>t.classList.remove('show'), 3000);
}
function triggerFile(id) { document.getElementById(id).click(); }
function fileSelected(id, input) {
  const area = document.getElementById(id + '-area');
  if (input.files[0]) { area.classList.add('uploaded'); area.querySelector('p').textContent = 'âœ“ ' + input.files[0].name; }
}

