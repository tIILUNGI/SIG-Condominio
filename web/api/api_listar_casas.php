<?php
/**
 * api_listar_casas.php — Listar apartamentos para interface admin (JSON)
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

$sql = "SELECT a.id, a.codigo, a.numero, a.andar, a.tipologia, a.estado, 
               b.letra as bloco,
               m.nome as morador_nome
        FROM apartamento a
        LEFT JOIN bloco b ON a.id_bloco = b.id
        LEFT JOIN morador_apartamento ma ON a.id = ma.id_apartamento AND ma.activo = 1
        LEFT JOIN morador m ON ma.id_morador = m.id
        ORDER BY b.letra, a.numero";

$result = mysqli_query($conexao, $sql);
$casas = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $casas[] = $row;
    }
}

echo json_encode(['sucesso' => true, 'dados' => $casas]);
?>