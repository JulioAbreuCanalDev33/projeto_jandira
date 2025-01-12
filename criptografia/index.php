<?php

if(!isset($_SESSION))
    session_start();

if(!isset($_SESSION['usuario']))
	die('Voçê não esta logado. <a href="login.php">Clique aqui</a> para logar.');

if(isset($_POST['email'])){

	include('conexao.php');

	$email = $_POST['email'];
	$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

	$mysqli->query("INSERT INTO senhas (email, senha) VALUES('$email', '$senha')");

}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	Cadastro de Usuario <br><br>
		<form action="" method="post">
			<p>
				<label>Email:</label>
				<input type="text" name="email">
			</p>
			<p>
				<label>Senha:</label>		
				<input type="text" name="senha">
			</p>	
				<button type="submit">Cadastrar senha</button>
		</form>
		<p><a href="logout.php">Sair</a></p>
</body>
</html>