<?php
include("conexao.php");

if (!$conexao) {
    die("Erro de conexão com a base de dados");
}

$id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID inválido");
}

$stmt = $conexao->prepare("UPDATE mensalidade SET estado='pago' WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: pages/morador-gest.html?ok=pagamento");
exit();
?>