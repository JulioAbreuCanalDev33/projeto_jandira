<?php
include('conexao.php');

$erro = false;

if (count($_POST) > 0) {
    function limpar_texto($str) {
        return preg_replace("/[0-9]/", "", $str);
    }

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

    if ($erro) {
        echo "<p><b>ERRO: $erro</b></p>";
    } else {
        $sql_code = "INSERT INTO cadastro_atletas (nome, rg, cpf, endereco, numero, cep, municipio, cidade, data_nascimento, email, telefone) 
                     VALUES ('$nome', '$rg', '$cpf', '$endereco', '$numero', '$cep', '$municipio', '$cidade', '$data_nascimento', '$email', '$telefone')";

        $deu_certo = $mysqli->query($sql_code) or die($mysqli->error);

        if ($deu_certo) {
            echo "<p><b>Atleta cadastrado com sucesso!!!</b></p>";
            unset($_POST);
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastrar Atleta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="atletas.php" class="btn btn-link mb-3">Voltar para a lista</a>
        <form method="POST" action="" class="row g-3 p-4 bg-success-subtle" style="border: 1px solid #ddd; border-radius: 8px;">
            <div class="col-md-6">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($_POST['nome']) ? $_POST['nome'] : ''; ?>">
            </div>
            <div class="col-md-6">
                <label for="rg" class="form-label">RG</label>
                <input type="text" class="form-control" id="rg" name="rg" value="<?php echo isset($_POST['rg']) ? $_POST['rg'] : ''; ?>">
            </div>
            <div class="col-md-6">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo isset($_POST['cpf']) ? $_POST['cpf'] : ''; ?>">
            </div>
            <div class="col-md-6">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo isset($_POST['endereco']) ? $_POST['endereco'] : ''; ?>">
            </div>
            <div class="col-md-3">
                <label for="numero" class="form-label">Número</label>
                <input type="text" class="form-control" id="numero" name="numero" value="<?php echo isset($_POST['numero']) ? $_POST['numero'] : ''; ?>">
            </div>
            <div class="col-md-3">
                <label for="cep" class="form-label">CEP</label>
                <input type="text" class="form-control" id="cep" name="cep" value="<?php echo isset($_POST['cep']) ? $_POST['cep'] : ''; ?>">
            </div>
            <div class="col-md-6">
                <label for="municipio" class="form-label">Município</label>
                <input type="text" class="form-control" id="municipio" name="municipio" value="<?php echo isset($_POST['municipio']) ? $_POST['municipio'] : ''; ?>">
            </div>
            <div class="col-md-6">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text" class="form-control" id="cidade" name="cidade" value="<?php echo isset($_POST['cidade']) ? $_POST['cidade'] : ''; ?>">
            </div>
            <div class="col-md-6">
                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                <input type="text" class="form-control" id="data_nascimento" name="data_nascimento" value="<?php echo isset($_POST['data_nascimento']) ? $_POST['data_nascimento'] : ''; ?>">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
            </div>
            <div class="col-md-6">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo isset($_POST['telefone']) ? $_POST['telefone'] : ''; ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Cadastrar Atleta</button>
            </div>
        </form>
    </div>
</body>
</html>
