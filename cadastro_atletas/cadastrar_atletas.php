<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('conexao.php');

$erro = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function limpar_texto($str) {
        return preg_replace("/[^0-9]/", "", $str);
    }

    $nome = trim($_POST['nome'] ?? '');
    $rg = trim($_POST['rg'] ?? '');
    $cpf = limpar_texto($_POST['cpf'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $numero = limpar_texto($_POST['numero'] ?? '');
    $cep = limpar_texto($_POST['cep'] ?? '');
    $municipio = trim($_POST['municipio'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $data_nascimento = trim($_POST['data_nascimento'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = limpar_texto($_POST['telefone'] ?? '');
    $senha_descriptografada = trim($_POST['senha'] ?? '');
    $foto = $_FILES['foto'] ?? null;

    // Validações
    if (empty($nome)) {
        $erro = "O campo 'Nome' é obrigatório.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Preencha um e-mail válido.";
    } elseif (!empty($data_nascimento)) {
        $pedacos = explode('/', $data_nascimento);
        if (count($pedacos) === 3) {
            $data_nascimento = implode('-', array_reverse($pedacos));
        } else {
            $erro = "A data de nascimento deve estar no formato dia/mês/ano.";
        }
    }

    if (!$erro && !empty($telefone) && strlen($telefone) !== 11) {
        $erro = "O telefone deve ser preenchido no formato (11) 94647-6117.";
    }

    if (!$erro && (strlen($senha_descriptografada) < 6 || strlen($senha_descriptografada) > 16)) {
        $erro = "A senha deve ter no mínimo 6 caracteres e no máximo 16.";
    }

    // Verifica se a foto foi enviada
    if (!$erro && $foto && $foto['error'] == UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extensao, $extensoes_permitidas)) {
            $erro = "A foto deve ser uma imagem no formato JPG, JPEG, PNG ou GIF.";
        } else {
            $caminho_foto = 'arquivos/' . uniqid() . '.' . $extensao;
            if (!move_uploaded_file($foto['tmp_name'], $caminho_foto)) {
                $erro = "Erro ao salvar a foto. Tente novamente.";
            }
        }
    } elseif (!$erro && !$foto) {
        $erro = "O campo de foto é obrigatório.";
    }

    // Inserção no banco de dados
    if (!$erro) {
        try {
            $stmt = $mysqli->prepare("INSERT INTO cadastro_atletas 
                (nome, rg, cpf, endereco, numero, cep, municipio, cidade, data_nascimento, email, senha, telefone, foto) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $senha = password_hash($senha_descriptografada, PASSWORD_DEFAULT);

            $stmt->bind_param(
                "sssssssssssss",
                $nome, $rg, $cpf, $endereco, $numero, $cep, $municipio, $cidade, $data_nascimento, $email, $senha, $telefone, $caminho_foto
            );

            if ($stmt->execute()) {
                echo "<p class='text-success'><b>Atleta cadastrado com sucesso!</b></p>";
                $_POST = [];
            } else {
                throw new Exception("Erro ao cadastrar atleta: " . $stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo "<p class='text-danger'><b>ERRO: " . $e->getMessage() . "</b></p>";
        }
    } else {
        echo "<p class='text-danger'><b>ERRO: $erro</b></p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastrar Atleta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="atletas.php" class="btn btn-link mb-3">Voltar para a lista</a>
        <form method="POST" action="" class="row g-3 p-4 bg-success-subtle" enctype="multipart/form-data" style="border: 1px solid #ddd; border-radius: 8px;">
            <div class="col-md-6">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="rg" class="form-label">RG</label>
                <input type="text" class="form-control" id="rg" name="rg" value="<?= htmlspecialchars($_POST['rg'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($_POST['cpf'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text" class="form-control" id="endereco" name="endereco" value="<?= htmlspecialchars($_POST['endereco'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label for="numero" class="form-label">Número</label>
                <input type="text" class="form-control" id="numero" name="numero" value="<?= htmlspecialchars($_POST['numero'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label for="cep" class="form-label">CEP</label>
                <input type="text" class="form-control" id="cep" name="cep" value="<?= htmlspecialchars($_POST['cep'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="municipio" class="form-label">Município</label>
                <input type="text" class="form-control" id="municipio" name="municipio" value="<?= htmlspecialchars($_POST['municipio'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text" class="form-control" id="cidade" name="cidade" value="<?= htmlspecialchars($_POST['cidade'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                <input type="text" class="form-control" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label for="senha" class="form-label">Senha</label>
                <input type="text" class="form-control" id="senha" name="senha">
            </div>
            <div class="col-md-6">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? ''); ?>">
            </div>
            <div class="col-md-12">
                <label for="foto" class="form-label">Foto</label>
                <input type="file" class="form-control" id="foto" name="foto" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Cadastrar Atleta</button>
            </div>
        </form>
    </div>
</body>
</html>
