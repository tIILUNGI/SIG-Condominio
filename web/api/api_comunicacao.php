<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

if (!isset($_SESSION['id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$id_user = intval($_SESSION['id']);
$tipo_user = $_SESSION['tipo']; // 'morador' ou 'admin' (ou 'funcionario')
$acao = $_GET['acao'] ?? 'listar_comunicados';

switch ($acao) {
    case 'listar_comunicados':
        $res = mysqli_query($conexao, "SELECT * FROM comunicado ORDER BY criado_em DESC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'enviar_comunicado':
        if ($tipo_user !== 'admin' && $tipo_user !== 'funcionario') {
            echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
            exit;
        }
        $titulo = $_POST['titulo'] ?? '';
        $conteudo = $_POST['conteudo'] ?? '';
        $tipo = $_POST['tipo'] ?? 'informativo';
        
        $stmt = $conexao->prepare("INSERT INTO comunicado (titulo, conteudo, tipo, criado_por) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $titulo, $conteudo, $tipo, $id_user);
        if ($stmt->execute()) echo json_encode(['sucesso' => true]);
        else echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        break;

    case 'listar_mensagens':
        // Se for morador, lista mensagens dele com funcionários
        // Se for admin, pode precisar de um ID de morador para ver a conversa específica
        $id_morador = ($tipo_user === 'morador') ? $id_user : intval($_GET['id_morador'] ?? 0);
        
        if (!$id_morador) {
            echo json_encode(['sucesso' => false, 'erro' => 'Morador não especificado']);
            exit;
        }

        $stmt = $conexao->prepare("SELECT * FROM chat_mensagem WHERE id_morador = ? ORDER BY enviada_em ASC");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'enviar_mensagem':
        $conteudo = $_POST['conteudo'] ?? '';
        if (!$conteudo) { echo json_encode(['sucesso' => false, 'erro' => 'Mensagem vazia']); exit; }

        if ($tipo_user === 'morador') {
            $id_morador = $id_user;
            $remetente = 'morador';
            $id_funcionario = NULL; // Pode ser atribuído depois ou ficar geral
        } else {
            $id_morador = intval($_POST['id_morador'] ?? 0);
            $remetente = 'funcionario';
            $id_funcionario = $id_user;
        }

        if (!$id_morador) { echo json_encode(['sucesso' => false, 'erro' => 'Destinatário inválido']); exit; }

        $stmt = $conexao->prepare("INSERT INTO chat_mensagem (id_morador, id_funcionario, remetente, conteudo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $id_morador, $id_funcionario, $remetente, $conteudo);
        if ($stmt->execute()) echo json_encode(['sucesso' => true]);
        else echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Acção inválida']);
}
?>
