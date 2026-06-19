<?php
include("conexao.php");

echo "<h1>🔍 Teste de Login - Debug</h1>";

// Dados de teste
$bi = '123456789';
$senha_digitada = 'Admin@2026';

echo "<h2>Testando com BI: <strong>$bi</strong> e Senha: <strong>$senha_digitada</strong></h2>";

// Buscar morador
$stmt = $conexao->prepare("SELECT id, nome, email, numbI, senha_hash, estado_conta FROM morador WHERE numbI = ?");
$stmt->bind_param("s", $bi);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $morador = $res->fetch_assoc();
    
    echo "<h3>📋 Dados do Morador:</h3>";
    echo "<pre>";
    print_r($morador);
    echo "</pre>";
    
    echo "<h3>🔐 Teste de Senha:</h3>";
    echo "Senha digitada: '" . $senha_digitada . "'<br>";
    echo "Hash no banco: '" . $morador['senha_hash'] . "'<br><br>";
    
    // Testar password_verify
    $resultado = password_verify($senha_digitada, $morador['senha_hash']);
    echo "✅ password_verify(): " . ($resultado ? '<span style="color:green">VERDADEIRO</span>' : '<span style="color:red">FALSO</span>') . "<br>";
    
    // Testar comparação direta
    echo "❌ Comparação direta (texto plano): " . ($senha_digitada === $morador['senha_hash'] ? '<span style="color:green">IGUAIS</span>' : '<span style="color:red">DIFERENTES</span>') . "<br>";
    
    // Mostrar todos os moradores
    echo "<h3>📊 Todos os moradores cadastrados:</h3>";
    $todos = $conexao->query("SELECT id, nome, email, numbI, estado_conta FROM morador");
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
    echo "<tr style='background:#333;color:#fff;'><th>ID</th><th>Nome</th><th>Email</th><th>BI</th><th>Status</th></tr>";
    while ($row = $todos->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nome'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['numbi'] . "</td>";
        echo "<td>" . $row['estado_conta'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "❌ Nenhum morador encontrado com o BI: $bi";
}
?>