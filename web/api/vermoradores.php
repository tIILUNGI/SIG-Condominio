<?php
/**
 * vermoradores.php — Listar moradores para interface admin (JSON)
 */
include "conexao.php";

$sql = "SELECT m.id, m.nome, m.numbi, m.telefone, m.email, m.nasc, m.nacionalidade, 
               m.morada_anterior as morada, m.estado_conta as activo,
               a.codigo as apartamento
        FROM morador m
        LEFT JOIN morador_apartamento ma ON m.id = ma.id_morador AND ma.activo = 1
        LEFT JOIN apartamento a ON ma.id_apartamento = a.id
        ORDER BY m.nome";

$resultado = $conexao->query($sql);

$moradores = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $moradores[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode(['sucesso' => true, 'dados' => $moradores]);
?>