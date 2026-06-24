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
    SELECT m.*, a.numero as apartamento, bl.letra as bloco
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
$mes_txt = $nome_mes[(int)$m['mes'] - 1] ?? 'Mês ' . $m['mes'];

$nome = $_SESSION['nome'] ?? 'Morador';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Simulador de Pagamento — Nosso Zimbo</title>
    <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="../js/theme-manager.js"></script>
    <script>
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .pay-card {
            max-width: 600px;
            margin: 40px auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .pay-header {
            background: var(--gold);
            color: #000;
            padding: 2rem;
            text-align: center;
        }
        .pay-body {
            padding: 2rem;
            background: var(--surface);
        }
        .iban-box {
            background: rgba(100,149,237,0.1);
            border: 1px dashed #6495ed;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            position: relative;
        }
        .iban-box label {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: block;
            margin-bottom: 5px;
        }
        .iban-value {
            font-family: monospace;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
        }
        .copy-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6495ed;
            cursor: pointer;
        }
        .amount-display {
            text-align: center;
            margin: 1rem 0;
        }
        .amount-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gold);
        }
        .btn-confirmar {
            width: 100%;
            padding: 15px;
            background: var(--gold);
            color: #000;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-confirmar:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<main class="main-content" style="margin-left:0; width:100%;">
    <div class="pay-card">
        <div class="pay-header">
            <i class="fa-solid fa-receipt" style="font-size:3rem; margin-bottom:1rem;"></i>
            <h2>Pagamento de Mensalidade</h2>
            <p><?php echo htmlspecialchars($mes_txt . ' / ' . $m['ano']); ?></p>
        </div>
        <div class="pay-body">
            <div style="display:flex; justify-content:space-between; margin-bottom:1rem; border-bottom:1px solid var(--border); padding-bottom:1rem;">
                <span>Serviço:</span>
                <strong><?php echo htmlspecialchars($m['servico']); ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:1rem; border-bottom:1px solid var(--border); padding-bottom:1rem;">
                <span>Residência:</span>
                <strong><?php echo htmlspecialchars($m['bloco'] . ' - ' . $m['apartamento']); ?></strong>
            </div>

            <div class="iban-box">
                <label>IBAN PARA TRANSFERÊNCIA (BAI):</label>
                <div class="iban-value">AO06 0040 0000 1234 5678 9012 3</div>
                <button class="copy-btn" onclick="copyIban()"><i class="fa-regular fa-copy"></i></button>
            </div>

            <div class="amount-display">
                <p style="color:var(--text-muted);">VALOR A PAGAR:</p>
                <div class="amount-value">Kz <?php echo number_format($m['valor'], 2); ?></div>
            </div>

            <form action="../api/pagar.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="valor" value="<?php echo $m['valor']; ?>">
                <input type="hidden" name="metodo" value="Transferência Bancária">
                
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label>Referência / Comprovativo (Opcional)</label>
                    <input type="text" name="referencia" placeholder="Ex: TXN123456789" style="width:100%; box-sizing:border-box; padding:12px; border-radius:8px; border:1px solid var(--border); background:var(--dark3); color:var(--text);">
                </div>

                <button type="submit" class="btn-confirmar">EFECTUAR PAGAMENTO</button>
                <a href="minhas_mensalidades.php" style="display:block; text-align:center; margin-top:1rem; color:var(--text-muted); text-decoration:none;">Cancelar e Sair</a>
            </form>
        </div>
    </div>
</main>

<script>
function copyIban() {
    const iban = "AO06 0040 0000 1234 5678 9012 3";
    navigator.clipboard.writeText(iban).then(() => {
        alert("IBAN copiado para a área de transferência!");
    });
}
</script>
</body>
</html>
