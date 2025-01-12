<?php

if(isset($_POST['email'])){

    include('conexao.php');

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql_code = "SELECT * FROM senhas WHERE email = '$email' LIMIT 1";
    $sql_exec = $mysqli->query($sql_code) or die($mysqli->error);
    $usuario = $sql_exec->fetch_assoc();
    
    if(password_verify($senha, $usuario['senha'])){
        if(!isset($_SESSION))
            session_start();
        $_SESSION['usuario'] = $usuario['id'];
        header("Location: index.php");
    } else {
        echo "Falha ao logar! email ou senha incorreto.";
    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="post">
        <p>
			<label>Email:</label>
			<input type="text" name="email">
        </p>
        <p>
			<label>Senha:</label>		
			<input type="text" name="senha">
        </p>	    
			<button type="submit">LOGAR</button>
	</form>
</body>
</html>