<?php

$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'projeto_jandira';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '1234';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
	// code...
	die("Falha na conexão com o banco de dados");
}

function formatar_data($data){
	return implode('/', array_reverse(explode('-', $data)));
}

function formatar_telefone($telefone) {
	// Verifica se o telefone já está no formato esperado
    if (preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', $telefone)) {
		return $telefone; // Retorna o número sem modificar
    }
	
    // Remove qualquer caractere não numérico
    $telefone = preg_replace('/\D/', '', $telefone);

    // Verifica se o número possui o tamanho correto
    if (strlen($telefone) === 11) {
        $ddd = substr($telefone, 0, 2);
        $parte1 = substr($telefone, 2, 5);
        $parte2 = substr($telefone, 7);
        return "($ddd) $parte1-$parte2";
    } elseif (strlen($telefone) === 10) {
		$ddd = substr($telefone, 0, 2);
        $parte1 = substr($telefone, 2, 4);
        $parte2 = substr($telefone, 6);
        return "($ddd) $parte1-$parte2";
    }

    // Retorna o número original se ele não tiver o tamanho correto
    return $telefone;
}

/*function formatar_telefone($telefone){
	$ddd = substr($telefone, 0, 2);
	$parte1 = substr($telefone, 2, 5);
	$parte2 = substr($telefone, 7);
	return "($ddd) $parte1-$parte2";
}*/

function formatar_cep($cep) {
	$pedaco1 = substr($cep, 0, 5);
	$pedaco2 = substr($cep, 5);
	return "$pedaco1-$pedaco2";
}

function formatar_cpf($cpf) {
	$part1 = substr($cpf, 0, 3);
	$part2 = substr($cpf, 3, 3);
	$part3 = substr($cpf, 6, 3);
	$part4 = substr($cpf, 9);
	return "$part1.$part2.$part3-$part4";
}

function buscar_modalidades_esporte(mysqli $mysqli): array {
	$resultado = $mysqli->query('SELECT id, nome FROM modalidades_esporte ORDER BY nome ASC');
	return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}

function modalidade_esporte_existe(mysqli $mysqli, int $id): bool {
	if ($id <= 0) {
		return false;
	}

	$stmt = $mysqli->prepare('SELECT 1 FROM modalidades_esporte WHERE id = ? LIMIT 1');
	if (!$stmt) {
		return false;
	}

	$stmt->bind_param('i', $id);
	$stmt->execute();
	$stmt->store_result();
	$existe = $stmt->num_rows > 0;
	$stmt->close();

	return $existe;
}
