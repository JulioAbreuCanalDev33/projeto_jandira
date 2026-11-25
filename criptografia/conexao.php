<?php

$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'projeto_jandira';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '1234';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die('Falha na conexão com o banco de dados.');
}

$mysqli->set_charset('utf8mb4');
