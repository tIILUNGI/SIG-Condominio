<?php
/**
 * dadospessoais.php — Registo de morador via admin
 */
session_start();
include("conexao.php");

if (!$conexao) {
    die("Erro de conexão com a base de dados");
}

$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$nasc = $_POST['nasc'] ?? '';
$nacionalidade = trim($_POST['nacionalidade'] ?? 'Angolana');
$morada = trim($_POST['morada'] ?? '');
$numbi = trim($_POST['numbi'] ?? '');
$emissao = $_POST['emissao'] ?? '';
$validade = $_POST['validade'] ?? '';
$locale = trim($_POST['locale'] ?? '');
$id_apartamento = intval($_POST['id_apartamento'] ?? 0);
$senha_temporaria = $_POST['senha'] ?? '';

if (!$nome || !$telefone || !$email || !$nasc || !$morada || !$numbi || !$emissao || !$validade || !$locale) {
    header("Location: ../dashboard.php?tab=registos&erro=campos");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../dashboard.php?tab=registos&erro=email");
    exit;
}

// Verificar se BI/email já existe
$chk = $conexao->prepare("SELECT id FROM morador WHERE numbi = ? OR email = ? LIMIT 1");
$chk->bind_param("ss", $numbi, $email);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    header("Location: ../dashboard.php?tab=registos&erro=duplicado");
    exit;
}
$chk->close();

// Gerar senha hash
$senha_hash = password_hash($senha_temporaria ?: 'Temp@123456', PASSWORD_DEFAULT);

mysqli_begin_transaction($conexao);

try {
    // Inserir morador
    $stmt = $conexao->prepare(
        "INSERT INTO morador (nome, telefone, email, nasc, nacionalidade, morada_anterior, numbi, emissao_bi, validade_bi, locale_bi, senha_hash) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssssssssss", $nome, $telefone, $email, $nasc, $nacionalidade, $morada, $numbi, $emissao, $validade, $locale, $senha_hash);
    $stmt->execute();
    $id_morador = $stmt->insert_id;
    $stmt->close();

    // Associar apartamento se fornecido
    if ($id_apartamento > 0) {
        // Verificar se apartamento está disponível
        $apt_check = $conexao->prepare("SELECT estado FROM apartamento WHERE id = ? AND estado = 'Disponivel'");
        $apt_check->bind_param("i", $id_apartamento);
        $apt_check->execute();
        if ($apt_check->get_result()->num_rows === 0) {
            throw new Exception('Apartamento não disponível');
        }
        $apt_check->close();

        // Associar morador ao apartamento
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
        $mensalidade_base = 140000;
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
    header("Location: ../dashboard.php?tab=registos&ok=morador_adicionado");
} catch (Exception $e) {
    mysqli_rollback($conexao);
    header("Location: ../dashboard.php?tab=registos&erro=" . urlencode($e->getMessage()));
}
exit;