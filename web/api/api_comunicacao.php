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
        // Usa o schema atual: tabelas mensagem/conversa/conversa_participante
        // Para simplificar o UI atual, filtramos por morador (id_morador) e mostramos mensagens.
        $id_morador = ($tipo_user === 'morador') ? $id_user : intval($_GET['id_morador'] ?? 0);

        if (!$id_morador) {
            echo json_encode(['sucesso' => false, 'erro' => 'Morador não especificado']);
            exit;
        }

        $stmt = $conexao->prepare(
            "SELECT msg.id,
                    msg.id_conversa,
                    msg.tipo_remetente,
                    msg.id_remetente,
                    msg.conteudo,
                    msg.lida,
                    msg.enviada_em,
                    conv.id AS conversa_id
             FROM mensagem msg
             JOIN conversa conv ON conv.id = msg.id_conversa
             WHERE (
                (msg.tipo_remetente = 'morador' AND msg.id_remetente = ?)
                OR (msg.tipo_remetente = 'administrador' AND conv.id IN (
                    SELECT cp.id_conversa
                    FROM conversa_participante cp
                    WHERE cp.tipo_user = 'morador' AND cp.id_user = ?
                ))
             )
             AND conv.id IN (
                SELECT cp2.id_conversa
                FROM conversa_participante cp2
                WHERE cp2.tipo_user = 'morador' AND cp2.id_user = ?
             )
             ORDER BY msg.enviada_em ASC"
        );

        // bind: morador_id para 3 placeholders
        $stmt->bind_param("iii", $id_morador, $id_morador, $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($row = $res->fetch_assoc()) {
            // Compat: o front espera `remetente` e `conteudo`
            $row['remetente'] = ($row['tipo_remetente'] === 'morador') ? 'morador' : 'funcionario';
            $rows[] = $row;
        }

        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'enviar_mensagem':
        $conteudo = trim($_POST['conteudo'] ?? '');
        if (!$conteudo) { echo json_encode(['sucesso' => false, 'erro' => 'Mensagem vazia']); exit; }

        // O UI atual envia apenas 'conteudo' quando é morador.
        // Para admin/funcionario, esperamos opcionalmente id_morador.
        if ($tipo_user === 'morador') {
            // Conversa pública do admin pode ter vários admins, mas o nosso UI só precisa da conversa privada do morador.
            $id_morador = $id_user;
            $tipo_remetente = 'morador';
            $id_remetente = $id_user;
        } else {
            $id_morador = intval($_POST['id_morador'] ?? 0);
            $tipo_remetente = 'administrador';
            $id_remetente = $id_user;
        }

        // Para garantir que o backend sempre cria/reutiliza a conversa privada do morador,
        // e não falhe caso o UI (morador) não envie id_morador.
        if ($tipo_user === 'morador') {
            $id_morador = $id_user;
        }

        if (!$id_morador) {
            echo json_encode(['sucesso' => false, 'erro' => 'Destinatário inválido']);
            exit;
        }

        // Criar/assegurar conversa privada por morador.
        // Estratégia simples: conversa do tipo 'privada' com título NULL, e participante morador.
        // Se existir uma conversa já, reutiliza.
        $stmt = $conexao->prepare(
            "SELECT c.id
             FROM conversa c
             JOIN conversa_participante cp ON cp.id_conversa = c.id
             WHERE c.tipo='privada' AND cp.tipo_user='morador' AND cp.id_user=?
             ORDER BY c.id DESC
             LIMIT 1"
        );
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();
        $conv_id = null;
        if ($res && $res->num_rows > 0) {
            $conv_id = (int)$res->fetch_assoc()['id'];
        }
        $stmt->close();

        if (!$conv_id) {
            $ins = $conexao->prepare("INSERT INTO conversa (tipo, titulo, criado_por) VALUES ('privada', NULL, NULL)");
            if (!$ins) { echo json_encode(['sucesso'=>false,'erro'=>$conexao->error]); exit; }
            $ins->execute();
            $conv_id = (int)$ins->insert_id;
            $ins->close();

            $insP = $conexao->prepare("INSERT INTO conversa_participante (id_conversa, tipo_user, id_user) VALUES (?, 'morador', ?)");
            $insP->bind_param("ii", $conv_id, $id_morador);
            $insP->execute();
            $insP->close();
        }

        $insMsg = $conexao->prepare(
            "INSERT INTO mensagem (id_conversa, tipo_remetente, id_remetente, conteudo)
             VALUES (?, ?, ?, ?)"
        );
        $insMsg->bind_param("issi", $conv_id, $tipo_remetente, $id_remetente, $conteudo);
        if ($insMsg->execute()) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        }
        $insMsg->close();
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Acção inválida']);
}
?>
