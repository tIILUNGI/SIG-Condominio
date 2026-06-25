<?php
session_start();
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$admin_id = $_SESSION['id'];
$admin_nome = $_SESSION['nome'];

$stmt = $conexao->prepare("SELECT * FROM administrador WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$msg_sucesso = '';
$msg_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'editar_perfil') {
        $novo_nome = trim($_POST['novo_nome'] ?? '');
        $novo_email = trim($_POST['novo_email'] ?? '');
        $novo_telefone = trim($_POST['novo_telefone'] ?? '');
        $nova_morada = trim($_POST['nova_morada'] ?? '');

        if ($novo_nome && $novo_email) {
            $upd = $conexao->prepare("UPDATE administrador SET nome=?, email=?, telefone=?, morada=? WHERE id=?");
            $upd->bind_param("ssssi", $novo_nome, $novo_email, $novo_telefone, $nova_morada, $admin_id);
            if ($upd->execute()) {
                $_SESSION['nome'] = $novo_nome;
                $admin_nome = $novo_nome;
                $msg_sucesso = 'Perfil actualizado com sucesso!';
                $admin['nome'] = $novo_nome;
                $admin['email'] = $novo_email;
                $admin['telefone'] = $novo_telefone;
                $admin['morada'] = $nova_morada;
            } else {
                $msg_erro = 'Erro ao actualizar o perfil.';
            }
        }
    } elseif ($_POST['acao'] === 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';
        
        if ($nova_senha === $confirmar && strlen($nova_senha) >= 6) {
            $chk = $conexao->prepare("SELECT senha_hash FROM administrador WHERE id=?");
            $chk->bind_param("i", $admin_id);
            $chk->execute();
            $row = $chk->get_result()->fetch_assoc();
            
            if ($row && password_verify($senha_atual, $row['senha_hash'])) {
                $new_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $upd = $conexao->prepare("UPDATE administrador SET senha_hash=? WHERE id=?");
                $upd->bind_param("si", $new_hash, $admin_id);
                if ($upd->execute()) $msg_sucesso = 'Senha alterada com sucesso!';
                else $msg_erro = 'Erro ao salvar nova senha.';
            } else {
                $msg_erro = 'Senha actual incorrecta.';
            }
        } else {
            $msg_erro = 'Dados de senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Perfil Administrativo - Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="../js/theme-manager.js"></script>
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Administrativo</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <button class="nav-item" onclick="window.location.href='../dashboard.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item active">
            <i class="fa-solid fa-user-shield"></i><span>Meu Perfil</span>
        </button>
    </nav>
    <div class="sidebar-footer">
        <div class="avatar-admin"><?php echo strtoupper(substr($admin_nome, 0, 2)); ?></div>
        <div style="flex:1;">
            <p class="af-name"><?php echo htmlspecialchars($admin_nome); ?></p>
            <p class="af-role"><?php echo ucfirst($_SESSION['tipo']); ?></p>
        </div>
        <a href="../api/logout.php" title="Sair" style="color:var(--text-muted); font-size:1rem;"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="topbar-title">🏛 Gestão de Perfil Administrativo</span>
        <div class="topbar-right">
            <div class="clock-display" id="clock-display"></div>
            <div class="avatar-admin" style="width:34px;height:34px;"><?php echo strtoupper(substr($admin_nome, 0, 2)); ?></div>
        </div>
    </header>

    <section class="tab-section active">
        <div class="page-header">
            <h1 class="page-title">👤 Configurações de Perfil</h1>
            <p class="page-sub">Gira as suas credenciais e informações de contacto</p>
        </div>

        <?php if ($msg_sucesso): ?><div class="alert alert-success"><?php echo $msg_sucesso; ?></div><?php endif; ?>
        <?php if ($msg_erro): ?><div class="alert alert-danger"><?php echo $msg_erro; ?></div><?php endif; ?>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div class="card">
                <div class="card-head"><p class="card-title">Dados Básicos</p></div>
                <form method="POST" style="padding:20px;">
                    <input type="hidden" name="acao" value="editar_perfil">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" name="novo_nome" value="<?php echo htmlspecialchars($admin['nome']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Corporativo</label>
                        <input type="email" name="novo_email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="novo_telefone" value="<?php echo htmlspecialchars($admin['telefone']); ?>">
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%; margin-top:10px;">Salvar Alterações</button>
                </form>
            </div>

            <div class="card">
                <div class="card-head"><p class="card-title">Segurança</p></div>
                <form method="POST" style="padding:20px;">
                    <input type="hidden" name="acao" value="alterar_senha">
                    <div class="form-group">
                        <label>Senha Actual</label>
                        <input type="password" name="senha_atual" required>
                    </div>
                    <div class="form-group">
                        <label>Nova Senha</label>
                        <input type="password" name="nova_senha" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmar Nova Senha</label>
                        <input type="password" name="confirmar_senha" required>
                    </div>
                    <button type="submit" class="btn-secondary" style="width:100%; margin-top:10px;">Actualizar Senha</button>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }
function clock() {
    const now = new Date();
    const el = document.getElementById('clock-display');
    if (el) el.textContent = now.toLocaleTimeString('pt-AO');
}
setInterval(clock, 1000);
clock();
</script>
<style>
.alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
.alert-success { background: rgba(76,175,125,0.1); color: #4caf7d; border: 1px solid #4caf7d; }
.alert-danger { background: rgba(224,82,82,0.1); color: #e05252; border: 1px solid #e05252; }
</style>
</body>
</html>
