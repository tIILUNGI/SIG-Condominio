<?php
/**
 * api_casas.php — API para gestão de apartamentos (JSON)
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

// Verificar permissão de admin
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

if (!$conexao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sem BD']);
    exit;
}

$acao = $_GET['acao'] ?? 'listar';

switch ($acao) {
    case 'listar':
        $sql = "SELECT a.id, a.numero, a.andar, a.tipologia, a.estado, a.codigo,
                       b.letra as bloco, a.obs
                FROM apartamento a
                LEFT JOIN bloco b ON a.id_bloco = b.id
                ORDER BY b.letra, a.numero";
        $result = mysqli_query($conexao, $sql);
        $casas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $casas[] = $row;
        }
        echo json_encode(['sucesso' => true, 'dados' => $casas]);
        break;

    case 'disponiveis':
        $sql = "SELECT a.id, a.numero, a.andar, a.tipologia, a.estado, a.codigo,
                       b.letra as bloco
                FROM apartamento a
                LEFT JOIN bloco b ON a.id_bloco = b.id
                WHERE a.estado = 'Disponivel'
                ORDER BY b.letra, a.numero";
        $result = mysqli_query($conexao, $sql);
        $casas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $casas[] = $row;
        }
        echo json_encode(['sucesso' => true, 'dados' => $casas]);
        break;

    case 'adicionar':
        $id_bloco = intval($_POST['id_bloco'] ?? 1);
        $numero = trim($_POST['numero'] ?? '');
        $andar = intval($_POST['andar'] ?? 0);
        $tipologia = $_POST['tipologia'] ?? 'V3';
        
        if (!$numero) {
            echo json_encode(['sucesso' => false, 'erro' => 'Número obrigatório']);
            exit;
        }

        // Buscar letra do bloco
        $bloco_stmt = $conexao->prepare("SELECT letra FROM bloco WHERE id = ?");
        $bloco_stmt->bind_param("i", $id_bloco);
        $bloco_stmt->execute();
        $bloco_result = $bloco_stmt->get_result();
        $bloco_row = $bloco_result->fetch_assoc();
        $bloco_stmt->close();
        
        $letra_bloco = $bloco_row['letra'] ?? 'A';
        $codigo = strtoupper($letra_bloco) . '-' . $numero;

        $stmt = $conexao->prepare(
            "INSERT INTO apartamento (id_bloco, numero, andar, tipologia, estado, codigo) 
             VALUES (?, ?, ?, ?, 'Disponivel', ?)"
        );
        $stmt->bind_param("iisss", $id_bloco, $numero, $andar, $tipologia, $codigo);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'id' => $stmt->insert_id, 'codigo' => $codigo]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        }
        $stmt->close();
        break;

    case 'ocupar':
        $id_apartamento = intval($_POST['id_apartamento'] ?? 0);
        if (!$id_apartamento) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do apartamento obrigatório']);
            exit;
        }

        $stmt = $conexao->prepare("UPDATE apartamento SET estado = 'Ocupado' WHERE id = ?");
        $stmt->bind_param("i", $id_apartamento);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        }
        $stmt->close();
        break;

    case 'desocupar':
        $id_apartamento = intval($_POST['id_apartamento'] ?? 0);
        if (!$id_apartamento) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do apartamento obrigatório']);
            exit;
        }

        $stmt = $conexao->prepare("UPDATE apartamento SET estado = 'Disponivel' WHERE id = ?");
        $stmt->bind_param("i", $id_apartamento);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação desconhecida']);
}
?>