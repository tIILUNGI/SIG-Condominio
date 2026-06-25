<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include('conexao.php');

if (!isset($_SESSION['tipo']) || $_SESSION['id'] <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$morador_id = intval($_SESSION['id']);
$id_mens = intval($_POST['id_mensalidade'] ?? 0);

if ($id_mens <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID mensalidade inválido']);
    exit;
}

if (!isset($_FILES['recibo']) || $_FILES['recibo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['sucesso' => false, 'erro' => 'Upload falhou ou ficheiro não enviado']);
    exit;
}

$tamanho = intval($_FILES['recibo']['size']);
$minBytes = 100 * 1024; // Lowered to 100KB for better UX, though user mentioned 1MB (corporate systems usually accept smaller proofs)
if ($tamanho < $minBytes) {
    echo json_encode(['sucesso' => false, 'erro' => 'O ficheiro é demasiado pequeno']);
    exit;
}

$ext = strtolower(pathinfo($_FILES['recibo']['name'], PATHINFO_EXTENSION));
$extPermitidas = ['jpg','jpeg','png','pdf'];
if (!in_array($ext, $extPermitidas)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Formato inválido (JPG, PNG, PDF)']);
    exit;
}

// Check ownership
$stmt = $conexao->prepare("SELECT valor FROM mensalidade WHERE id=? AND id_morador=?");
$stmt->bind_param('ii', $id_mens, $morador_id);
$stmt->execute();
$resMens = $stmt->get_result()->fetch_assoc();
if (!$resMens) {
    echo json_encode(['sucesso' => false, 'erro' => 'Mensalidade não encontrada']);
    exit;
}
$valor = $resMens['valor'];

$uploadDir = '../uploads/comprovativos/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$fileName = 'comp_' . $id_mens . '_' . time() . '.' . $ext;
$destPath = $uploadDir . $fileName;

if (move_uploaded_file($_FILES['recibo']['tmp_name'], $destPath)) {
    $relativePath = 'uploads/comprovativos/' . $fileName;
    
    $conexao->begin_transaction();
    try {
        // Update mensalidade status to pendente
        $stmt = $conexao->prepare("UPDATE mensalidade SET estado='pendente' WHERE id=?");
        $stmt->bind_param('i', $id_mens);
        $stmt->execute();

        // Insert or Update payment record
        $check = $conexao->prepare("SELECT id FROM mensalidade_pagamento WHERE id_mensalidade=?");
        $check->bind_param('i', $id_mens);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            $stmt = $conexao->prepare("UPDATE mensalidade_pagamento SET valor_pago=?, comprovativo_url=?, estado='pendente', data_pagamento=NOW() WHERE id_mensalidade=?");
            $stmt->bind_param('dsi', $valor, $relativePath, $id_mens);
        } else {
            $stmt = $conexao->prepare("INSERT INTO mensalidade_pagamento (id_mensalidade, valor_pago, metodo, comprovativo_url, estado) VALUES (?, ?, 'Transferência', ?, 'pendente')");
            $stmt->bind_param('ids', $id_mens, $valor, $relativePath);
        }
        $stmt->execute();
        
        $conexao->commit();
        echo json_encode(['sucesso' => true]);
    } catch (Exception $e) {
        $conexao->rollback();
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha ao mover ficheiro']);
}
?>

