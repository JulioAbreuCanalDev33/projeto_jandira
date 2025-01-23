<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('conexao.php');

// Consulta os atletas no banco de dados
$sql_atletas = "SELECT * FROM cadastro_atletas";
$query_atletas = $mysqli->query($sql_atletas);

// Verifica se houve erro na consulta
if (!$query_atletas) {
    die("Erro na consulta ao banco de dados: " . $mysqli->error);
}

$num_atletas = $query_atletas->num_rows;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
        crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
        crossorigin="anonymous"></script>
    <title>Lista de Atletas</title>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Lista de Atletas</h1>
        <p>Estes são os atletas cadastrados no sistema:</p>
        <table class="table table-success table-striped-columns">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>RG</th>
                    <th>CPF</th>
                    <th>Endereço</th>
                    <th>Número</th>
                    <th>CEP</th>
                    <th>Município</th>
                    <th>Cidade</th>
                    <th>Data de Nascimento</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($num_atletas == 0): ?>
                    <tr>
                        <td colspan="14" class="text-center">Nenhum atleta foi cadastrado</td>
                    </tr>
                <?php else: 
                    while ($atletas = $query_atletas->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($atletas['id']); ?></td>
                            <td>
                                <?php if (!empty($atletas['foto']) && file_exists($atletas['foto'])): ?>
                                    <img height="40" src="<?= htmlspecialchars($atletas['foto']); ?>" alt="Foto do atleta">
                                <?php else: ?>
                                    <span>Sem foto</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($atletas['nome']); ?></td>
                            <td><?= htmlspecialchars($atletas['rg']); ?></td>
                            <td><?= htmlspecialchars($atletas['cpf']); ?></td>
                            <td><?= htmlspecialchars($atletas['endereco']); ?></td>
                            <td><?= htmlspecialchars($atletas['numero']); ?></td>
                            <td><?= htmlspecialchars($atletas['cep']); ?></td>
                            <td><?= htmlspecialchars($atletas['municipio']); ?></td>
                            <td><?= htmlspecialchars($atletas['cidade']); ?></td>
                            <td><?= htmlspecialchars($atletas['data_nascimento']); ?></td>
                            <td><?= htmlspecialchars($atletas['email']); ?></td>
                            <td><?= htmlspecialchars($atletas['telefone']); ?></td>
                            <td>
                                <a href="editar_atletas.php?id=<?= $atletas['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="deletar_atletas.php?id=<?= $atletas['id']; ?>" class="btn btn-danger btn-sm">Deletar</a>
                            </td>
                        </tr>
                    <?php endwhile; 
                endif; ?>
            </tbody>
        </table>
        <a href="cadastrar_atletas.php" class="btn btn-primary">Voltar para o cadastro</a>
    </div>
</body>
</html>
