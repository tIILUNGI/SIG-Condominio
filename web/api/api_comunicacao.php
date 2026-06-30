<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

if (!isset($_SESSION['id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

if (!$conexao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sem conexão com a base de dados']);
    exit;
}

$id_user    = intval($_SESSION['id']);
$tipo_user  = $_SESSION['tipo']; // 'morador' | 'admin' | 'funcionario'
$acao       = $_GET['acao'] ?? 'listar_comunicados';

// Map session type to DB enum value
$db_tipo_user = ($tipo_user === 'morador') ? 'morador' : 'administrador';

// ─────────────────────────────────────────────────────────────────────────
// Helper — find or create a private conversation for a given morador
// ─────────────────────────────────────────────────────────────────────────
if (!function_exists('getOrCreateConversaId')):
function getOrCreateConversaId($conexao, int $id_morador): ?int {
    $stmt = $conexao->prepare(
        "SELECT id_conversa FROM conversa_participante
         WHERE tipo_user='morador' AND id_user=? LIMIT 1"
    );
    $stmt->bind_param("i", $id_morador);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) return intval($row['id_conversa']);

    // Create conversation + add morador as participant
    $conexao->begin_transaction();
    try {
        $ins = $conexao->prepare("INSERT INTO conversa (tipo) VALUES ('privada')");
        $ins->execute();
        $conv_id = $ins->insert_id;
        $ins->close();

        $insP = $conexao->prepare(
            "INSERT INTO conversa_participante (id_conversa, tipo_user, id_user) VALUES (?, 'morador', ?)"
        );
        $insP->bind_param("ii", $conv_id, $id_morador);
        $insP->execute();
        $insP->close();

        $conexao->commit();
        return $conv_id;
    } catch (Exception $e) {
        $conexao->rollback();
        return null;
    }
}
endif;

// ─────────────────────────────────────────────────────────────────────────
switch ($acao) {

    // ── COMUNICADOS ──────────────────────────────────────────────────────
    case 'listar_comunicados':
        $res = mysqli_query($conexao,
            "SELECT c.*, a.nome AS autor
             FROM comunicado c
             LEFT JOIN administrador a ON a.id = c.criado_por
             ORDER BY c.criado_em DESC"
        );
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'enviar_comunicado':
        if ($db_tipo_user !== 'administrador') {
            echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
            exit;
        }
        $titulo   = trim($_POST['titulo']   ?? '');
        $conteudo = trim($_POST['conteudo'] ?? '');
        $tipo     = $_POST['tipo'] ?? 'informativo';

        if (!$titulo || !$conteudo) {
            echo json_encode(['sucesso' => false, 'erro' => 'Preencha todos os campos']);
            exit;
        }

        $stmt = $conexao->prepare(
            "INSERT INTO comunicado (titulo, conteudo, tipo, criado_por) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("sssi", $titulo, $conteudo, $tipo, $id_user);
        $comunicado_criado = false;
        if ($stmt->execute()) $comunicado_criado = true;
        else { echo json_encode(['sucesso' => false, 'erro' => $conexao->error]); break; }

        $mensagens_enviadas = 0;
        $stmtConv = $conexao->prepare(
            "SELECT cp.id_conversa, cp.id_user
             FROM conversa_participante cp
             WHERE cp.tipo_user = 'morador'
               AND cp.id_user IN (SELECT id FROM morador WHERE estado_conta IN ('Activo','Pendente','AguardandoValidacaoPagamento','AguardandoAtribuicaoCasa','Aprovado'))"
        );
        $stmtConv->execute();
        $resConv = $stmtConv->get_result();
        while ($row = $resConv->fetch_assoc()) {
            $id_conversa = $row['id_conversa'];
            $id_morador_dest = $row['id_user'];
            $msg_texto = "[COMUNICADO - {$tipo}] {$titulo}\n\n{$conteudo}";
            $stmtMsg = $conexao->prepare(
                "INSERT INTO mensagem (id_conversa, tipo_remetente, id_remetente, conteudo) VALUES (?, 'administrador', ?, ?)"
            );
            $stmtMsg->bind_param("iis", $id_conversa, $id_user, $msg_texto);
            $stmtMsg->execute();
            $stmtMsg->close();
            $mensagens_enviadas++;
        }
        $stmtConv->close();
        $stmt->close();

        echo json_encode(['sucesso' => true, 'mensagens_enviadas' => $mensagens_enviadas]);
        break;

    // ── CHAT — LISTAR CONVERSAS (Admin) ──────────────────────────────────
    case 'listar_conversas':
        if ($db_tipo_user === 'administrador') {
            $sql = "SELECT
                        c.id AS conversa_id,
                        m.nome AS morador_nome,
                        m.id   AS morador_id,
                        (SELECT msg.conteudo
                         FROM mensagem msg
                         WHERE msg.id_conversa = c.id
                         ORDER BY msg.enviada_em DESC LIMIT 1) AS ultima_msg,
                        (SELECT msg.enviada_em
                         FROM mensagem msg
                         WHERE msg.id_conversa = c.id
                         ORDER BY msg.enviada_em DESC LIMIT 1) AS ultima_em,
                        (SELECT COUNT(*)
                         FROM mensagem msg
                         WHERE msg.id_conversa = c.id
                           AND msg.lida = 0
                           AND msg.tipo_remetente = 'morador') AS nao_lidas
                    FROM conversa c
                    JOIN conversa_participante cp ON cp.id_conversa = c.id
                    JOIN morador m ON m.id = cp.id_user AND cp.tipo_user = 'morador'
                    WHERE c.tipo = 'privada'
                    ORDER BY ultima_em DESC";
        } else {
            // Morador: só vê a sua conversa
            $sql = "SELECT
                        c.id AS conversa_id,
                        'Administração' AS morador_nome,
                        (SELECT msg.conteudo
                         FROM mensagem msg
                         WHERE msg.id_conversa = c.id
                         ORDER BY msg.enviada_em DESC LIMIT 1) AS ultima_msg,
                        (SELECT COUNT(*)
                         FROM mensagem msg
                         WHERE msg.id_conversa = c.id
                           AND msg.lida = 0
                           AND msg.tipo_remetente = 'administrador') AS nao_lidas
                    FROM conversa c
                    JOIN conversa_participante cp ON cp.id_conversa = c.id
                    WHERE cp.tipo_user = 'morador' AND cp.id_user = $id_user
                    LIMIT 1";
        }
        $res  = mysqli_query($conexao, $sql);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    // ── CHAT — LISTAR MENSAGENS ───────────────────────────────────────────
    case 'listar_mensagens':
        $id_morador = ($db_tipo_user === 'morador')
            ? $id_user
            : intval($_GET['id_morador'] ?? 0);

        if (!$id_morador) {
            echo json_encode(['sucesso' => false, 'erro' => 'Identificador inválido']);
            exit;
        }

        // Marcar como lidas as mensagens DO OUTRO lado
        $upd = $conexao->prepare("
            UPDATE mensagem SET lida = 1
            WHERE id_conversa IN (
                SELECT id_conversa FROM conversa_participante
                WHERE tipo_user='morador' AND id_user=?
            )
            AND tipo_remetente != ?
        ");
        $upd->bind_param("is", $id_morador, $db_tipo_user);
        $upd->execute();
        $upd->close();

        // Buscar mensagens
        $stmt = $conexao->prepare("
            SELECT msg.id,
                   msg.tipo_remetente,
                   msg.id_remetente,
                   msg.conteudo,
                   msg.lida,
                   msg.enviada_em
            FROM mensagem msg
            JOIN conversa_participante cp ON cp.id_conversa = msg.id_conversa
            WHERE cp.tipo_user = 'morador' AND cp.id_user = ?
            ORDER BY msg.enviada_em ASC
        ");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $res  = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            // Normalize: 'morador' = morador sent, 'admin' = admin sent
            $r['de_morador'] = ($r['tipo_remetente'] === 'morador');
            $rows[] = $r;
        }
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    // ── CHAT — ENVIAR MENSAGEM ────────────────────────────────────────────
    case 'enviar_mensagem':
        $conteudo = trim($_POST['conteudo'] ?? '');
        if (!$conteudo) {
            echo json_encode(['sucesso' => false, 'erro' => 'Mensagem vazia']);
            exit;
        }

        $id_morador = ($db_tipo_user === 'morador')
            ? $id_user
            : intval($_POST['id_morador'] ?? 0);

        if (!$id_morador) {
            echo json_encode(['sucesso' => false, 'erro' => 'Destinatário inválido']);
            exit;
        }

        $conv_id = getOrCreateConversaId($conexao, $id_morador);
        if (!$conv_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível criar conversa']);
            exit;
        }

        $insMsg = $conexao->prepare(
            "INSERT INTO mensagem (id_conversa, tipo_remetente, id_remetente, conteudo)
             VALUES (?, ?, ?, ?)"
        );
        $insMsg->bind_param("isis", $conv_id, $db_tipo_user, $id_user, $conteudo);
        if ($insMsg->execute()) {
            echo json_encode(['sucesso' => true, 'id' => $insMsg->insert_id]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        }
        break;

    // ── CHAT — CONTAGEM DE NÃO LIDAS (para badge na navbar) ──────────────
    case 'nao_lidas':
        if ($db_tipo_user === 'morador') {
            $stmt = $conexao->prepare("
                SELECT COUNT(*) AS total
                FROM mensagem msg
                JOIN conversa_participante cp ON cp.id_conversa = msg.id_conversa
                WHERE cp.tipo_user = 'morador' AND cp.id_user = ?
                  AND msg.lida = 0 AND msg.tipo_remetente = 'administrador'
            ");
            $stmt->bind_param("i", $id_user);
        } else {
            $stmt = $conexao->prepare("
                SELECT COUNT(*) AS total
                FROM mensagem msg
                WHERE msg.lida = 0 AND msg.tipo_remetente = 'morador'
            ");
        }
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'];
        echo json_encode(['sucesso' => true, 'total' => intval($total)]);
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Acção inválida']);
}
?>
