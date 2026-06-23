/**
 * ============================================================================
 * ADMIN_DASHBOARD.JS — Dashboard do Administrador
 * ============================================================================
 * Sistema de gestão do painel administrativo
 * - Carrega dados reais da base de dados via API
 * - Exibe KPIs, gráficos, tabelas de moradores, pagamentos, etc.
 * - Gerencia navegação entre abas
 * - Integração completa com Backend PHP
 */

// ─────────────────────────────────────────────────────────────────────────
// VARIÁVEIS GLOBAIS
// ─────────────────────────────────────────────────────────────────────────

let graficoBudget = null;
let graficoDistribuicao = null;
let graficos = {};

// ─────────────────────────────────────────────────────────────────────────
// INICIALIZAÇÃO DO DASHBOARD
// ─────────────────────────────────────────────────────────────────────────

/**
 * Função: Inicializar o dashboard
 * Executa na carga da página — carrega dados do servidor
 */
window.onload = () => {
    console.log("🟢 Dashboard carregando...");
    
    // Atualizar relógio
    atualizarRelogio();
    setInterval(atualizarRelogio, 1000);
    
    // Atualizar data
    atualizarData();
    
    // Carregar dados do servidor
    carregarResumo();
    carregarMoradores();
    carregarApartamentos();
    carregarMensalidades();
    carregarPagamentos();
    
    // Inicializar gráficos
    inicializarGraficos();
    
    console.log("✅ Dashboard carregado com sucesso");
};

// ─────────────────────────────────────────────────────────────────────────
// FUNÇÕES DE CHAMADAS À API
// ─────────────────────────────────────────────────────────────────────────

/**
 * Função: Buscar dados resumidos do dashboard
 * GET: api/api_dashboard.php?acao=resumo
 */
function carregarResumo() {
    fetch('api/api_dashboard.php?acao=resumo')
        .then(res => res.json())
        .then(data => {
            if (data.sucesso && data.dados) {
                console.log("✅ Resumo carregado:", data.dados);
                
                // Atualizar elementos do HTML
                document.getElementById('ds-moradores').textContent = data.dados.total_moradores || 0;
                document.getElementById('ds-admins').textContent = data.dados.total_admins || 0;
                document.getElementById('ds-apartamentos').textContent = data.dados.total_apartamentos || 0;
                document.getElementById('ds-disponiveis').textContent = data.dados.apartamentos_disponiveis || 0;
                document.getElementById('ds-pendentes').textContent = data.dados.mensalidades_pendentes || 0;
                document.getElementById('ds-receitas').textContent = 
                    formatarMoeda(data.dados.receitas_mes) + ' Kz';
            }
        })
        .catch(err => console.error("❌ Erro ao carregar resumo:", err));
}

/**
 * Função: Buscar lista de moradores
 * GET: api/api_dashboard.php?acao=moradores
 */
function carregarMoradores() {
    fetch('api/api_dashboard.php?acao=moradores')
        .then(res => res.json())
        .then(data => {
            if (data.sucesso && data.dados) {
                console.log("✅ Moradores carregados:", data.dados.length);
                renderizarTabelaMoradores(data.dados);
            }
        })
        .catch(err => console.error("❌ Erro ao carregar moradores:", err));
}

/**
 * Função: Buscar lista de apartamentos
 * GET: api/api_dashboard.php?acao=casas
 */
function carregarApartamentos() {
    fetch('api/api_dashboard.php?acao=casas')
        .then(res => res.json())
        .then(data => {
            if (data.sucesso && data.dados) {
                console.log("✅ Apartamentos carregados:", data.dados.length);
                renderizarTabelaApartamentos(data.dados);
            }
        })
        .catch(err => console.error("❌ Erro ao carregar apartamentos:", err));
}

/**
 * Função: Buscar lista de mensalidades
 * GET: api/api_dashboard.php?acao=mensalidades
 */
