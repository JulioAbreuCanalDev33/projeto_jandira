<?php
$host = "localhost";
$db = "projeto_jandira";
$user = "root";
$pass = "1234";

// Criação da conexão usando MySQLi com tratamento de erro
$mysqli = new mysqli($host, $user, $pass, $db);

// Verifica se ocorreu um erro na conexão
if ($mysqli->connect_error) {
    die("Falha na conexão com o banco de dados: " . $mysqli->connect_error);
}


// Define o charset para UTF-8
$mysqli->set_charset("utf8mb4");
?>
