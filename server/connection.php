<?php
$db_host = "localhost"; // Servidor do banco de dados (geralmente localhost no XAMPP)
$db_user = "root";      // Usuário do banco de dados (o padrão do XAMPP é root)
$db_pass = "";          // Senha do banco de dados (o padrão do XAMPP é vazia)
$db_name = "project_db"; // Nome do banco de dados que você criou

// Cria a conexão usando MySQLi (API do PHP para MySQL)
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Verifica se a conexão falhou
if (!$conn) {
    // Se a conexão falhar, exibe uma mensagem de erro e interrompe a execução
    die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
} else {
    // Opcional: Se quiser confirmar que a conexão foi bem-sucedida (pode remover depois)
    // echo "Conexão com o banco de dados estabelecida com sucesso!";
}
?>