function carregarMensalidades() {
    fetch('api/api_dashboard.php?acao=mensalidades')
        .then(res => res.json())
        .then(data => {
            if (data.sucesso && data.dados) {
                console.log("✅ Mensalidades carregadas:", data.dados.length);
                renderizarTabelaMensalidades(data.dados);
            }
        })
        .catch(err => console.error("❌ Erro ao carregar mensalidades:", err));
}

/**
 * Função: Buscar lista de pagamentos
 * GET: api/api_dashboard.php?acao=pagamentos
 */
function carregarPagamentos() {
    fetch('api/api_dashboard.php?acao=pagamentos')
        .then(res => res.json())
        .then(data => {
            if (data.sucesso && data.dados) {
                console.log("✅ Pagamentos carregados:", data.dados.length);
                renderizarTabelaPagamentos(data.dados);
            }
        })
        .catch(err => console.error("❌ Erro ao carregar pagamentos:", err));
}

// ─────────────────────────────────────────────────────────────────────────
// FUNÇÕES DE RENDERIZAÇÃO
// ─────────────────────────────────────────────────────────────────────────

/**
 * Função: Renderizar tabela de moradores
 * @param {Array} moradores - Lista de moradores do servidor
 */
function renderizarTabelaMoradores(moradores) {
    const tbody = document.getElementById('tabela-moradores-tbody');
    if (!tbody) return;
    
    if (moradores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:2rem;">Nenhum morador registado</td></tr>';
        return;
    }
    
    tbody.innerHTML = moradores.map(m => `
        <tr>
            <td><strong>${htmlEscape(m.nome)}</strong></td>
            <td>${m.numbi || '—'}</td>
            <td>${m.telefone || '—'}</td>
            <td>${m.email || '—'}</td>
            <td><span class="badge ${m.estado_conta === 'Activo' ? 'sucesso' : 'atencao'}">${m.estado_conta}</span></td>
            <td>${m.apartamento || '—'}</td>
        </tr>
    `).join('');
}

/**
 * Função: Renderizar tabela de apartamentos
 * @param {Array} apartamentos - Lista de apartamentos do servidor
 */
function renderizarTabelaApartamentos(apartamentos) {
    const tbody = document.getElementById('tabela-apartamentos-tbody');
    if (!tbody) return;
    
    if (apartamentos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">Nenhum apartamento registado</td></tr>';
        return;
    }
    
    tbody.innerHTML = apartamentos.map(a => `
        <tr>
            <td><strong>${a.bloco}-${a.numero}</strong></td>
            <td>${a.andar || '0'}</td>
            <td>${a.tipologia || '—'}</td>
            <td>${a.codigo || '—'}</td>
            <td><span class="badge ${a.estado === 'Disponivel' ? 'info' : a.estado === 'Ocupado' ? 'sucesso' : 'atencao'}">${a.estado}</span></td>
        </tr>
    `).join('');
}

/**
 * Função: Renderizar tabela de mensalidades
 * @param {Array} mensalidades - Lista de mensalidades do servidor
 */
function renderizarTabelaMensalidades(mensalidades) {
    const tbody = document.getElementById('tabela-mensalidades-tbody');
    if (!tbody) return;
    
    if (mensalidades.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">Nenhuma mensalidade registada</td></tr>';
        return;
    }
    
    tbody.innerHTML = mensalidades.slice(0, 20).map(m => `
        <tr>
            <td><strong>${htmlEscape(m.nome)}</strong></td>
            <td>${m.apartamento || '—'}</td>
            <td>${m.servico || '—'}</td>
            <td>${formatarMoeda(m.valor)} Kz</td>
            <td>${formatarData(m.vencimento)}</td>
            <td><span class="badge ${m.estado === 'pago' ? 'sucesso' : m.estado === 'pendente' ? 'atencao' : 'erro'}">${m.estado}</span></td>
        </tr>
    `).join('');
}

