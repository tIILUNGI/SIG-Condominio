<?php
require_once 'conexao.php';

global $conexao;

if ($conexao) {
    echo "✅ Conexão com o banco de dados estabelecida com sucesso!<br>";
    echo "📊 Banco: " . DB_NAME . "<br><br>";
    
    // Testar consulta
    $result = mysqli_query($conexao, "SELECT COUNT(*) as total FROM morador");
    if ($row = mysqli_fetch_assoc($result)) {
        echo "👥 Total de moradores: " . $row['total'] . "<br>";
    }
    
    $result = mysqli_query($conexao, "SELECT COUNT(*) as total FROM administrador");
    if ($row = mysqli_fetch_assoc($result)) {
        echo "👨‍💼 Total de administradores: " . $row['total'] . "<br>";
    }
    
    echo "<br>✅ Sistema pronto para uso!";
} else {
    echo "❌ Falha na conexão";
}
?>