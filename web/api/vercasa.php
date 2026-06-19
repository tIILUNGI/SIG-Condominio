<?php

include "conexao.php";

$sql = "SELECT * FROM casa";

$resultado = $conexao->query($sql);

if($resultado->num_rows > 0){

    while($row = $resultado->fetch_assoc()){

        echo "<tr>

                <td>".$row['bloco']."</td>

                <td>".$row['rua']."</td>

                <td>".$row['casanum']."</td>

                <td>".$row['tipologia']."</td>

                <td>".$row['estado']."</td>

                <td>

                    <button>Editar</button>

                </td>

              </tr>";

    }

}else{

    echo "<tr>

            <td colspan='6'>

                Sem residências cadastradas

            </td>

          </tr>";

}

?>