/**
 * Função: Renderizar tabela de pagamentos
 * @param {Array} pagamentos - Lista de pagamentos do servidor
 */
function renderizarTabelaPagamentos(pagamentos) {
    const tbody = document.getElementById('tabela-pagamentos-tbody');
    if (!tbody) return;
    
    if (pagamentos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">Nenhum pagamento registado</td></tr>';
        return;
    }
    
    tbody.innerHTML = pagamentos.slice(0, 15).map(p => `
        <tr>
            <td><strong>${htmlEscape(p.morador)}</strong></td>
            <td>${p.apartamento || '—'}</td>
            <td>${formatarMoeda(p.valor_pago)} Kz</td>
            <td>${p.metodo || '—'}</td>
            <td>${formatarData(p.data_pagamento)}</td>
            <td><span class="badge ${p.estado === 'confirmado' ? 'sucesso' : p.estado === 'pendente' ? 'atencao' : 'erro'}">${p.estado}</span></td>
        </tr>
    `).join('');
}

// ─────────────────────────────────────────────────────────────────────────
// FUNÇÕES DE NAVEGAÇÃO
// ─────────────────────────────────────────────────────────────────────────

/**
 * Função: Mudar de aba (abas do dashboard)
 * @param {string} id - Identificador da aba (ex: 'dashboard', 'moradores', etc)
 * @param {HTMLElement} btn - Botão clicado (para aplicar estilos)
 */
function switchTab(id, btn) {
    // Remover classe "active" de todas as abas e botões
    document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    
    // Adicionar "active" à aba e botão clicado
    const tab = document.getElementById('tab-' + id);
    if (tab) tab.classList.add('active');
    if (btn) btn.classList.add('active');
    
    console.log(`📑 Alterando para aba: ${id}`);
}

/**
 * Função: Alternar sidebar (menu lateral)
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
        console.log("☰ Sidebar alternado");
    }
}

// ─────────────────────────────────────────────────────────────────────────
// FUNÇÕES AUXILIARES
// ─────────────────────────────────────────────────────────────────────────

/**
 * Função: Atualizar relógio em tempo real
 */
function atualizarRelogio() {
    const agora = new Date();
    const el = document.getElementById('clock-display');
    if (el) {
        el.textContent = agora.toLocaleTimeString('pt-AO');
    }
}

/**
 * Função: Atualizar data do painel
 */
function atualizarData() {
    const agora = new Date();
    const el = document.getElementById('dash-date');
    if (el) {
        const opcoes = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        el.textContent = agora.toLocaleDateString('pt-AO', opcoes);
    }
}

/**
 * Função: Formatar valor monetário
 * @param {number} valor - Valor a formatar
 * @returns {string} Valor formatado (ex: "14 000,00")
 */
function formatarMoeda(valor) {
    if (!valor) return '0,00';
    return new Intl.NumberFormat('pt-AO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(parseFloat(valor));
}

/**
 * Função: Formatar data
 * @param {string} data - Data em formato YYYY-MM-DD ou ISO
 * @returns {string} Data formatada (ex: "23/06/2026")
 */
function formatarData(data) {
    if (!data) return '—';
    const d = new Date(data);
    return d.toLocaleDateString('pt-AO');
}

/**
 * Função: Escapar HTML para evitar XSS
 * @param {string} texto - Texto a escapar
 * @returns {string} Texto seguro
 */
function htmlEscape(texto) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return texto.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Função: Inicializar gráficos (placeholder para Chart.js)
 */
function inicializarGraficos() {
    // Exemplo: Gráfico de receitas mensais
    const ctx = document.getElementById('chart-receitas');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [{
                    label: 'Receitas (Kz)',
                    data: [500000, 520000, 510000, 540000, 560000, 580000],
                    borderColor: '#c9a84c',
                    backgroundColor: 'rgba(201, 168, 76, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
}

console.log("✅ admin_dashboard.js carregado com sucesso");
