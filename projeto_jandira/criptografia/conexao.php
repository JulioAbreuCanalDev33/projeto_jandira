<?php

$host = "localhost";
$db = "senhas";
$user = "root";
$pass = "1234";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
	// code...
	die("Falha na conexão com o banco de dados");
}