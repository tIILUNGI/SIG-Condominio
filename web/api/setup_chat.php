<?php
include("conexao.php");
$queries = [
    "CREATE TABLE IF NOT EXISTS comunicado (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        conteudo TEXT NOT NULL,
        tipo ENUM('informativo', 'urgente', 'manutencao') DEFAULT 'informativo',
        criado_por INT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS chat_mensagem (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_morador INT,
        id_funcionario INT,
        remetente ENUM('morador', 'funcionario') NOT NULL,
        conteudo TEXT NOT NULL,
        lida TINYINT(1) DEFAULT 0,
        enviada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($queries as $q) {
    mysqli_query($conexao, $q);
}
echo "Tabelas verificadas/criadas.";
?>
