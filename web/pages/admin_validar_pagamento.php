<?php
session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    header("Location: ../login.html?erro=acesso");
    exit;
}
$admin_nome = $_SESSION['nome'] ?? 'Admin';
$admin_id   = $_SESSION['id']  ?? 0;
include(__DIR__ . '/../api/conexao.php');
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Pagamento Presencial — Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="../js/theme-manager.js"></script>
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .search-bar {
            display: flex; gap: .6rem; margin-bottom: 1.5rem; flex-wrap: wrap;
        }
        .search-bar input {
            flex: 1; min-width: 280px; padding: .75rem 1rem; border-radius: 12px;
            border: 1.5px solid var(--border); background: var(--bg); color: var(--text); font-size: .95rem;
        }
        .detail-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;
        }
        .detail-item {
            background: var(--bg); border-radius: 12px; padding: 1rem; border: 1px solid var(--border);
        }
        .detail-item label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); display: block; margin-bottom: .2rem; }
        .detail-item div { font-weight: 700; font-size: .95rem; }
        .btn-validate {
            background: linear-gradient(135deg, #27ae60, #219a52); color: #fff; border: none;
            padding: .8rem 1.5rem; border-radius: 12px; font-weight: 700; cursor: pointer; font-size: .9rem;
            display: inline-flex; align-items: center; gap: .5rem; transition: all .2s;
        }
        .btn-validate:hover { opacity: .9; transform: translateY(-2px); }
        .card-print {
            background: var(--surface); border-radius: 18px; padding: 2.5rem; max-width: 700px; margin: 0 auto;
            box-shadow: 0 12px 40px rgba(0,0,0,.12); border: 1px solid var(--border);
        }
        .print-header {
            text-align: center; border-bottom: 2px solid var(--gold); padding-bottom: 1.25rem; margin-bottom: 1.5rem;
        }
        .print-header h2 { margin: .3rem 0; font-size: 1.3rem; }
        .print-header p { margin: .2rem 0; color: var(--text-muted); font-size: .85rem; }
        .print-row {
            display: flex; justify-content: space-between; padding: .6rem 0; border-bottom: 1px dashed var(--border); font-size: .9rem;
        }
        .print-row .key { color: var(--text-muted); }
        .print-row .val { font-weight: 700; }
        .print-footer {
            margin-top: 2rem; padding-top: 1rem; border-top: 2px solid var(--gold);
            display: flex; justify-content: space-between; font-size: .8rem; color: var(--text-muted);
        }
        .stamp-box {
            margin-top: 1.5rem; padding: 1rem; border: 3px solid #27ae60; border-radius: 14px;
            text-align: center; color: #27ae60; font-weight: 800; font-size: 1.1rem;
        }
        @media print {
            body * { visibility: hidden; }
            .card-print, .card-print * { visibility: visible; }
            .card-print { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
<?php include('sidebar_admin.php'); ?>

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="topbar-title"><i class="fa-solid fa-file-invoice-dollar"></i> Validar Pagamento Presencial</span>
        <div class="topbar-right">
            <button class="btn-primary" onclick="window.print()" style="font-size:.8rem;"><i class="fa-solid fa-print"></i> Imprimir</button>
        </div>
    </header>

    <div style="padding: 24px;">
        <div class="page-header">
            <div>
                <h1 class="page-title">Validação por Referência</h1>
                <p class="page-sub">Insira a referência do pagamento presencial para validar e emitir comprovativo.</p>
            </div>
        </div>

        <div class="search-bar">
            <input type="text" id="ref-input" placeholder="Ex: PRES260701A1B2C3D3 ou MX260701E4F5G6" />
            <button class="btn-primary" onclick="buscarPorReferencia()"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
        </div>

        <div id="resultado-validacao"></div>
    </div>
</main>

<div class="toast" id="toast"></div>

<script>
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }
function showToast(msg, isErr=false) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.className = 'toast show' + (isErr ? ' error' : '');
    setTimeout(() => t.classList.remove('show'), 3500);
}
function escHtml(s) { if(!s) return ''; return s.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

async function buscarPorReferencia() {
    const ref = document.getElementById('ref-input').value.trim();
    if (!ref) { showToast('Insira a referência', true); return; }

    const res = await fetch('../api/api_dashboard.php?acao=buscar_por_referencia&ref=' + encodeURIComponent(ref));
    const data = await res.json();
    const el = document.getElementById('resultado-validacao');

    if (!data.sucesso || !data.dados) {
        el.innerHTML = `<div class="card" style="padding:1.5rem; text-align:center; color:var(--danger);">
            <i class="fa-solid fa-circle-xmark" style="font-size:2rem;"></i>
            <p style="margin-top:.5rem;">Nenhum pagamento encontrado para esta referência.</p>
        </div>`;
        return;
    }

    const p = data.dados;
    const dt = p.data_pagamento ? new Date(p.data_pagamento).toLocaleString('pt-AO') : '—';
    el.innerHTML = `
        <div class="card-print" id="recibo-card">
            <div class="print-header">
                <div style="font-size:2rem; color:var(--gold);"><i class="fa-solid fa-building-columns"></i></div>
                <h2>Comprovativo de Pagamento</h2>
                <p>Condomínio Nosso Zimbo</p>
                <p>Ref: <strong>${escHtml(p.referencia)}</strong></p>
            </div>
            <div class="print-row"><span class="key">Morador</span><span class="val">${escHtml(p.morador)}</span></div>
            <div class="print-row"><span class="key">Apartamento</span><span class="val">${escHtml(p.apartamento)}</span></div>
            <div class="print-row"><span class="key">Serviço</span><span class="val">${escHtml(p.servico)}</span></div>
            <div class="print-row"><span class="key">Valor Pago</span><span class="val" style="color:#27ae60;">Kz ${parseFloat(p.valor_pago).toLocaleString('pt-AO', {minimumFractionDigits:2})}</span></div>
            <div class="print-row"><span class="key">Método</span><span class="val">${escHtml(p.metodo)}</span></div>
            <div class="print-row"><span class="key">Data/Hora</span><span class="val">${dt}</span></div>
            <div class="print-row"><span class="key">Estado</span><span class="val"><span class="badge badge-validated">${escHtml(p.estado)}</span></span></div>
            <div class="stamp-box"><i class="fa-solid fa-circle-check"></i> PAGAMENTO VALIDADO</div>
            <div class="print-footer">
                <span>Emitido em: ${new Date().toLocaleString('pt-AO')}</span>
                <span>Assinatura: _____________________</span>
            </div>
        </div>
        <div style="text-align:center; margin-top:1rem;">
            <button class="btn-validate" onclick="validarPagamento(${p.id})"><i class="fa-solid fa-check-double"></i> Marcar como Confirmado</button>
        </div>
    `;
}

async function validarPagamento(idPagamento) {
    if (!confirm('Confirmar este pagamento como válido?')) return;
    const fd = new FormData();
    fd.append('acao', 'confirmar_pagamento');
    fd.append('id', idPagamento);
    fd.append('estado', 'confirmado');
    fd.append('notas', 'Validado presencialmente');

    const res = await fetch('../api/api_dashboard.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.sucesso) {
        showToast('Pagamento confirmado com sucesso!');
        document.getElementById('ref-input').value = '';
        document.getElementById('resultado-validacao').innerHTML = '';
    } else {
        showToast(data.erro || 'Erro ao confirmar', true);
    }
}

document.getElementById('ref-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') buscarPorReferencia();
});
</script>
</body>
</html>
