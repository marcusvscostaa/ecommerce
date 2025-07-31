<?php
$db_host = "localhost";
$db_user = "root";      
$db_pass = "";          
$db_name = "project_db"; 

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
} else {
    // echo "Conexão com o banco de dados estabelecida com sucesso!";
}
?>