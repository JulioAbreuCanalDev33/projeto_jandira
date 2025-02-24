<?php
session_start(); // Inicia a sessão
include 'conexao.php';

$mensagem = ""; // Variável para armazenar a mensagem de sucesso

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    $stmt = $mysqli->prepare("INSERT INTO eventos (titulo, descricao, data_inicio, data_fim) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$titulo, $descricao, $data_inicio, $data_fim])) {
        $_SESSION['mensagem'] = "Evento adicionado com sucesso!";
    } else {
        $_SESSION['mensagem'] = "Erro ao adicionar o evento.";
    }

    // Redireciona para evitar reenvio do formulário
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Exibe a mensagem da sessão e depois a remove
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']); // Remove a mensagem para não aparecer ao atualizar a página
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-primary text-white">
    <div class="container mt-5">
        <div class="card p-4 bg-light text-dark">
            <h2 class="text-center">Adicionar Evento</h2>

            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-success text-center" role="alert">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título:</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição:</label>
                    <textarea id="descricao" name="descricao" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label for="data_inicio" class="form-label">Data de Início:</label>
                    <input type="datetime-local" id="data_inicio" name="data_inicio" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="data_fim" class="form-label">Data de Término:</label>
                    <input type="datetime-local" id="data_fim" name="data_fim" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Adicionar Evento</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
