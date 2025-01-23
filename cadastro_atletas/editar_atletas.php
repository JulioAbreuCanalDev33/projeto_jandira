<?php
include('conexao.php');
$id = intval($_GET['id']);

$erro = false;
if (count($_POST) > 0) {
    function limpar_texto($str) {
        return preg_replace("/[0-9]/", "", $str);
    }
    $foto = $_POST['foto'];
    $nome = $_POST['nome'];
    $rg = $_POST['rg'];
    $cpf = $_POST['cpf'];
    $endereco = $_POST['endereco'];
    $numero = $_POST['numero'];
    $cep = $_POST['cep'];
    $municipio = $_POST['municipio'];
    $cidade = $_POST['cidade'];
    $data_nascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $telefone = $_POST['telefone'];

    if (empty($nome)) {
        $erro = "Preencha o nome";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Preencha o E-mail";
    }
    if (!empty($data_nascimento)) {
        $pedacos = explode('/', $data_nascimento);
        if (count($pedacos) == 3) {
            $data_nascimento = implode('-', array_reverse($pedacos));
        } else {
            $erro = "A data de nascimento deve seguir o padrão dia/mês/ano.";
        }
    }
    if (empty($telefone)) {
        $telefone = limpar_texto($telefone);
        if (strlen($telefone) != 15) {
            $erro = "O telefone deve ser preenchido no padrão (11) 94647-6117";
        }
    }

    function enviarArquivo($error, $size, $name, $tmp_name) {
        include("conexao.php");
    
        if($error)
            die("Falha ao enviar arquivo");
    
        if($size > 2097152)
            die("Arquivo muito grande!! Max: 2MB"); 
    
        $pasta = "arquivos/";
        $nomeDoArquivo = $name;
        $novoNomeDoArquivo = uniqid();
        $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));
    
        if($extensao != "jpg" && $extensao != 'png'){
            die("Tipo de arquivo não aceito");
    
            $path = $pasta . $novoNomeDoArquivo . "." . $extensao;
            $deu_certo = move_uploaded_file($tmp_name, $path);
        } else {
            die("Erro ao mover o arquivo para: " . $path);
        }
    }
    

    if ($erro) {
        echo "<p><b>ERRO: $erro</b></p>";
    } else {
        $sql_code = "UPDATE cadastro_atletas SET 
            foto = '$foto',
            nome = '$nome',
            rg = '$rg',
            cpf = '$cpf',
            endereco = '$endereco',
            numero = '$numero',
            cep = '$cep',
            municipio = '$municipio',
            cidade = '$cidade',
            data_nascimento = '$data_nascimento',
            email = '$email',
            senha = '$senha',
            telefone = '$telefone'
            WHERE id = '$id'";

        $deu_certo = $mysqli->query($sql_code) or die($mysqli->error);

        if ($deu_certo) {
            echo "<p><b>Atleta atualizado com sucesso!!!</b></p>";
            unset($_POST);
        }
    }
}

$sql_atletas = "SELECT * FROM cadastro_atletas WHERE id = '$id'";
$query_atletas = $mysqli->query($sql_atletas) or die($mysqli->error);
$atletas = $query_atletas->fetch_assoc();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Atleta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="atletas.php" class="btn btn-link mb-3">Voltar para a lista</a>
        <form method="POST" action="" class="row g-3 p-4 bg-success-subtle" style="border: 1px solid #ddd; border-radius: 8px;">
            <div class="col-md-6">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $atletas['nome']; ?>">
            </div>
            <div class="col-md-6">
                <label for="rg" class="form-label">RG</label>
                <input type="text" class="form-control" id="rg" name="rg" value="<?php echo $atletas['rg']; ?>">
            </div>
            <div class="col-md-6">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo $atletas['cpf']; ?>">
            </div>
            <div class="col-md-6">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo $atletas['endereco']; ?>">
            </div>
            <div class="col-md-3">
                <label for="numero" class="form-label">Número</label>
                <input type="text" class="form-control" id="numero" name="numero" value="<?php echo $atletas['numero']; ?>">
            </div>
            <div class="col-md-3">
                <label for="cep" class="form-label">CEP</label>
                <input type="text" class="form-control" id="cep" name="cep" value="<?php echo $atletas['cep']; ?>">
            </div>
            <div class="col-md-6">
                <label for="municipio" class="form-label">Município</label>
                <input type="text" class="form-control" id="municipio" name="municipio" value="<?php echo $atletas['municipio']; ?>">
            </div>
            <div class="col-md-6">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text" class="form-control" id="cidade" name="cidade" value="<?php echo $atletas['cidade']; ?>">
            </div>
            <div class="col-md-6">
                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                <input type="text" class="form-control" id="data_nascimento" name="data_nascimento" value="<?php echo $atletas['data_nascimento']; ?>">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $atletas['email']; ?>">
            </div>
            <div class="col-md-6">
                <label for="senha" class="form-label">Senha</label>
                <input type="senha" class="form-control" id="senha" name="senha" value="<?php echo $atletas['senha']; ?>">
            </div>
            <div class="col-md-6">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo $atletas['telefone']; ?>">
            </div>
            <div>
                 <p>
                    <label for="">Selecione o arquivo</label>
                    <input multiple name="foto" type="file" value="<?php echo $atletas['foto']; ?>">
                </p>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Salvar Atleta</button>
            </div>
        </form>
    </div>
</body>
</html>
