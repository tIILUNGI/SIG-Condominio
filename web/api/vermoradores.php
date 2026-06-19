<?php
include "conexao.php";

$sql = "SELECT id, nome, numbi, telefone, email, nasc, nacionalidade, morada FROM moradores;";

$resultado = $conexao->query($sql);

if ($resultado->num_rows > 0) {

    while($row = $resultado->fetch_assoc()) {

        echo "
        <tr>
            <td>".$row['id']."</td>
            <td>".$row['nome']."</td>
            <td>".$row['numbi']."</td>
            <td>".$row['telefone']."</td>
            <td>".$row['email']."</td>
            <td>".$row['nasc']."</td>
            <td>".$row['nacionalidade']."</td>
            <td>".$row['morada']."</td>
            <td>Activo</td>

            <td>
                <button>Editar</button>
                <button>Eliminar</button>
            </td>
        </tr>
        ";

    }

} else {

    echo "
    <tr>
        <td colspan='8'>Sem registros</td>
    </tr>
    ";

}
?>