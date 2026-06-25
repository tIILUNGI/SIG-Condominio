<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

if (!isset($_SESSION['id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$id_user = intval($_SESSION['id']);
$tipo_user = $_SESSION['tipo']; // 'morador', 'admin' or 'funcionario'
$acao = $_GET['acao'] ?? 'listar_comunicados';

// Map session 'admin'/'funcionario' to database 'administrador'
$db_tipo_user = ($tipo_user === 'morador') ? 'morador' : 'administrador';

switch ($acao) {
    case 'listar_comunicados':
        $res = mysqli_query($conexao, "SELECT c.*, a.nome as autor FROM comunicado c LEFT JOIN administrador a ON a.id = c.criado_por ORDER BY c.criado_em DESC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'enviar_comunicado':
        if ($db_tipo_user !== 'administrador') {
            echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
            exit;
        }
        $titulo = trim($_POST['titulo'] ?? '');
        $conteudo = trim($_POST['conteudo'] ?? '');
        $tipo = $_POST['tipo'] ?? 'informativo';
        
        if (!$titulo || !$conteudo) {
            echo json_encode(['sucesso' => false, 'erro' => 'Preencha todos os campos']);
            exit;
        }

        $stmt = $conexao->prepare("INSERT INTO comunicado (titulo, conteudo, tipo, criado_por) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $titulo, $conteudo, $tipo, $id_user);
        if ($stmt->execute()) echo json_encode(['sucesso' => true]);
        else echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        break;

    case 'listar_conversas':
        // List all private conversations for the current admin/user
        if ($db_tipo_user === 'administrador') {
            $sql = "SELECT c.id as conversa_id, m.nome as morador_nome, m.id as morador_id,
                           (SELECT msg.conteudo FROM mensagem msg WHERE msg.id_conversa = c.id ORDER BY msg.enviada_em DESC LIMIT 1) as ultima_msg,
                           (SELECT COUNT(*) FROM mensagem msg WHERE msg.id_conversa = c.id AND msg.lida = 0 AND msg.tipo_remetente = 'morador') as nao_lidas
                    FROM conversa c
                    JOIN conversa_participante cp ON cp.id_conversa = c.id
                    JOIN morador m ON m.id = cp.id_user AND cp.tipo_user = 'morador'
                    WHERE c.tipo = 'privada'
                    ORDER BY (SELECT MAX(enviada_em) FROM mensagem WHERE id_conversa = c.id) DESC";
            $res = mysqli_query($conexao, $sql);
        } else {
            // Morador sees only their conversation with admin
            $sql = "SELECT c.id as conversa_id, 'Administração' as morador_nome,
                           (SELECT msg.conteudo FROM mensagem msg WHERE msg.id_conversa = c.id ORDER BY msg.enviada_em DESC LIMIT 1) as ultima_msg,
                           (SELECT COUNT(*) FROM mensagem msg WHERE msg.id_conversa = c.id AND msg.lida = 0 AND msg.tipo_remetente = 'administrador') as nao_lidas
                    FROM conversa c
                    JOIN conversa_participante cp ON cp.id_conversa = c.id
                    WHERE cp.tipo_user = 'morador' AND cp.id_user = $id_user
                    LIMIT 1";
            $res = mysqli_query($conexao, $sql);
        }
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'listar_mensagens':
        $id_morador = ($db_tipo_user === 'morador') ? $id_user : intval($_GET['id_morador'] ?? 0);
        if (!$id_morador) {
            echo json_encode(['sucesso' => false, 'erro' => 'Identificador inválido']);
            exit;
        }

        // Mark messages as read
        $upd = $conexao->prepare("
            UPDATE mensagem SET lida = 1 
            WHERE id_conversa IN (SELECT id_conversa FROM conversa_participante WHERE tipo_user='morador' AND id_user=?)
            AND tipo_remetente != ?
        ");
        $upd->bind_param("is", $id_morador, $db_tipo_user);
        $upd->execute();

        $stmt = $conexao->prepare("
            SELECT msg.id, msg.tipo_remetente, msg.id_remetente, msg.conteudo, msg.lida, msg.enviada_em
            FROM mensagem msg
            JOIN conversa conv ON conv.id = msg.id_conversa
            JOIN conversa_participante cp ON cp.id_conversa = conv.id
            WHERE cp.tipo_user = 'morador' AND cp.id_user = ?
            ORDER BY msg.enviada_em ASC
        ");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($row = $res->fetch_assoc()) {
            // Frontend expects 'remetente'
            $row['remetente'] = ($row['tipo_remetente'] === 'morador') ? 'morador' : 'funcionario';
            $rows[] = $row;
        }
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'enviar_mensagem':
        $conteudo = trim($_POST['conteudo'] ?? '');
        if (!$conteudo) { echo json_encode(['sucesso' => false, 'erro' => 'Vazio']); exit; }

        $id_morador = ($db_tipo_user === 'morador') ? $id_user : intval($_POST['id_morador'] ?? 0);
        if (!$id_morador) { echo json_encode(['sucesso' => false, 'erro' => 'Destinatário inválido']); exit; }

        // Find or create conversation
        $stmt = $conexao->prepare("SELECT id_conversa FROM conversa_participante WHERE tipo_user='morador' AND id_user=? LIMIT 1");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $conv_id = $stmt->get_result()->fetch_assoc()['id_conversa'] ?? null;
        $stmt->close();

        if (!$conv_id) {
            $conexao->begin_transaction();
            try {
                $ins = $conexao->prepare("INSERT INTO conversa (tipo) VALUES ('privada')");
                $ins->execute();
                $conv_id = $ins->insert_id;
                
                $insP = $conexao->prepare("INSERT INTO conversa_participante (id_conversa, tipo_user, id_user) VALUES (?, 'morador', ?)");
                $insP->bind_param("ii", $conv_id, $id_morador);
                $insP->execute();
                
                $conexao->commit();
            } catch (Exception $e) {
                $conexao->rollback();
                echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
                exit;
            }
        }

        $insMsg = $conexao->prepare("INSERT INTO mensagem (id_conversa, tipo_remetente, id_remetente, conteudo) VALUES (?, ?, ?, ?)");
        $insMsg->bind_param("isis", $conv_id, $db_tipo_user, $id_user, $conteudo);
        if ($insMsg->execute()) echo json_encode(['sucesso' => true]);
        else echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Acção inválida']);
}
?>
