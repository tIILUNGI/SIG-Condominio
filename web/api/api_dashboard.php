<?php
/**
 * api_dashboard.php — Dados para o Painel Admin (JSON)
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

if (!$conexao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sem BD']);
    exit;
}

$acao = $_GET['acao'] ?? 'resumo';

switch ($acao) {

    case 'resumo':
        // KPIs do dashboard
        $r = [];

        $r['total_moradores']   = mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM morador"))[0] ?? 0;
        $r['total_admins']      = mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM administrador"))[0] ?? 0;
        $r['total_apartamentos'] = mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM apartamento"))[0] ?? 0;
        $r['apartamentos_disponiveis'] = mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM apartamento WHERE estado='Disponivel'"))[0] ?? 0;
        $r['apartamentos_ocupados'] = mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM apartamento WHERE estado='Ocupado' OR estado='ocupada'"))[0] ?? 0;
        $r['mensalidades_pendentes'] = mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM mensalidade WHERE estado='pendente'"))[0] ?? 0;
        $r['receitas_mes'] = mysqli_fetch_row(mysqli_query($conexao, "SELECT COALESCE(SUM(valor_pago),0) FROM mensalidade_pagamento WHERE MONTH(data_pagamento)=MONTH(NOW()) AND YEAR(data_pagamento)=YEAR(NOW()) AND estado='confirmado'"))[0] ?? 0;
        $r['localizacao'] = "Viana, Luanda, Angola";
        $r['apoio'] = "+244 923 000 000";

        echo json_encode(['sucesso' => true, 'dados' => $r]);
        break;

    case 'casas':
        $sql = "SELECT a.id, b.letra as bloco, a.numero, a.andar, a.tipologia, a.estado, a.codigo
                FROM apartamento a
                JOIN bloco b ON b.id = a.id_bloco
                ORDER BY b.letra, a.numero";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'moradores':
        $sql = "SELECT m.id, m.nome, m.email, m.telefone, m.numbi, m.estado_conta,
                       a.codigo as apartamento
                FROM morador m
                LEFT JOIN morador_apartamento ma ON ma.id_morador = m.id AND ma.activo = 1
                LEFT JOIN apartamento a ON a.id = ma.id_apartamento
                ORDER BY m.nome";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'admins':
        $sql = "SELECT id, nome, email, funcao, activo, ultimo_login FROM administrador ORDER BY nome";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'mensalidades':
        $sql = "SELECT men.id, mor.nome, a.codigo as apartamento, men.servico,
                       men.mes, men.ano, men.valor, men.vencimento, men.estado
                FROM mensalidade men
                JOIN morador mor ON mor.id = men.id_morador
                JOIN apartamento a ON a.id = men.id_apartamento
                ORDER BY men.ano DESC, men.mes DESC";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'pagamentos':
        $sql = "SELECT mp.id, mor.nome as morador, a.codigo as apartamento,
                       mp.valor_pago, mp.metodo, mp.referencia,
                       mp.data_pagamento, mp.estado, mp.notas_admin
                FROM mensalidade_pagamento mp
                JOIN mensalidade men ON men.id = mp.id_mensalidade
                JOIN morador mor ON mor.id = men.id_morador
                JOIN apartamento a ON a.id = men.id_apartamento
                ORDER BY mp.data_pagamento DESC";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'confirmar_pagamento':
        $id_pay   = intval($_POST['id'] ?? 0);
        $id_admin = intval($_SESSION['id'] ?? 0);
        $notas    = trim($_POST['notas'] ?? '');
        $estado   = $_POST['estado'] ?? 'confirmado'; // confirmado | rejeitado

        if (!$id_pay) { echo json_encode(['sucesso'=>false,'erro'=>'ID inválido']); exit; }

        $stmt = $conexao->prepare("UPDATE mensalidade_pagamento SET estado=?, confirmado_por=?, notas_admin=? WHERE id=?");
        $stmt->bind_param("sisi", $estado, $id_admin, $notas, $id_pay);

        if ($stmt->execute()) {
            // Se confirmado, actualiza a mensalidade para "pago"
            if ($estado === 'confirmado') {
                $mens_id = mysqli_fetch_row(mysqli_query($conexao, "SELECT id_mensalidade FROM mensalidade_pagamento WHERE id=$id_pay"))[0] ?? 0;
                if ($mens_id) {
                    mysqli_query($conexao, "UPDATE mensalidade SET estado='pago' WHERE id=$mens_id");
                }
            }
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        }
        $stmt->close();
        break;

    case 'visitas':
        $sql = "SELECT v.id, m.nome as morador, a.codigo as apartamento, v.nome_visitante,
                       v.data_prevista, v.hora_prevista, v.estado, v.codigo_acesso
                FROM visita v
                JOIN morador m ON m.id = v.id_morador
                JOIN apartamento a ON a.id = v.id_apartamento
                ORDER BY v.data_prevista DESC";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'agendamentos_area':
        $sql = "SELECT age.id, m.nome as morador, age.area_comum, age.data_evento,
                       age.hora_inicio, age.hora_fim, age.estado
                FROM agendamento age
                JOIN morador m ON m.id = age.id_morador
                ORDER BY age.data_evento DESC";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'validar_agendamento':
        $id     = intval($_POST['id'] ?? 0);
        $tipo   = $_POST['tipo'] ?? 'area'; // area | visita
        $estado = $_POST['estado'] ?? 'confirmado'; // confirmado | cancelado / negado
        
        if (!$id) { echo json_encode(['sucesso'=>false,'erro'=>'ID inválido']); exit; }
        
        $tabela = ($tipo === 'area') ? 'agendamento' : 'visita';
        $col_estado = ($tipo === 'area') ? 'estado' : 'estado'; // Ambos usam 'estado'
        
        $stmt = $conexao->prepare("UPDATE $tabela SET estado=? WHERE id=?");
        $stmt->bind_param("si", $estado, $id);
        if ($stmt->execute()) echo json_encode(['sucesso' => true]);
        else echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        $stmt->close();
        break;

    case 'processar_morador':
        $id_morador = intval($_POST['id_morador'] ?? 0);
        $id_apartamento = intval($_POST['id_apartamento'] ?? 0);
        $estado = $_POST['estado'] ?? 'Activo';

        if (!$id_morador) { echo json_encode(['sucesso'=>false,'erro'=>'ID Morador inválido']); exit; }

        mysqli_begin_transaction($conexao);
        try {
            // Actualizar estado do morador
            $stmt = $conexao->prepare("UPDATE morador SET estado_conta = ? WHERE id = ?");
            $stmt->bind_param("si", $estado, $id_morador);
            $stmt->execute();
            $stmt->close();

            if ($id_apartamento > 0) {
                // Desactivar atribuições anteriores
                $stmt = $conexao->prepare("UPDATE morador_apartamento SET activo = 0 WHERE id_morador = ?");
                $stmt->bind_param("i", $id_morador);
                $stmt->execute();
                $stmt->close();

                // Nova atribuição
                $stmt = $conexao->prepare("INSERT INTO morador_apartamento (id_morador, id_apartamento, data_entrada, activo) VALUES (?, ?, NOW(), 1)");
                $stmt->bind_param("ii", $id_morador, $id_apartamento);
                $stmt->execute();
                $stmt->close();

                // Marcar casa como ocupada
                $stmt = $conexao->prepare("UPDATE apartamento SET estado = 'Ocupado' WHERE id = ?");
                $stmt->bind_param("i", $id_apartamento);
                $stmt->execute();
                $stmt->close();
            }

            mysqli_commit($conexao);
            echo json_encode(['sucesso' => true]);
        } catch (Exception $e) {
            mysqli_rollback($conexao);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        break;

    case 'eliminar_morador':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['sucesso'=>false]); exit; }
        // Remove associações e o morador
        mysqli_query($conexao, "DELETE FROM morador_apartamento WHERE id_morador=$id");
        mysqli_query($conexao, "DELETE FROM mensalidade_pagamento WHERE id_mensalidade IN (SELECT id FROM mensalidade WHERE id_morador=$id)");
        mysqli_query($conexao, "DELETE FROM mensalidade WHERE id_morador=$id");
        mysqli_query($conexao, "DELETE FROM chat_mensagem WHERE id_morador=$id");
        $res = mysqli_query($conexao, "DELETE FROM morador WHERE id=$id");
        echo json_encode(['sucesso' => $res]);
        break;

    case 'eliminar_casa':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['sucesso'=>false]); exit; }
        mysqli_query($conexao, "DELETE FROM morador_apartamento WHERE id_apartamento=$id");
        $res = mysqli_query($conexao, "DELETE FROM apartamento WHERE id=$id");
        echo json_encode(['sucesso' => $res]);
        break;

    case 'eliminar_admin':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['sucesso'=>false]); exit; }
        $res = mysqli_query($conexao, "DELETE FROM administrador WHERE id=$id");
        echo json_encode(['sucesso' => $res]);
        break;
    
    case 'blocos':
        $res = mysqli_query($conexao, "SELECT id, letra, descricao FROM bloco ORDER BY letra");
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Acção desconhecida']);
}
