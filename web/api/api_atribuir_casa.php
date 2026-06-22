<?php
/**
 * api_atribuir_casa.php — Atribuir apartamento a morador (JSON)
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

if (!$conexao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sem BD']);
    exit;
}

$id_morador = intval($_POST['id_morador'] ?? 0);
$id_apartamento = intval($_POST['id_apartamento'] ?? 0);

if (!$id_morador || !$id_apartamento) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID morador e apartamento obrigatórios']);
    exit;
}

mysqli_begin_transaction($conexao);

try {
    // Verificar se apartamento está disponível
    $apt_check = $conexao->prepare("SELECT estado FROM apartamento WHERE id = ?");
    $apt_check->bind_param("i", $id_apartamento);
    $apt_check->execute();
    $apt_result = $apt_check->get_result();
    if ($apt_result->num_rows === 0) {
        throw new Exception('Apartamento não encontrado');
    }
    $apt_info = $apt_result->fetch_assoc();
    if ($apt_info['estado'] !== 'Disponivel') {
        throw new Exception('Apartamento já está ocupado');
    }
    $apt_check->close();

    // Desativar associação anterior do morador (se houver)
    $stmt = $conexao->prepare("UPDATE morador_apartamento SET activo = 0 WHERE id_morador = ? AND activo = 1");
    $stmt->bind_param("i", $id_morador);
    $stmt->execute();
    $stmt->close();

    // Criar nova associação
    $data_entrada = date('Y-m-d');
    $stmt = $conexao->prepare(
        "INSERT INTO morador_apartamento (id_morador, id_apartamento, data_entrada, activo) 
         VALUES (?, ?, ?, 1)"
    );
    $stmt->bind_param("iis", $id_morador, $id_apartamento, $data_entrada);
    $stmt->execute();
    $stmt->close();

    // Marcar apartamento como ocupado
    $stmt = $conexao->prepare("UPDATE apartamento SET estado = 'Ocupado' WHERE id = ?");
    $stmt->bind_param("i", $id_apartamento);
    $stmt->execute();
    $stmt->close();

    // Gerar mensalidades para os próximos 12 meses se for novo morador sem mensalidades
    $mens_check = $conexao->prepare("SELECT COUNT(*) as total FROM mensalidade WHERE id_morador = ?");
    $mens_check->bind_param("i", $id_morador);
    $mens_check->execute();
    $mens_count = $mens_check->get_result()->fetch_assoc()['total'];
    $mens_check->close();

    if ($mens_count == 0) {
        $mensalidade_base = 140000;
        $apt_stmt = $conexao->prepare("SELECT codigo FROM apartamento WHERE id = ?");
        $apt_stmt->bind_param("i", $id_apartamento);
        $apt_stmt->execute();
        $apt_info = $apt_stmt->get_result()->fetch_assoc();
        $apt_stmt->close();

        $data_atual = new DateTime();
        for ($i = 0; $i < 12; $i++) {
            $mes = $data_atual->format('n');
            $ano = $data_atual->format('Y');
            $vencimento = $data_atual->format('Y-m-t');

            $stmt = $conexao->prepare(
                "INSERT INTO mensalidade (id_morador, id_apartamento, servico, mes, ano, valor, vencimento, estado) 
                 VALUES (?, ?, 'Quota Condominal', ?, ?, ?, ?, 'pendente')"
            );
            $stmt->bind_param("iiiisd", $id_morador, $id_apartamento, $mes, $ano, $mensalidade_base, $vencimento);
            $stmt->execute();
            $stmt->close();

            $data_atual->modify('+1 month');
        }
    }

    mysqli_commit($conexao);
    echo json_encode(['sucesso' => true, 'mensalidades_criadas' => $mens_count == 0 ? 12 : 0]);
} catch (Exception $e) {
    mysqli_rollback($conexao);
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}