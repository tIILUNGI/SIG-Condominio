<?php
/**
 * api_morador.php — Dados do portal do morador (JSON)
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

if (!$conexao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sem BD']);
    exit;
}

$id_morador = intval($_SESSION['id']);
$acao = $_GET['acao'] ?? 'perfil';

switch ($acao) {

    case 'perfil':
        $stmt = $conexao->prepare(
            "SELECT m.id, m.nome, m.email, m.telefone, m.numbi, m.estado_conta,
                    a.codigo as apartamento, a.tipologia, b.letra as bloco
             FROM morador m
             LEFT JOIN morador_apartamento ma ON ma.id_morador = m.id AND ma.activo = 1
             LEFT JOIN apartamento a ON a.id = ma.id_apartamento
             LEFT JOIN bloco b ON b.id = a.id_bloco
             WHERE m.id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();
        echo json_encode(['sucesso' => true, 'dados' => $res->fetch_assoc()]);
        $stmt->close();
        break;

    case 'mensalidades':
        $stmt = $conexao->prepare(
            "SELECT id, servico, mes, ano, valor, vencimento, estado
             FROM mensalidade
             WHERE id_morador = ?
             ORDER BY ano DESC, mes DESC"
        );
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        $stmt->close();
        break;

    case 'historico_pagamentos':
        $stmt = $conexao->prepare(
            "SELECT mp.id, men.servico, men.mes, men.ano,
                    mp.valor_pago, mp.metodo, mp.referencia,
                    mp.data_pagamento, mp.estado
             FROM mensalidade_pagamento mp
             JOIN mensalidade men ON men.id = mp.id_mensalidade
             WHERE men.id_morador = ?
             ORDER BY mp.data_pagamento DESC"
        );
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        $stmt->close();
        break;

    case 'resumo_financeiro':
        // ✅ CORRIGIDO: Usar prepared statements em vez de interpolação
        $stmt = $conexao->prepare("
            SELECT COALESCE(SUM(valor),0) as total 
            FROM mensalidade 
            WHERE id_morador=? AND estado='pendente'
        ");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $total_pend = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();

        $stmt = $conexao->prepare("
            SELECT COALESCE(SUM(valor_pago),0) as total 
            FROM mensalidade_pagamento mp
            JOIN mensalidade men ON men.id = mp.id_mensalidade
            WHERE men.id_morador=? AND mp.estado='confirmado'
        ");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $total_pago = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();

        $stmt = $conexao->prepare("
            SELECT COUNT(*) as total 
            FROM mensalidade 
            WHERE id_morador=? AND estado='pago'
        ");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $meses_pagos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();

        echo json_encode(['sucesso' => true, 'dados' => [
            'pendente'    => (float)$total_pend,
            'pago'        => (float)$total_pago,
            'meses_pagos' => (int)$meses_pagos,
        ]]);
        break;

    case 'vizinhos':
        // Listar moradores do mesmo bloco
        $stmt = $conexao->prepare(
            "SELECT m.nome, a.codigo as apartamento, a.tipologia
             FROM morador m
             JOIN morador_apartamento ma ON ma.id_morador = m.id AND ma.activo = 1
             JOIN apartamento a ON a.id = ma.id_apartamento
             JOIN bloco b ON b.id = a.id_bloco
             WHERE b.id = (
                SELECT b2.id FROM morador_apartamento ma2
                JOIN apartamento a2 ON a2.id = ma2.id_apartamento
                JOIN bloco b2 ON b2.id = a2.id_bloco
                WHERE ma2.id_morador = ? AND ma2.activo = 1 LIMIT 1
             ) AND m.id != ?
             ORDER BY m.nome"
        );
        $stmt->bind_param("ii", $id_morador, $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        $stmt->close();
        break;

    case 'visitas':
        $stmt = $conexao->prepare("SELECT nome_visitante, data_prevista, hora_prevista, estado, codigo_acesso FROM visita WHERE id_morador = ? ORDER BY data_prevista DESC");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        $stmt->close();
        break;

    case 'novo_agendamento_visita':
        $nome = $_POST['nome'] ?? '';
        $data = $_POST['data'] ?? '';
        $hora = $_POST['hora'] ?? null;
        if (!$nome || !$data) { echo json_encode(['sucesso' => false, 'erro' => 'Campos obrigatórios']); exit; }
        
        // Pegar ID do apartamento activo
        $res_apt = mysqli_query($conexao, "SELECT id_apartamento FROM morador_apartamento WHERE id_morador=$id_morador AND activo=1 LIMIT 1");
        $id_apt = mysqli_fetch_row($res_apt)[0] ?? 0;
        
        $codigo = strtoupper(substr(md5($nome . time()), 0, 6));

        $stmt = $conexao->prepare("INSERT INTO visita (id_morador, id_apartamento, nome_visitante, data_prevista, hora_prevista, estado, codigo_acesso) VALUES (?, ?, ?, ?, ?, 'autorizado', ?)");
        $stmt->bind_param("iissss", $id_morador, $id_apt, $nome, $data, $hora, $codigo);
        if ($stmt->execute()) echo json_encode(['sucesso' => true]);
        else echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        $stmt->close();
        break;

    case 'agendamentos_area':
        $stmt = $conexao->prepare("SELECT area_comum, data_evento, hora_inicio, hora_fim, estado FROM agendamento WHERE id_morador = ? ORDER BY data_evento DESC");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        $stmt->close();
        break;

    case 'novo_agendamento_area':
        $area = $_POST['area'] ?? '';
        $data = $_POST['data'] ?? '';
        $inicio = $_POST['inicio'] ?? '';
        $fim = $_POST['fim'] ?? '';
        if (!$area || !$data || !$inicio || !$fim) { echo json_encode(['sucesso' => false, 'erro' => 'Campos obrigatórios']); exit; }

        $stmt = $conexao->prepare("INSERT INTO agendamento (id_morador, area_comum, data_evento, hora_inicio, hora_fim, estado) VALUES (?, ?, ?, ?, ?, 'pendente')");
        $stmt->bind_param("issss", $id_morador, $area, $data, $inicio, $fim);
        if ($stmt->execute()) echo json_encode(['sucesso' => true]);
        else echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        $stmt->close();
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Acção desconhecida']);
}
