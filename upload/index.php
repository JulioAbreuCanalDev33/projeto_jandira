<?php
include("conexao.php");

// Função para excluir arquivos
if (isset($_GET['deletar'])) {
    $id = intval($_GET['deletar']);
    $sql_query = $mysqli->query("SELECT * FROM arquivos WHERE id = '$id'") or die($mysqli->error);
    $arquivo = $sql_query->fetch_assoc();

    if ($arquivo && unlink($arquivo['path'])) {
        $deu_certo = $mysqli->query("DELETE FROM arquivos WHERE id = '$id'") or die($mysqli->error);
        if ($deu_certo) {
            echo "<p>Arquivo excluído com sucesso!</p>";
            // Redirecionamento para evitar reenvio do formulário
            header("Location: index.php");
            exit();
        }
    } else {
        echo "<p>Falha ao excluir o arquivo.</p>";
    }
}

// Função para enviar arquivos
function enviarArquivo($error, $size, $name, $tmp_name) {
    global $mysqli;

    if ($error !== UPLOAD_ERR_OK) {
        die("Falha ao enviar o arquivo. Erro: " . $error);
    }

    if ($size > 2097152) {
        die("Arquivo muito grande! Tamanho máximo permitido: 2MB.");
    }

    $pasta = "arquivos/";
    $nomeDoArquivo = basename($name);
    $novoNomeDoArquivo = uniqid();
    $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));

    // Verifica a extensão do arquivo
    if (!in_array($extensao, ['jpg', 'png', 'jpeg'])) {
        die("Formato de arquivo não permitido. Use apenas JPG ou PNG.");
    }

    $path = $pasta . $novoNomeDoArquivo . "." . $extensao;
    if (move_uploaded_file($tmp_name, $path)) {
        // Corrigido o formato da query SQL
        $stmt = $mysqli->prepare("INSERT INTO arquivos (nome, path) VALUES (?, ?)");
        $stmt->bind_param("ss", $nomeDoArquivo, $path);
        $stmt->execute();
        return true;
    }
    return false;
}

// Processamento de upload
if (isset($_FILES['arquivos'])) {
    $arquivos = $_FILES['arquivos'];
    $tudo_certo = true;

    foreach ($arquivos['name'] as $index => $arq) {
        $deu_certo = enviarArquivo(
            $arquivos['error'][$index], 
            $arquivos['size'][$index], 
            $arquivos['name'][$index], 
            $arquivos["tmp_name"][$index]
        );

        if (!$deu_certo) {
            $tudo_certo = false;
        }
    }
    
    
    if ($tudo_certo) {
        echo "<p>Todos os arquivos foram enviados com sucesso!</p>";
    } else {
        echo "<p>Falha ao enviar um ou mais arquivos!</p>";
    }
}

// Seleção dos arquivos existentes
$sql_query = $mysqli->query("SELECT * FROM arquivos ORDER BY data_upload DESC") or die($mysqli->error);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Arquivo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h1 class="text-center">Upload de Arquivos</h1>
    <form method="POST" enctype="multipart/form-data" class="my-4">
        <label for="arquivos" class="form-label">Selecione os arquivos (JPG/PNG):</label>
        <input multiple name="arquivos[]" type="file" class="form-control mb-3" required>
        <button name="upload" type="submit" class="btn btn-success">Enviar Arquivo</button>
    </form>

    <h2 class="mt-5">Lista de Arquivos</h2>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Preview</th>
                <th>Arquivo</th>
                <th>Data de Envio</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($arquivo = $sql_query->fetch_assoc()) : ?>
            <tr>
                <td><img height="50" src="<?php echo htmlspecialchars($arquivo['path']); ?>" alt="Imagem"></td>
                <td><a target="_blank" href="<?php echo htmlspecialchars($arquivo['path']); ?>"><?php echo htmlspecialchars($arquivo['nome']); ?></a></td>
                <td><?php echo date("d/m/Y H:i", strtotime($arquivo['data_upload'])); ?></td>
                <td>
                    <a class="btn btn-danger" href="index.php?deletar=<?php echo $arquivo['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Deletar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
