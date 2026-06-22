<?php
/**
 * api_moradores.php — API para gestão de moradores (JSON)
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
        $sql = "SELECT m.id, m.nome, m.email, m.telefone, m.numbi, m.estado_conta,
                       a.codigo as apartamento, b.letra as bloco, a.estado as apt_estado
                FROM morador m
                LEFT JOIN morador_apartamento ma ON ma.id_morador = m.id AND ma.activo = 1
                LEFT JOIN apartamento a ON a.id = ma.id_apartamento
                LEFT JOIN bloco b ON b.id = a.id_bloco
                ORDER BY m.nome";
        $result = mysqli_query($conexao, $sql);
        $moradores = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $moradores[] = $row;
        }
        echo json_encode(['sucesso' => true, 'dados' => $moradores]);
        break;

    case 'adicionar':
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $numbi = trim($_POST['numbi'] ?? '');
        $id_apartamento = intval($_POST['id_apartamento'] ?? 0);
        $senha = $_POST['senha'] ?? '';
        $nasc = $_POST['nasc'] ?? '';
        $morada = trim($_POST['morada'] ?? '');
        $emissao_bi = $_POST['emissao_bi'] ?? '';
        $validade_bi = $_POST['validade_bi'] ?? '';
        $locale_bi = trim($_POST['locale_bi'] ?? '');
        
        // Validação
        if (!$nome || !$email || !$telefone || !$numbi || !$senha || !$nasc) {
            echo json_encode(['sucesso' => false, 'erro' => 'Campos obrigatórios em falta']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Email inválido']);
            exit;
        }

        if (strlen($senha) < 6) {
            echo json_encode(['sucesso' => false, 'erro' => 'Senha deve ter pelo menos 6 caracteres']);
            exit;
        }

        // Verificar se BI/email já existe
        $chk = $conexao->prepare("SELECT id FROM morador WHERE numbi = ? OR email = ? LIMIT 1");
        $chk->bind_param("ss", $numbi, $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'BI ou Email já registado']);
            exit;
        }
        $chk->close();

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Garantir conta activa para permitir login
        $estado_conta = 'Activo';
        
        // Iniciar transação
        mysqli_begin_transaction($conexao);
        
        try {
            // Inserir morador
            $stmt = $conexao->prepare(
"INSERT INTO morador (nome, email, telefone, numbi, senha_hash, nasc, morada_anterior, emissao_bi, validade_bi, locale_bi, estado_conta) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"

            );
$stmt->bind_param("sssssssssss", $nome, $email, $telefone, $numbi, $senha_hash, $nasc, $morada, $emissao_bi, $validade_bi, $locale_bi, $estado_conta);

            $stmt->execute();
            $id_morador = $stmt->insert_id;
            $stmt->close();

            // Associar ao apartamento se fornecido
            if ($id_apartamento > 0) {
                // Verificar se apartamento está disponível
                $apt_check = $conexao->prepare("SELECT estado FROM apartamento WHERE id = ? AND estado = 'Disponivel'");
                $apt_check->bind_param("i", $id_apartamento);
                $apt_check->execute();
                if ($apt_check->get_result()->num_rows === 0) {
                    throw new Exception('Apartamento não disponível');
                }
                $apt_check->close();

                // Criar associação morador-apartamento
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

                // Gerar mensalidades para os próximos 12 meses
                $apt_stmt = $conexao->prepare("SELECT codigo FROM apartamento WHERE id = ?");
                $apt_stmt->bind_param("i", $id_apartamento);
                $apt_stmt->execute();
                $apt_info = $apt_stmt->get_result()->fetch_assoc();
                $apt_stmt->close();

                $mensalidade_base = 140000; // Valor base
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
            echo json_encode(['sucesso' => true, 'id' => $id_morador, 'mensalidades_criadas' => $id_apartamento ? 12 : 0]);
        } catch (Exception $e) {
            mysqli_rollback($conexao);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        break;

    case 'atualizar':
        $id = intval($_POST['id'] ?? 0);
        $estado_conta = $_POST['estado_conta'] ?? 'Activo';
        
        if (!$id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID obrigatório']);
            exit;
        }

        $stmt = $conexao->prepare("UPDATE morador SET estado_conta = ? WHERE id = ?");
        $stmt->bind_param("si", $estado_conta, $id);
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $conexao->error]);
        }
        $stmt->close();
        break;

    case 'remover':
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID obrigatório']);
            exit;
        }

        mysqli_begin_transaction($conexao);
        try {
            // Remover associação ao apartamento
            $stmt = $conexao->prepare("
                UPDATE morador_apartamento ma 
                JOIN apartamento a ON a.id = ma.id_apartamento 
                SET a.estado = 'Disponivel' 
                WHERE ma.id_morador = ? AND ma.activo = 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Desativar morador
            $stmt = $conexao->prepare("UPDATE morador SET estado_conta = 'Inactivo' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            mysqli_commit($conexao);
            echo json_encode(['sucesso' => true]);
        } catch (Exception $e) {
            mysqli_rollback($conexao);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação desconhecida']);
}