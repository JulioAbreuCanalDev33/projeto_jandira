<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexao.php");

if(isset($_GET['deletar'])) {
    $id = intval($_GET['deletar']);
    $sql_query = $mysqli->query("SELECT * FROM arquivos WHERE id = '$id'") or die($mysqli->error);
    $arquivo = $sql_query->fetch_assoc();

    if (!$arquivo) {
        die("Arquivo não encontrado no banco de dados.");
    }

    if (!empty($arquivo['path']) && file_exists($arquivo['path'])) {
        if (is_writable($arquivo['path'])) {
            if (unlink($arquivo['path'])) {
                $deu_certo = $mysqli->query("DELETE FROM arquivos WHERE id = '$id'") or die($mysqli->error);
                echo "<p>Arquivo excluído com sucesso!</p>";
            } else {
                die("Erro ao tentar deletar o arquivo: " . $arquivo['path']);
            }
        } else {
            die("Permissões insuficientes para excluir o arquivo: " . $arquivo['path']);
        }
    } else {
        die("O arquivo não existe no caminho especificado.");
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

    if($extensao != "jpg" && $extensao != 'png')
        die("Tipo de arquivo não aceito");

    $path = $pasta . $novoNomeDoArquivo . "." . $extensao;
    $deu_certo = move_uploaded_file($tmp_name, $path);
    if ($deu_certo) {
        $mysqli->query("INSERT INTO arquivos (nome, path) VALUES('$nomeDoArquivo', '$path')") or die($mysqli->error);
        return true;
    } else {
        die("Erro ao mover o arquivo para: " . $path);
    }
}

if(isset($_FILES['arquivos'])) {
    $arquivos = $_FILES['arquivos'];
    $tudo_certo = true;
    foreach($arquivos['name'] as $index => $arq) {
        $deu_certo = enviarArquivo($arquivos['error'][$index], $arquivos['size'][$index], $arquivos['name'][$index], $arquivos["tmp_name"][$index]);
        if(!$deu_certo) {
            $tudo_certo = false;
        }
    }  
    if($tudo_certo) {
        echo "<p>Todos os arquivos foram enviados com sucesso!</p>";
    } else {
        echo "<p>Falha ao enviar um ou mais arquivos!</p>";
    }

    header("Location: index.php");
    exit();
}

$sql_query = $mysqli->query("SELECT * FROM arquivos") or die($mysqli->error);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Arquivo</title>
</head>
<body>
    <form method="POST" enctype="multipart/form-data" action="">
        <p><label for="">Selecione o arquivo</label>
        <input multiple name="arquivos[]" type="file"></p>
        <button name="upload" type="submit">Enviar arquivo</button>
    </form>

    <h1>Lista de Arquivos</h1>
    <table border="1" cellpadding="10">
        <thead>
            <th>Preview</th>
            <th>Arquivo</th>
            <th>Data de Envio</th>
            <th></th>
        </thead>
        <tbody>
            <?php
            while($arquivo = $sql_query->fetch_assoc()) {
            ?>
            <tr>
                <td><img height="50" src="<?php echo htmlspecialchars($arquivo['path']); ?>" alt=""></td>
                <td><a target="_blank" href="<?php echo htmlspecialchars($arquivo['path']); ?>"><?php echo htmlspecialchars($arquivo['nome']); ?></a></td>
                <td><?php echo date("d/m/Y H:i", strtotime($arquivo['data_upload'])); ?></td>
                <th><a href="index.php?deletar=<?php echo $arquivo['id']; ?>">Deletar</a></th>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</body>
</html>
