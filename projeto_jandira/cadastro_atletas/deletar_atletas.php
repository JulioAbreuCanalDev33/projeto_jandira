<?php
if (isset($_POST['confirmar'])) {
    include('conexao.php');
    $id = intval($_GET['id']);
    $sql_code = "DELETE FROM cadastro_atletas WHERE id = '$id'";
    $sql_query = $mysqli->query($sql_code) or die($mysqli->error);

    if ($sql_query) { ?>
        <div class="alert alert-success text-center" role="alert">
            <h1>Atleta deletado com sucesso!</h1>
            <p><a href="atletas.php" class="btn btn-link">Clique aqui</a> para voltar para a lista dos atletas.</p>
        </div>
    <?php
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Deletar Atletas</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 p-4 bg-danger-subtle border border-danger rounded">
                <h2 class="text-center text-danger">Confirmação de Exclusão</h2>
                <p class="text-center">Tem certeza que deseja deletar este atleta?</p>
                <form action="" method="post" class="text-center">
                    <button name="confirmar" value="1" type="submit" class="btn btn-danger me-2">Sim</button>
                    <a href="atletas.php" class="btn btn-secondary">Não</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
