<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = $_SESSION['id'];
$morador_nome = $_SESSION['nome'];

$stmt = $conexao->prepare("SELECT * FROM morador WHERE id = ?");
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$result = $stmt->get_result();
$morador = $result->fetch_assoc();

// Handle Edit Profile POST
$msg_sucesso = '';
$msg_erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'editar_perfil') {
        $novo_nome = trim($_POST['novo_nome'] ?? '');
        $novo_email = trim($_POST['novo_email'] ?? '');
        $novo_telefone = trim($_POST['novo_telefone'] ?? '');
        $nova_morada = trim($_POST['nova_morada'] ?? '');
        if ($novo_nome && $novo_email && $novo_telefone) {
            $upd = $conexao->prepare("UPDATE morador SET nome=?, email=?, telefone=?, morada_anterior=? WHERE id=?");
            $upd->bind_param("ssssi", $novo_nome, $novo_email, $novo_telefone, $nova_morada, $morador_id);
            if ($upd->execute()) {
                $_SESSION['nome'] = $novo_nome;
                $morador_nome = $novo_nome;
                $msg_sucesso = 'Perfil actualizado com sucesso!';
                $morador['nome'] = $novo_nome;
                $morador['email'] = $novo_email;
                $morador['telefone'] = $novo_telefone;
                $morador['morada_anterior'] = $nova_morada;
            } else {
                $msg_erro = 'Erro ao actualizar o perfil. Tente novamente.';
            }
        } else {
            $msg_erro = 'Preencha todos os campos obrigatórios.';
        }
    } elseif ($_POST['acao'] === 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';
        if (!$senha_atual || !$nova_senha || !$confirmar) {
            $msg_erro = 'Preencha todos os campos de senha.';
        } elseif ($nova_senha !== $confirmar) {
            $msg_erro = 'A nova senha e a confirmação não coincidem.';
        } elseif (strlen($nova_senha) < 6) {
            $msg_erro = 'A nova senha deve ter no mínimo 6 caracteres.';
        } else {
            // Verify current password
            $chk = $conexao->prepare("SELECT senha FROM morador WHERE id=?");
            $chk->bind_param("i", $morador_id);
            $chk->execute();
            $row = $chk->get_result()->fetch_assoc();
            if ($row && password_verify($senha_atual, $row['senha'])) {
                $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $upd = $conexao->prepare("UPDATE morador SET senha=? WHERE id=?");
                $upd->bind_param("si", $hash, $morador_id);
                if ($upd->execute()) {
                    $msg_sucesso = 'Senha alterada com sucesso!';
                } else {
                    $msg_erro = 'Erro ao alterar a senha. Tente novamente.';
                }
            } else {
                // Fallback: check plain text (for older records)
                $chk2 = $conexao->prepare("SELECT id FROM morador WHERE id=? AND senha=?");
                $hash_plain = md5($senha_atual);
                $chk2->bind_param("is", $morador_id, $senha_atual);
                $chk2->execute();
                if ($chk2->get_result()->num_rows > 0) {
                    $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $upd = $conexao->prepare("UPDATE morador SET senha=? WHERE id=?");
                    $upd->bind_param("si", $hash, $morador_id);
                    if ($upd->execute()) { $msg_sucesso = 'Senha alterada com sucesso!'; }
                    else { $msg_erro = 'Erro ao alterar a senha.'; }
                } else {
                    $msg_erro = 'A senha actual está incorrecta.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Meu Perfil - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="../js/theme-manager.js"></script>
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:1000; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--dark2,#1a1a2e); border:1px solid var(--border,#333); border-radius:16px; padding:1.75rem; width:100%; max-width:480px; position:relative; box-shadow:0 20px 60px rgba(0,0,0,.5); }
        .modal-close { position:absolute; top:.75rem; right:.75rem; background:none; border:none; color:var(--text-muted,#888); font-size:1.1rem; cursor:pointer; padding:.25rem .5rem; border-radius:6px; }
        .modal-close:hover { background:var(--dark4,#333); color:var(--text,#fff); }
        .modal-title { font-size:1.1rem; font-weight:700; margin-bottom:1.25rem; }
        .form-group { margin-bottom:1rem; }
        .form-group label { display:block; font-size:.78rem; font-weight:600; color:var(--text-muted,#888); text-transform:uppercase; letter-spacing:.04em; margin-bottom:.4rem; }
        .form-group input { width:100%; background:var(--dark4,#2a2a3e); border:1px solid var(--border,#333); border-radius:8px; padding:.65rem .9rem; color:var(--text,#fff); font-family:inherit; font-size:.9rem; outline:none; transition:border-color .2s; }
        .form-group input:focus { border-color:var(--gold,#c9a84c); }
        .alert-msg { padding:.75rem 1rem; border-radius:8px; font-size:.85rem; font-weight:500; margin-bottom:1rem; }
        .alert-success { background:rgba(76,175,125,.15); border:1px solid rgba(76,175,125,.4); color:#4caf7d; }
        .alert-error { background:rgba(224,82,82,.12); border:1px solid rgba(224,82,82,.35); color:#e05252; }
        .profile-avatar { width:72px; height:72px; border-radius:50%; background:var(--gold,#c9a84c); color:#000; font-size:1.6rem; font-weight:900; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; border:3px solid rgba(201,168,76,.4); }
        .info-row { display:flex; justify-content:space-between; align-items:center; padding:.65rem 0; border-bottom:1px solid var(--border,#333); font-size:.88rem; }
        .info-row:last-child { border-bottom:none; }
        .info-key { color:var(--text-muted,#888); font-weight:500; }
        .info-val { font-weight:600; color:var(--text,#fff); text-align:right; max-width:60%; }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Meu Perfil</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Menu</p>
        <button class="nav-item" onclick="window.location.href='dashboard_morador.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item active">
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
        <span class="topbar-title"><i class="fa-solid fa-building-columns"></i> Nosso Zimbo — Perfil</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;background:#f0c040;color:#000;">
                <?php echo strtoupper(substr($morador_nome, 0, 2)); ?>
            </div>
        </div>
    </header>

    <section class="tab-section active">
        <div class="page-header">
            <h1 class="page-title">👤 Meu Perfil</h1>
            <p class="page-sub">Gira as suas informações pessoais</p>
        </div>

        <?php if ($msg_sucesso): ?>
        <div class="alert-msg alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($msg_sucesso); ?></div>
        <?php endif; ?>
        <?php if ($msg_erro): ?>
        <div class="alert-msg alert-error"><i class="fa-solid fa-circle-xmark"></i> <?php echo htmlspecialchars($msg_erro); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-head">
                <p class="card-title"><i class="fa-solid fa-id-card"></i> Dados Pessoais</p>
                <div style="display:flex;gap:.5rem;">
                    <button class="btn-primary" onclick="abrirModalEditar()" style="font-size:.82rem;padding:.5rem .9rem;">
                        <i class="fa-solid fa-pen"></i> Editar Perfil
                    </button>
                    <button class="btn-secondary" onclick="abrirModalSenha()" style="font-size:.82rem;padding:.5rem .9rem;">
                        <i class="fa-solid fa-key"></i> Alterar Senha
                    </button>
                </div>
            </div>
            <div style="padding:1.5rem;">
                <div class="profile-avatar"><?php echo strtoupper(substr($morador_nome, 0, 2)); ?></div>
                <div>
                    <div class="info-row">
                        <span class="info-key"><i class="fa-solid fa-user" style="margin-right:.4rem;color:var(--gold);"></i> Nome</span>
                        <span class="info-val"><?php echo htmlspecialchars($morador['nome'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key"><i class="fa-solid fa-envelope" style="margin-right:.4rem;color:var(--gold);"></i> Email</span>
                        <span class="info-val"><?php echo htmlspecialchars($morador['email'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key"><i class="fa-solid fa-phone" style="margin-right:.4rem;color:var(--gold);"></i> Telefone</span>
                        <span class="info-val"><?php echo htmlspecialchars($morador['telefone'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key"><i class="fa-solid fa-id-badge" style="margin-right:.4rem;color:var(--gold);"></i> Bilhete de Identidade</span>
                        <span class="info-val" style="font-family:monospace;"><?php echo htmlspecialchars($morador['numbI'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key"><i class="fa-solid fa-globe" style="margin-right:.4rem;color:var(--gold);"></i> Nacionalidade</span>
                        <span class="info-val"><?php echo htmlspecialchars($morador['nacionalidade'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key"><i class="fa-solid fa-cake-candles" style="margin-right:.4rem;color:var(--gold);"></i> Data Nascimento</span>
                        <span class="info-val"><?php echo $morador['nasc'] ? date('d/m/Y', strtotime($morador['nasc'])) : 'N/A'; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key"><i class="fa-solid fa-location-dot" style="margin-right:.4rem;color:var(--gold);"></i> Morada</span>
                        <span class="info-val"><?php echo htmlspecialchars($morador['morada_anterior'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key"><i class="fa-solid fa-circle-dot" style="margin-right:.4rem;color:var(--gold);"></i> Estado da Conta</span>
                        <span class="info-val">
                            <span style="color:<?php echo ($morador['estado_conta'] ?? '') === 'Activo' ? 'var(--success)' : 'var(--danger)'; ?>; font-weight:700;">
                                <?php echo htmlspecialchars($morador['estado_conta'] ?? 'N/A'); ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- MODAL: Editar Perfil -->
<div class="modal-overlay" id="modal-editar" onclick="fecharModal('modal-editar')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="fecharModal('modal-editar')"><i class="fa-solid fa-xmark"></i></button>
        <h3 class="modal-title"><i class="fa-solid fa-pen" style="color:var(--gold);margin-right:.4rem;"></i> Editar Perfil</h3>
        <form method="POST" action="">
            <input type="hidden" name="acao" value="editar_perfil">
            <div class="form-group">
                <label>Nome Completo *</label>
                <input type="text" name="novo_nome" value="<?php echo htmlspecialchars($morador['nome'] ?? ''); ?>" required maxlength="60" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" title="O nome deve conter apenas letras e espaços." />
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="novo_email" value="<?php echo htmlspecialchars($morador['email'] ?? ''); ?>" required />
            </div>
            <div class="form-group">
                <label>Telefone *</label>
                <input type="tel" name="novo_telefone" value="<?php echo htmlspecialchars($morador['telefone'] ?? ''); ?>" required maxlength="11" placeholder="9XX-XXX-XXX" pattern="9[0-9]{2}-[0-9]{3}-[0-9]{3}" title="Formato esperado: 9xx-xxx-xxx" oninput="maskPhone(this)" />
            </div>
            <div class="form-group">
                <label>Morada Actual</label>
                <input type="text" name="nova_morada" value="<?php echo htmlspecialchars($morador['morada_anterior'] ?? ''); ?>" placeholder="Rua, bairro, município, província" />
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1.25rem;">
                <button type="submit" class="btn-primary" style="flex:1;"><i class="fa-solid fa-floppy-disk"></i> Guardar Alterações</button>
                <button type="button" class="btn-secondary" onclick="fecharModal('modal-editar')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Alterar Senha -->
<div class="modal-overlay" id="modal-senha" onclick="fecharModal('modal-senha')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="fecharModal('modal-senha')"><i class="fa-solid fa-xmark"></i></button>
        <h3 class="modal-title"><i class="fa-solid fa-key" style="color:var(--gold);margin-right:.4rem;"></i> Alterar Senha</h3>
        <form method="POST" action="" onsubmit="return validarSenha()">
            <input type="hidden" name="acao" value="alterar_senha">
            <div class="form-group">
                <label>Senha Actual *</label>
                <input type="password" id="senha-atual" name="senha_atual" required placeholder="A sua senha actual" />
            </div>
            <div class="form-group">
                <label>Nova Senha *</label>
                <input type="password" id="nova-senha" name="nova_senha" required placeholder="Mínimo 6 caracteres" minlength="6" />
            </div>
            <div class="form-group">
                <label>Confirmar Nova Senha *</label>
                <input type="password" id="confirmar-senha" name="confirmar_senha" required placeholder="Repita a nova senha" />
            </div>
            <div id="senha-erro" style="color:#e05252;font-size:.82rem;margin-bottom:.75rem;display:none;"></div>
            <div style="display:flex;gap:.75rem;margin-top:1.25rem;">
                <button type="submit" class="btn-primary" style="flex:1;"><i class="fa-solid fa-lock"></i> Alterar Senha</button>
                <button type="button" class="btn-secondary" onclick="fecharModal('modal-senha')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function maskPhone(i) {
    let v = i.value.replace(/\D/g, "");
    if (v.length > 9) v = v.substring(0, 9);
    let r = "";
    if (v.length > 0) r += v.substring(0, 3);
    if (v.length > 3) r += "-" + v.substring(3, 6);
    if (v.length > 6) r += "-" + v.substring(6, 9);
    i.value = r;
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
function clock() {
    const now = new Date();
    const el = document.getElementById('clock-display');
    if (el) el.textContent = now.toLocaleTimeString('pt-AO');
}
function abrirModalEditar() {
    document.getElementById('modal-editar').classList.add('open');
}
function abrirModalSenha() {
    document.getElementById('modal-senha').classList.add('open');
}
function fecharModal(id) {
    document.getElementById(id).classList.remove('open');
}
function validarSenha() {
    const nova = document.getElementById('nova-senha').value;
    const confirmar = document.getElementById('confirmar-senha').value;
    const erroEl = document.getElementById('senha-erro');
    if (nova !== confirmar) {
        erroEl.textContent = 'As senhas não coincidem!';
        erroEl.style.display = 'block';
        return false;
    }
    if (nova.length < 6) {
        erroEl.textContent = 'A senha deve ter no mínimo 6 caracteres.';
        erroEl.style.display = 'block';
        return false;
    }
    erroEl.style.display = 'none';
    return true;
}
window.onload = function() { clock(); setInterval(clock, 1000); };

<?php if ($msg_sucesso): ?>
// Auto-open modal was just submitted; keep on page
<?php endif; ?>
</script>
</body>
</html>