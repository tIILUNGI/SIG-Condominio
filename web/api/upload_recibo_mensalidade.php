<?php
/**
 * upload_recibo_mensalidade.php
 * Endpoint dedicado para upload do comprovativo (recibo) de uma mensalidade.
 *
 * Regras do teu pedido:
 * - Morador envia ficheiro (>= 1MB)
 * - O pagamento fica PENDENTE para aprovação do admin
 * - Administração aprova e muda o status para pago
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

include('conexao.php');

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'morador') {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

if (!$conexao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sem BD']);
    exit;
}

$morador_id = intval($_SESSION['id'] ?? 0);
if ($morador_id <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão inválida']);
    exit;
}

$id_mens = intval($_POST['id_mensalidade'] ?? 0);
if ($id_mens <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID mensalidade inválido']);
    exit;
}

if (!isset($_FILES['recibo']) || !is_uploaded_file($_FILES['recibo']['tmp_name'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Recibo/ficheiro não enviado']);
    exit;
}

$err = $_FILES['recibo']['error'] ?? 0;
if ($err !== UPLOAD_ERR_OK) {
    echo json_encode(['sucesso' => false, 'erro' => 'Upload falhou (código '.$err.')']);
    exit;
}

$nomeOriginal = $_FILES['recibo']['name'] ?? 'recibo';
$tamanho = intval($_FILES['recibo']['size'] ?? 0);
$tmp = $_FILES['recibo']['tmp_name'];

// Requisito: pelo menos 1MB
$minBytes = 1024 * 1024;
if ($tamanho < $minBytes) {
    echo json_encode(['sucesso' => false, 'erro' => 'O recibo deve ter pelo menos 1MB']);
    exit;
}

// Limite prático (ajustável). Por padrão, confia no php.ini também.
$maxBytes = 10 * 1024 * 1024; // 10MB
if ($tamanho > $maxBytes) {
    echo json_encode(['sucesso' => false, 'erro' => 'O recibo excede o limite (10MB)']);
    exit;
}

$ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
$extPermitidas = ['jpg','jpeg','png','pdf'];
if (!in_array($ext, $extPermitidas, true)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Formato inválido. Use JPG/PNG/PDF']);
    exit;
}

// Garantir que a mensalidade pertence ao morador
$stmt = $conexao->prepare("SELECT id FROM mensalidade WHERE id=? AND id_morador=? LIMIT 1");
$stmt->bind_param('ii', $id_mens, $morador_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'Mensalidade não pertence ao morador']);
    exit;
}
$stmt->close();

// Pasta de uploads (criar se não existir)
$baseDir = __DIR__ . '/../uploads/recibos_mensalidades';
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0775, true);
}

// Nome do ficheiro: id_mensalidade_morador_data_random.ext
$rand = bin2hex(random_bytes(8));
$safeExt = $ext ?: 'pdf';
$filename = 'mens_'.$id_mens.'_mor_'.$morador_id.'_'.date('Ymd_His').'_'.$rand.'.'.$safeExt;
$destPath = $baseDir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($tmp, $destPath)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha ao guardar o ficheiro']);
    exit;
}

// Guardar recibo e marcar como pendente.
// Como o teu DB não tem campo para guardar recibo, usamos uma tabela extra.
// (Será criada automaticamente se não existir.)

$create = "CREATE TABLE IF NOT EXISTS mensalidade_recibo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mensalidade INT NOT NULL,
    id_morador INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_ext VARCHAR(20) NOT NULL,
    file_size INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_receipt (id_mensalidade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conexao, $create);

$ins = $conexao->prepare(
    "INSERT INTO mensalidade_recibo (id_mensalidade, id_morador, file_name, file_path, file_ext, file_size)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$file_path = 'uploads/recibos_mensalidades/'.$filename; // path relativo para servir/mostrar
$ins->bind_param('iisssi', $id_mens, $morador_id, $nomeOriginal, $file_path, $safeExt, $tamanho);
$ok = $ins->execute();
if (!$ok) {
    // Possível: já existe por causa do UNIQUE. Fazemos update.
    $ins->close();
    $upd = $conexao->prepare(
        "UPDATE mensalidade_recibo SET id_morador=?, file_name=?, file_path=?, file_ext=?, file_size=?, criado_em=CURRENT_TIMESTAMP
         WHERE id_mensalidade=?"
    );
    $upd->bind_param('issssi', $morador_id, $nomeOriginal, $file_path, $safeExt, $tamanho, $id_mens);
    $upd->execute();
    $upd->close();
}

// Atualizar mensalidade para PENDENTE (em vez de pago)
// Dependendo da tua lógica, estado pode ser 'pendente' (existe no teu front).
$up = $conexao->prepare("UPDATE mensalidade SET estado='pendente', actualizado_em=NOW() WHERE id=? AND id_morador=?");
$up->bind_param('ii', $id_mens, $morador_id);
$up->execute();
$up->close();

// Registar uma linha em mensalidade_pagamento se ainda não existir (estado='pendente').
// Se existir, atualiza.
$check = $conexao->prepare("SELECT id FROM mensalidade_pagamento WHERE id_mensalidade=? AND id_morador=? LIMIT 1");
$check->bind_param('ii', $id_mens, $morador_id);
$check->execute();
$qr = $check->get_result();
if ($qr && $qr->num_rows > 0) {
    $pid = intval($qr->fetch_assoc()['id']);
    $updPay = $conexao->prepare("UPDATE mensalidade_pagamento SET estado='pendente', metodo='Upload de recibo', referencia='' WHERE id=?");
    $updPay->bind_param('i', $pid);
    $updPay->execute();
    $updPay->close();
} else {
    // Tenta inserir apenas com as colunas que teu sistema já usa.
    // O teu admin usa mensalidade_pagamento (id, id_mensalidade, valor_pago, metodo, referencia, data_pagamento, estado, notas_admin)
    $stmtVal = $conexao->prepare("SELECT valor FROM mensalidade WHERE id=? LIMIT 1");
    $stmtVal->bind_param('i', $id_mens);
    $stmtVal->execute();
    $rv = $stmtVal->get_result()->fetch_assoc();
    $stmtVal->close();

    $valor = floatval($rv['valor'] ?? 0);

    $insPay = $conexao->prepare(
        "INSERT INTO mensalidade_pagamento (id_mensalidade, valor_pago, metodo, referencia, estado)
         VALUES (?, ?, ?, ?, 'pendente')"
    );
    $metodo = 'Upload de recibo';
    $ref = '';
    $insPay->bind_param('idss', $id_mens, $valor, $metodo, $ref);
    $insPay->execute();
    $insPay->close();
}

echo json_encode(['sucesso' => true]);
exit;

