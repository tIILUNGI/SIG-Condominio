<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: minhas_mensalidades.php?erro=id_invalido");
    exit;
}

// Buscar detalhes da mensalidade
$stmt = $conexao->prepare("
    SELECT m.*, a.numero as apartamento, bl.letra as bloco, a.codigo as apt_codigo
    FROM mensalidade m
    LEFT JOIN apartamento a ON m.id_apartamento = a.id
    LEFT JOIN bloco bl ON a.id_bloco = bl.id
    WHERE m.id = ? AND m.id_morador = ?
");
$stmt->bind_param("ii", $id, $_SESSION['id']);
$stmt->execute();
$res = $stmt->get_result();
$m = $res->fetch_assoc();

if (!$m) {
    header("Location: minhas_mensalidades.php?erro=nao_encontrada");
    exit;
}

$nome_mes = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$mes_txt  = $nome_mes[(int)$m['mes'] - 1] ?? 'Mês ' . $m['mes'];
$nome     = $_SESSION['nome'] ?? 'Morador';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pagar Mensalidade — Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        :root {
            --mx-red: #E30613;
            --mx-red-dark: #b5040f;
            --mx-green: #009E60;
            --mx-blue: #003087;
        }

        body { background: var(--bg, #f0f2f5); }
        [data-theme="dark"] body { background: #111; }

        .pay-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .pay-container { width: 100%; max-width: 500px; }

        /* ── Back link ── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            color: var(--text-muted, #888);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            transition: color .2s;
        }
        .back-link:hover { color: var(--mx-red); }

        /* ── METHOD TABS ── */
        .method-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: .6rem;
            margin-bottom: 1.5rem;
        }
        .method-tab {
            background: var(--surface, #fff);
            border: 2px solid var(--border, #ddd);
            border-radius: 14px;
            padding: 1rem .5rem;
            text-align: center;
            cursor: pointer;
            transition: all .25s;
        }
        .method-tab:hover    { border-color: var(--mx-red); transform: translateY(-2px); }
        .method-tab.active   { border-color: var(--mx-red); background: rgba(227,6,19,.06); }
        .method-tab .mt-icon { font-size: 1.6rem; margin-bottom: .4rem; display: block; }
        .method-tab .mt-label{ font-size: .7rem; font-weight: 700; color: var(--text-muted, #666); }
        .method-tab.active .mt-label { color: var(--mx-red); }

        /* ── MAIN CARD ── */
        .pay-card {
            background: var(--surface, #fff);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,.12);
        }

        /* ── HEADER VARIANTS ── */
        .mx-header {
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        .mx-header.red   { background: linear-gradient(135deg, #E30613 0%, #a00010 100%); }
        .mx-header.blue  { background: linear-gradient(135deg, #1a3c8f 0%, #0e2460 100%); }
        .mx-header.gray  { background: linear-gradient(135deg, #444 0%, #222 100%); }
        .mx-header::before {
            content: '';
            position: absolute; right: -30px; top: -30px;
            width: 120px; height: 120px;
            background: rgba(255,255,255,.08); border-radius: 50%;
        }
        .mx-header::after {
            content: '';
            position: absolute; right: 20px; bottom: -40px;
            width: 90px; height: 90px;
            background: rgba(255,255,255,.06); border-radius: 50%;
        }
        .mx-logo {
            width: 52px; height: 52px;
            background: #fff; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,.2);
        }
        .mx-header-text h2 { color:#fff; font-size:1.1rem; margin:0; font-weight:800; }
        .mx-header-text p  { color:rgba(255,255,255,.8); font-size:.78rem; margin:.1rem 0 0; }

        /* ── BODY ── */
        .pay-body { padding: 1.75rem 2rem; }

        .amount-pill {
            border-radius: 14px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .amount-pill.green { background:linear-gradient(135deg,#f0f9f0,#e8f5e9); border:2px solid var(--mx-green); }
        .amount-pill.blue  { background:linear-gradient(135deg,#eff4ff,#e0ebff); border:2px solid var(--mx-blue); }
        .amount-pill.gray  { background:var(--dark4,#f5f5f5); border:2px solid var(--border,#ddd); }
        [data-theme="dark"] .amount-pill.green { background: rgba(0,158,96,.08); }
        [data-theme="dark"] .amount-pill.blue  { background: rgba(0,48,135,.08); }
        .amount-pill .ap-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; display:block; }
        .amount-pill .ap-value { font-size:1.6rem; font-weight:900; }
        .amount-pill.green .ap-label { color: var(--mx-green); }
        .amount-pill.green .ap-value { color: var(--mx-green); }
        .amount-pill.blue  .ap-label { color: var(--mx-blue); }
        .amount-pill.blue  .ap-value { color: var(--mx-blue); }
        .amount-pill.gray  .ap-label { color: var(--text-muted,#666); }
        .amount-pill.gray  .ap-value { color: var(--text,#222); }

        .info-row {
            display:flex; justify-content:space-between; align-items:center;
            padding:.6rem 0; border-bottom:1px solid var(--border,#eee); font-size:.87rem;
        }
        .info-row:last-child { border-bottom:none; }
        .info-row .ir-key   { color:var(--text-muted,#888); }
        .info-row .ir-val   { font-weight:700; color:var(--text,#222); }
        .divider { margin:1.25rem 0; border:none; border-top:1px dashed var(--border,#ddd); }

        /* ── PHONE INPUT ── */
        .phone-wrapper {
            display:flex; align-items:center; gap:.5rem;
            background:var(--dark3,#f5f5f5); border:2px solid var(--border,#ddd);
            border-radius:12px; padding:.75rem 1rem; margin-bottom:1.25rem;
            transition:border-color .2s;
        }
        .phone-wrapper:focus-within { border-color:var(--mx-red); }
        .phone-flag   { font-size:1.3rem; }
        .phone-prefix { font-weight:800; font-size:.95rem; color:var(--text,#222); white-space:nowrap; }
        .phone-wrapper input {
            border:none; background:transparent; outline:none;
            font-size:1rem; font-weight:700; color:var(--text,#222);
            width:100%; letter-spacing:.08em;
        }
        .phone-wrapper input::placeholder { color:var(--text-muted,#bbb); font-weight:400; letter-spacing:0; }

        /* ── PIN ── */
        .pin-section { display:none; animation:fadeIn .3s ease; }
        .pin-section.show { display:block; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

        .pin-dots { display:flex; gap:.75rem; justify-content:center; margin:1.25rem 0; }
        .pin-dot { width:14px; height:14px; border-radius:50%; background:var(--border,#ddd); transition:background .2s; }
        .pin-dot.filled { background:var(--mx-red); transform:scale(1.15); }

        .pin-keypad {
            display:grid; grid-template-columns:repeat(3,1fr); gap:.6rem; margin-bottom:1.25rem;
        }
        .pin-key {
            background:var(--dark4,#f5f5f5); border:1.5px solid var(--border,#e0e0e0);
            border-radius:12px; padding:1rem; font-size:1.2rem; font-weight:700;
            cursor:pointer; text-align:center; transition:all .15s; color:var(--text,#222);
            line-height:1.1;
        }
        .pin-key:hover  { background:rgba(227,6,19,.08); border-color:var(--mx-red); }
        .pin-key:active { transform:scale(.92); background:var(--mx-red); color:#fff; border-color:var(--mx-red); }
        .pin-key.delete { color:var(--mx-red); font-size:.9rem; }
        .pin-key.zero   { grid-column:2; }
        .pin-key small  { font-size:.48rem; display:block; color:var(--text-muted,#999); font-weight:400; }

        /* ── BUTTONS ── */
        .btn-mx {
            width:100%; padding:1rem; border:none; border-radius:14px;
            font-size:1rem; font-weight:800; cursor:pointer; transition:all .25s;
            letter-spacing:.03em; display:flex; align-items:center; justify-content:center; gap:.6rem;
        }
        .btn-mx.red  { background:linear-gradient(135deg,var(--mx-red),var(--mx-red-dark)); color:#fff; }
        .btn-mx.blue { background:linear-gradient(135deg,#1a3c8f,#0e2460); color:#fff; }
        .btn-mx.dark { background:linear-gradient(135deg,#444,#222); color:#fff; }
        .btn-mx:hover { opacity:.9; transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.2); }
        .btn-mx:disabled { opacity:.5; cursor:not-allowed; transform:none !important; }

        .btn-cancel {
            width:100%; padding:.8rem; background:transparent;
            color:var(--text-muted,#888); border:1.5px solid var(--border,#ddd);
            border-radius:12px; font-size:.88rem; cursor:pointer; margin-top:.75rem; transition:all .2s;
        }
        .btn-cancel:hover { border-color:var(--mx-red); color:var(--mx-red); }

        /* ── SUCCESS ── */
        .success-screen { display:none; text-align:center; padding:2rem; animation:fadeIn .5s ease; }
        .success-screen.show { display:block; }
        .success-ring {
            width:80px; height:80px;
            background:linear-gradient(135deg,var(--mx-green),#007a48);
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            font-size:2rem; color:#fff; margin:0 auto 1.25rem;
            animation:pop .4s cubic-bezier(.175,.885,.32,1.275);
        }
        @keyframes pop { from{transform:scale(0)} to{transform:scale(1)} }
        .success-ref {
            background:var(--dark4,#f5f5f5); border-radius:10px;
            padding:.75rem 1.25rem; font-family:monospace; font-size:1rem; font-weight:700;
            color:var(--text,#222); margin:1rem 0; letter-spacing:.08em;
        }

        /* ── IBAN BOX ── */
        .iban-box {
            background:rgba(100,149,237,.07); border:1.5px dashed #6495ed;
            padding:1.25rem 1.5rem; border-radius:14px; margin-bottom:1.25rem;
            position:relative;
        }
        .iban-box label { font-size:.7rem; color:var(--text-muted,#888); font-weight:700; text-transform:uppercase; display:block; margin-bottom:.3rem; }
        .iban-val { font-family:monospace; font-size:1.05rem; font-weight:800; color:var(--text,#222); }
        .copy-btn {
            position:absolute; right:1rem; top:50%; transform:translateY(-50%);
            background:none; border:none; color:#6495ed; cursor:pointer; font-size:1.1rem; transition:color .2s;
        }
        .copy-btn:hover { color:var(--mx-green); }

        /* ── ATM REF ── */
        .ref-entity { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem; }
        .ref-box { background:var(--dark4,#f5f5f5); border-radius:12px; padding:1rem; text-align:center; }
        .ref-box label { font-size:.68rem; color:var(--text-muted,#888); font-weight:700; text-transform:uppercase; display:block; margin-bottom:.4rem; }
        .ref-box .ref-num { font-family:monospace; font-size:1.4rem; font-weight:900; color:var(--mx-red); }
        .ref-box .ref-num.blue { color:var(--mx-blue); }
        .ref-box .ref-num.green { color:var(--mx-green); }

        /* ── SECURITY NOTE ── */
        .security-note {
            display:flex; align-items:center; gap:.6rem;
            background:rgba(0,158,96,.06); border:1px solid rgba(0,158,96,.2);
            border-radius:10px; padding:.75rem 1rem; font-size:.77rem; color:var(--text-muted,#666);
            margin-top:1rem; line-height:1.5;
        }
        .security-note i { color:var(--mx-green); flex-shrink:0; }

        /* ── UPLOAD ZONE ── */
        .upload-zone {
            background:var(--dark3,#f5f5f5); border:2px dashed var(--border,#ddd);
            border-radius:14px; padding:1.5rem; text-align:center; cursor:pointer;
            margin-bottom:1rem; transition:border-color .2s;
        }
        .upload-zone:hover { border-color:var(--mx-green); }
        .upload-zone i { font-size:1.8rem; color:var(--text-muted,#aaa); margin-bottom:.5rem; display:block; }
        .upload-zone p { margin:0; font-weight:700; font-size:.88rem; }
        .upload-zone small { color:var(--text-muted,#aaa); font-size:.75rem; }

        /* ── PROCESSING OVERLAY ── */
        .processing-overlay {
            display:none; position:fixed; inset:0; background:rgba(0,0,0,.6);
            z-index:9999; align-items:center; justify-content:center; flex-direction:column; gap:1.25rem;
        }
        .processing-overlay.show { display:flex; }
        .proc-card {
            background:#fff; border-radius:20px; padding:2rem 2.5rem; text-align:center;
            min-width:260px; box-shadow:0 20px 60px rgba(0,0,0,.3);
        }
        [data-theme="dark"] .proc-card { background:#1a1a1a; }
        .processing-spinner {
            width:52px; height:52px; margin:0 auto .75rem;
            border:4px solid rgba(227,6,19,.15); border-top-color:var(--mx-red);
            border-radius:50%; animation:spin .7s linear infinite;
        }
        @keyframes spin { to{transform:rotate(360deg)} }
        .proc-card h4 { margin:.3rem 0; font-size:1rem; color:var(--text,#222); }
        .proc-card p  { margin:0; font-size:.82rem; color:var(--text-muted,#888); }
        .proc-steps { display:flex; flex-direction:column; gap:.4rem; margin-top:1rem; }
        .proc-step { display:flex; align-items:center; gap:.5rem; font-size:.8rem; color:var(--text-muted,#aaa); }
        .proc-step.done { color:var(--mx-green); }
        .proc-step i { width:14px; }
    </style>
</head>
<body>
<div class="pay-wrapper">
<div class="pay-container">

    <!-- Back -->
    <a href="minhas_mensalidades.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Voltar às Mensalidades
    </a>

    <!-- Method Tabs -->
    <div class="method-tabs">
        <div class="method-tab active" id="tab-mx" onclick="selectTab('mx')">
            <span class="mt-icon">📱</span>
            <span class="mt-label">Multicaixa Express</span>
        </div>
        <div class="method-tab" id="tab-ref" onclick="selectTab('ref')">
            <span class="mt-icon">🏧</span>
            <span class="mt-label">Referência ATM</span>
        </div>
        <div class="method-tab" id="tab-tf" onclick="selectTab('tf')">
            <span class="mt-icon">🏦</span>
            <span class="mt-label">Transferência</span>
        </div>
    </div>

    <div class="pay-card">

    <!-- ══════════════════════════════════════════════
         MULTICAIXA EXPRESS PANEL
    ══════════════════════════════════════════════ -->
    <div id="panel-mx">
        <div class="mx-header red">
            <div class="mx-logo">💳</div>
            <div class="mx-header-text">
                <h2>Multicaixa Express</h2>
                <p>Pagamento seguro e instantâneo</p>
            </div>
        </div>
        <div class="pay-body">

            <!-- Amount -->
            <div class="amount-pill green">
                <div>
                    <span class="ap-label">Valor a Pagar</span>
                    <div class="ap-value">Kz <?php echo number_format($m['valor'], 0, ',', '.'); ?></div>
                </div>
                <i class="fa-solid fa-shield-halved" style="font-size:1.5rem; color:var(--mx-green); opacity:.8;"></i>
            </div>

            <!-- Info -->
            <div class="info-row"><span class="ir-key">Referência</span><span class="ir-val"><?php echo htmlspecialchars($mes_txt . ' ' . $m['ano']); ?></span></div>
            <div class="info-row"><span class="ir-key">Serviço</span><span class="ir-val"><?php echo htmlspecialchars($m['servico']); ?></span></div>
            <div class="info-row"><span class="ir-key">Titular</span><span class="ir-val"><?php echo htmlspecialchars($nome); ?></span></div>

            <hr class="divider">

            <!-- STEP 1: Phone -->
            <div id="step-phone">
                <p style="font-size:.84rem; color:var(--text-muted); margin-bottom:.75rem; font-weight:500;">
                    <i class="fa-solid fa-mobile-screen-button" style="color:var(--mx-red);"></i>
                    Número de telemóvel associado à sua conta EMIS
                </p>
                <div class="phone-wrapper">
                    <span class="phone-flag">🇦🇴</span>
                    <span class="phone-prefix">+244</span>
                    <input type="tel" id="phone-input" placeholder="9XX XXX XXX" maxlength="9" oninput="this.value=this.value.replace(/\D/g,'').slice(0,9)" />
                </div>
                <button class="btn-mx red" onclick="proceedToPin()">
                    <i class="fa-solid fa-arrow-right"></i> Continuar
                </button>
                <button class="btn-cancel" onclick="location.href='minhas_mensalidades.php'">Cancelar</button>
                <div class="security-note">
                    <i class="fa-solid fa-lock"></i>
                    Transação protegida por encriptação SSL. O seu PIN nunca é armazenado nos nossos servidores.
                </div>
            </div>

            <!-- STEP 2: PIN -->
            <div class="pin-section" id="step-pin">
                <p style="font-size:.88rem; font-weight:700; text-align:center; margin-bottom:.25rem;">Introduza o seu PIN de 4 dígitos</p>
                <p style="font-size:.78rem; color:var(--text-muted); text-align:center; margin-bottom:.75rem;" id="pin-phone-display"></p>
                <div class="pin-dots">
                    <div class="pin-dot" id="d0"></div>
                    <div class="pin-dot" id="d1"></div>
                    <div class="pin-dot" id="d2"></div>
                    <div class="pin-dot" id="d3"></div>
                </div>
                <div class="pin-keypad">
                    <button class="pin-key" onclick="pressPin('1')">1</button>
                    <button class="pin-key" onclick="pressPin('2')">2<small>ABC</small></button>
                    <button class="pin-key" onclick="pressPin('3')">3<small>DEF</small></button>
                    <button class="pin-key" onclick="pressPin('4')">4<small>GHI</small></button>
                    <button class="pin-key" onclick="pressPin('5')">5<small>JKL</small></button>
                    <button class="pin-key" onclick="pressPin('6')">6<small>MNO</small></button>
                    <button class="pin-key" onclick="pressPin('7')">7<small>PQRS</small></button>
                    <button class="pin-key" onclick="pressPin('8')">8<small>TUV</small></button>
                    <button class="pin-key" onclick="pressPin('9')">9<small>WXYZ</small></button>
                    <button class="pin-key delete" onclick="deletePin()"><i class="fa-solid fa-delete-left"></i></button>
                    <button class="pin-key zero" onclick="pressPin('0')">0</button>
                </div>
                <button class="btn-cancel" onclick="backToPhone()"><i class="fa-solid fa-arrow-left"></i> Voltar</button>
            </div>

            <!-- STEP 3: SUCCESS -->
            <div class="success-screen" id="step-success">
                <div class="success-ring"><i class="fa-solid fa-check"></i></div>
                <h3 style="color:var(--mx-green); margin:.4rem 0 .2rem;">Pagamento Efectuado!</h3>
                <p style="font-size:.84rem; color:var(--text-muted); margin:.4rem 0 1rem; line-height:1.6;">O seu pagamento foi processado com sucesso e ficará pendente de confirmação pelo administrador.</p>
                <div class="success-ref" id="success-ref">REF: —</div>
                <div class="info-row"><span class="ir-key">Valor</span><span class="ir-val" style="color:var(--mx-green);">Kz <?php echo number_format($m['valor'], 0, ',', '.'); ?></span></div>
                <div class="info-row"><span class="ir-key">Método</span><span class="ir-val">Multicaixa Express</span></div>
                <div class="info-row"><span class="ir-key">Estado</span><span class="ir-val"><span style="background:#e8f5e9; color:var(--mx-green); padding:2px 10px; border-radius:20px; font-size:.82rem;">Pendente confirmação</span></span></div>
                <div class="info-row"><span class="ir-key">Data</span><span class="ir-val" id="success-date">—</span></div>
                <button class="btn-mx red" style="margin-top:1.5rem;" onclick="location.href='minhas_mensalidades.php'">
                    <i class="fa-solid fa-house"></i> Voltar às Mensalidades
                </button>
            </div>

        </div><!-- /pay-body -->
    </div><!-- /panel-mx -->

    <!-- ══════════════════════════════════════════════
         REFERÊNCIA ATM PANEL
    ══════════════════════════════════════════════ -->
    <div id="panel-ref" style="display:none;">
        <div class="mx-header blue">
            <div class="mx-logo" style="color:var(--mx-blue);">🏧</div>
            <div class="mx-header-text">
                <h2>Referência Multibanco / ATM</h2>
                <p>Pague em qualquer ATM ou homebanking</p>
            </div>
        </div>
        <div class="pay-body">
            <div class="amount-pill blue">
                <div>
                    <span class="ap-label">Valor a Pagar</span>
                    <div class="ap-value">Kz <?php echo number_format($m['valor'], 0, ',', '.'); ?></div>
                </div>
                <i class="fa-solid fa-building-columns" style="font-size:1.4rem; color:var(--mx-blue); opacity:.8;"></i>
            </div>
            <div class="ref-entity">
                <div class="ref-box">
                    <label>Entidade</label>
                    <div class="ref-num blue">20348</div>
                </div>
                <div class="ref-box">
                    <label>Referência</label>
                    <div class="ref-num" id="atm-ref-num">—</div>
                </div>
            </div>
            <div class="ref-box" style="margin-bottom:1.25rem;">
                <label>Montante</label>
                <div class="ref-num green">Kz <?php echo number_format($m['valor'], 0, ',', '.'); ?></div>
            </div>
            <p style="font-size:.8rem; color:var(--text-muted); line-height:1.6; margin-bottom:1.25rem;">
                <i class="fa-solid fa-circle-info" style="color:var(--mx-blue);"></i>
                Use estes dados em qualquer terminal ATM BAI, BFA, BIC ou homebanking. A referência expira em <strong>72 horas</strong>.
            </p>
            <button class="btn-mx blue" onclick="simulateAtmPay()">
                <i class="fa-solid fa-circle-check"></i> Simular Pagamento ATM
            </button>
            <button class="btn-cancel" onclick="location.href='minhas_mensalidades.php'">Cancelar</button>
        </div>
    </div><!-- /panel-ref -->

    <!-- ══════════════════════════════════════════════
         TRANSFERÊNCIA BANCÁRIA PANEL
    ══════════════════════════════════════════════ -->
    <div id="panel-tf" style="display:none;">
        <div class="mx-header gray">
            <div class="mx-logo">🏦</div>
            <div class="mx-header-text">
                <h2>Transferência Bancária</h2>
                <p>BAI · BFA · BIC · BPC e outros bancos</p>
            </div>
        </div>
        <div class="pay-body">
            <div class="amount-pill gray">
                <div>
                    <span class="ap-label">Valor a Transferir</span>
                    <div class="ap-value">Kz <?php echo number_format($m['valor'], 0, ',', '.'); ?></div>
                </div>
            </div>
            <div class="iban-box">
                <label>IBAN (BAI) — Beneficiário</label>
                <div class="iban-val">AO06 0040 0000 1234 5678 9012 3</div>
                <button class="copy-btn" onclick="copyText('AO06 0040 0000 1234 5678 9012 3', this)" title="Copiar IBAN"><i class="fa-regular fa-copy"></i></button>
            </div>
            <div class="info-row"><span class="ir-key">Beneficiário</span><span class="ir-val">Cond. Nosso Zimbo</span></div>
            <div class="info-row"><span class="ir-key">Descritivo / Ref.</span><span class="ir-val"><?php echo htmlspecialchars($mes_txt . ' ' . $m['ano'] . ' · ' . $nome); ?></span></div>
            <hr class="divider">
            <p style="font-size:.8rem; color:var(--text-muted); line-height:1.6; margin-bottom:1.2rem;">
                Após a transferência, envie o comprovativo abaixo para validação. O pagamento será confirmado em até 2 dias úteis.
            </p>
            <form action="../api/upload_recibo_mensalidade.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_mensalidade" value="<?php echo $id; ?>">
                <div class="upload-zone" onclick="document.getElementById('file-recibo').click()">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>Carregar Comprovativo</p>
                    <small>PDF, PNG ou JPG · Máx 5MB</small>
                    <input type="file" id="file-recibo" name="recibo" accept=".pdf,.png,.jpg,.jpeg" required style="display:none;" onchange="showFileName(this)">
                </div>
                <div id="file-name-display" style="font-size:.82rem; color:var(--mx-green); text-align:center; margin-bottom:1rem; display:none;"></div>
                <button type="submit" class="btn-mx dark">
                    <i class="fa-solid fa-paper-plane"></i> Enviar para Aprovação
                </button>
            </form>
            <button class="btn-cancel" onclick="location.href='minhas_mensalidades.php'">Cancelar</button>
        </div>
    </div><!-- /panel-tf -->

    </div><!-- /pay-card -->
</div><!-- /pay-container -->
</div><!-- /pay-wrapper -->

<!-- Processing Overlay -->
<div class="processing-overlay" id="processing-overlay">
    <div class="proc-card">
        <div class="processing-spinner"></div>
        <h4 id="proc-title">A processar pagamento...</h4>
        <p id="proc-sub">Por favor aguarde</p>
        <div class="proc-steps" id="proc-steps">
            <div class="proc-step" id="ps1"><i class="fa-solid fa-circle-notch fa-spin"></i> A verificar dados...</div>
            <div class="proc-step" id="ps2" style="opacity:.4"><i class="fa-regular fa-circle"></i> A contactar banco...</div>
            <div class="proc-step" id="ps3" style="opacity:.4"><i class="fa-regular fa-circle"></i> A confirmar transação...</div>
        </div>
    </div>
</div>

<script>
const MENSALIDADE_ID = <?php echo $id; ?>;
let pinValue   = '';
let phoneValue = '';

/* ── TAB SELECTION ── */
function selectTab(tab) {
    ['mx','ref','tf'].forEach(t => {
        document.getElementById('tab-' + t).classList.toggle('active', t === tab);
        document.getElementById('panel-' + t).style.display = t === tab ? 'block' : 'none';
    });
    if (tab === 'ref') generateAtmRef();
}

/* ── ATM REFERENCE ── */
function generateAtmRef() {
    const ref = String(Math.floor(100000000 + Math.random() * 900000000));
    document.getElementById('atm-ref-num').textContent = ref;
}

/* ── PHONE → PIN ── */
function proceedToPin() {
    phoneValue = document.getElementById('phone-input').value;
    if (phoneValue.length < 9 || !/^9[0-9]{8}$/.test(phoneValue)) {
        document.getElementById('phone-input').style.outline = '2px solid var(--mx-red)';
        setTimeout(() => document.getElementById('phone-input').style.outline = '', 1500);
        return;
    }
    document.getElementById('step-phone').style.display = 'none';
    const pin = document.getElementById('step-pin');
    pin.classList.add('show');
    document.getElementById('pin-phone-display').textContent =
        '+244 ' + phoneValue.slice(0,3) + ' ' + phoneValue.slice(3,6) + ' ' + phoneValue.slice(6);
    pinValue = '';
    updateDots();
}

function backToPhone() {
    document.getElementById('step-pin').classList.remove('show');
    document.getElementById('step-phone').style.display = 'block';
    pinValue = '';
    updateDots();
}

/* ── PIN KEYPAD ── */
function pressPin(digit) {
    if (pinValue.length >= 4) return;
    pinValue += digit;
    updateDots();
    if (pinValue.length === 4) setTimeout(processPayment, 350);
}
function deletePin() {
    pinValue = pinValue.slice(0, -1);
    updateDots();
}
function updateDots() {
    for (let i = 0; i < 4; i++) {
        const d = document.getElementById('d' + i);
        d.classList.toggle('filled', i < pinValue.length);
    }
}

/* ── PROCESS PAYMENT (MC Express) ── */
async function processPayment() {
    const overlay = document.getElementById('processing-overlay');
    overlay.classList.add('show');

    const steps = [
        { id: 'ps1', msg: 'A verificar dados...', delay: 900 },
        { id: 'ps2', msg: 'A contactar banco...', delay: 900 },
        { id: 'ps3', msg: 'A confirmar transação...', delay: 700 },
    ];

    for (let i = 0; i < steps.length; i++) {
        const el = document.getElementById(steps[i].id);
        el.style.opacity = '1';
        el.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin" style="color:var(--mx-red)"></i> ${steps[i].msg}`;
        document.getElementById('proc-title').textContent = steps[i].msg.replace('A ', '').replace('...', '');
        await new Promise(r => setTimeout(r, steps[i].delay));
        el.classList.add('done');
        el.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${steps[i].msg.replace(/A |\.{3}/g, '').trim()} ✓`;
    }

    // Backend call
    try {
        const fd = new FormData();
        fd.append('id_mensalidade', MENSALIDADE_ID);
        fd.append('metodo', 'Multicaixa Express');
        fd.append('telefone', phoneValue);
        const res = await fetch('../api/simular_pagamento.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.sucesso) {
            document.getElementById('success-ref').textContent = 'REF: ' + data.referencia;
        }
    } catch(e) {
        // Offline simulation
        document.getElementById('success-ref').textContent = 'REF: MX' + Date.now().toString().slice(-8).toUpperCase();
    }

    await new Promise(r => setTimeout(r, 400));
    overlay.classList.remove('show');

    document.getElementById('success-date').textContent = new Date().toLocaleString('pt-AO');
    document.getElementById('step-pin').classList.remove('show');
    document.getElementById('step-success').classList.add('show');
}

/* ── ATM SIMULATION ── */
async function simulateAtmPay() {
    const overlay = document.getElementById('processing-overlay');
    document.getElementById('proc-title').textContent = 'A verificar referência ATM';
    document.getElementById('proc-sub').textContent = 'Ligação ao terminal...';
    document.getElementById('proc-steps').innerHTML = `
        <div class="proc-step done"><i class="fa-solid fa-circle-notch fa-spin" style="color:var(--mx-blue)"></i> Validando referência...</div>
    `;
    overlay.classList.add('show');

    await new Promise(r => setTimeout(r, 2200));
    document.getElementById('proc-steps').innerHTML = `<div class="proc-step done"><i class="fa-solid fa-check-circle"></i> Pagamento confirmado no terminal!</div>`;
    document.getElementById('proc-title').textContent = 'Pagamento Concluído!';
    await new Promise(r => setTimeout(r, 700));

    try {
        const fd = new FormData();
        fd.append('id_mensalidade', MENSALIDADE_ID);
        fd.append('metodo', 'ATM/Referência Multicaixa');
        await fetch('../api/simular_pagamento.php', { method: 'POST', body: fd });
    } catch(e) {}

    overlay.classList.remove('show');
    location.href = 'minhas_mensalidades.php?msg=pago_atm';
}

/* ── COPY IBAN ── */
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check" style="color:var(--mx-green);"></i>';
        setTimeout(() => btn.innerHTML = orig, 2000);
    }).catch(() => alert('IBAN copiado: ' + text));
}

/* ── FILE NAME ── */
function showFileName(input) {
    const el = document.getElementById('file-name-display');
    if (input.files[0]) {
        el.textContent = '✅ ' + input.files[0].name;
        el.style.display = 'block';
    }
}

// Init
selectTab('mx');
generateAtmRef();
</script>
</body>
</html>
