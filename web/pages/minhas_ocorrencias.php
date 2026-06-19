<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    header("Location: ../login.html?erro=acesso");
    exit;
}
include("../api/conexao.php");

$morador_id = $_SESSION['id'];

// Processar nova ocorrência
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $tipo = $_POST['tipo'];
    $prioridade = $_POST['prioridade'];
    
    $stmt = $conexao->prepare("
        INSERT INTO ocorrencia (id_morador, titulo, descricao, tipo, prioridade) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $morador_id, $titulo, $descricao, $tipo, $prioridade);
    $stmt->execute();
    $msg = "Ocorrência criada com sucesso!";
}

// Buscar ocorrências do morador
$stmt = $conexao->prepare("
    SELECT * FROM ocorrencia 
    WHERE id_morador = ? 
    ORDER BY criado_em DESC
");
$stmt->bind_param("i", $morador_id);
$stmt->execute();
$ocorrencias = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Ocorrências</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-back { background: #95a5a6; color: white; }
        .btn-back:hover { background: #7f8c8d; }
        .btn-danger { background: #e74c3c; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;
        }
        .form-group textarea { height: 100px; }
        .ocorrencia { border-left: 4px solid #3498db; padding: 15px; margin-bottom: 15px; background: #f8f9fa; border-radius: 4px; }
        .ocorrencia .status { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; }
        .status-aberta { background: #e74c3c; color: white; }
        .status-em_analise { background: #f39c12; color: white; }
        .status-resolvida { background: #27ae60; color: white; }
        .status-encerrada { background: #95a5a6; color: white; }
        .prioridade { font-weight: 600; }
        .Baixa { color: #27ae60; }
        .Media { color: #f39c12; }
        .Alta { color: #e67e22; }
        .Urgente { color: #e74c3c; }
        .header-actions { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .msg { background: #27ae60; color: white; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-exclamation-triangle"></i> Minhas Ocorrências</h2>
            <a href="dashboard_morador.php" class="btn btn-back" style="margin-top:10px;">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <?php if (isset($msg)): ?>
            <div class="msg"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Formulário Nova Ocorrência -->
        <div class="card">
            <h3>📝 Nova Ocorrência</h3>
            <form method="POST">
                <input type="hidden" name="acao" value="criar">
                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="titulo" required>
                </div>
                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" required></textarea>
                </div>
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo">
                        <option value="Avaria">Avaria</option>
                        <option value="Reclamacao">Reclamação</option>
                        <option value="Sugestao">Sugestão</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Prioridade</label>
                    <select name="prioridade">
                        <option value="Baixa">Baixa</option>
                        <option value="Media" selected>Média</option>
                        <option value="Alta">Alta</option>
                        <option value="Urgente">Urgente</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Criar Ocorrência</button>
            </form>
        </div>

        <!-- Lista de Ocorrências -->
        <div class="card">
            <h3>📋 Minhas Ocorrências</h3>
            <?php if ($ocorrencias->num_rows > 0): ?>
                <?php while ($oc = $ocorrencias->fetch_assoc()): ?>
                    <div class="ocorrencia">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                            <h4><?php echo htmlspecialchars($oc['titulo']); ?></h4>
                            <span class="status status-<?php echo $oc['estado']; ?>">
                                <?php echo str_replace('_', ' ', $oc['estado']); ?>
                            </span>
                        </div>
                        <p style="margin:10px 0;"><?php echo nl2br(htmlspecialchars($oc['descricao'])); ?></p>
                        <div style="font-size:13px; color:#888;">
                            <span class="prioridade <?php echo $oc['prioridade']; ?>">
                                <i class="fas fa-flag"></i> <?php echo $oc['prioridade']; ?>
                            </span>
                            | <i class="fas fa-tag"></i> <?php echo $oc['tipo']; ?>
                            | <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($oc['criado_em'])); ?>
                            <?php if ($oc['data_resolucao']): ?>
                                | Resolvida em: <?php echo date('d/m/Y', strtotime($oc['data_resolucao'])); ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($oc['notas_admin']): ?>
                            <div style="margin-top:10px; background:#e8f4fd; padding:10px; border-radius:4px;">
                                <strong>Resposta da Administração:</strong>
                                <p><?php echo nl2br(htmlspecialchars($oc['notas_admin'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#888; text-align:center; padding:20px;">
                    <i class="fas fa-inbox"></i> Nenhuma ocorrência registrada.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>