<?php 

include('conexao.php');

$sql_atletas = "SELECT * FROM cadastro_atletas";
$query_atletas = $mysqli->query($sql_atletas) or die($mysqli->error);
$num_atletas = $query_atletas->num_rows;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Lista de Atletas</title>
</head>
<body>
    <h1>Lista de Atletas</h1>
    <p>Estes são os atletas cadastrados no seu sistema:</p>
    <table class="table table-success table-striped-columns">
        <thead>
            <th>ID</th>
            <th>Nome</th>
            <th>RG</th>
            <th>CPF</th>
            <th>Endereço</th>
            <th>Numero</th>
            <th>CEP</th>
            <th>Municipio</th>
            <th>Cidade</th>
            <th>Data</th>
            <th>E-mail</th>
            <th>Telefone</th>
            <th>Ações</th>
        </thead>
        <tbody>
            <?php  if($num_atletas == 0) { ?>
                    <tr>
                        <td colspan="13">Nenhum atleta foi cadastrado</td>
                    </tr>                
             <?php 
            }  else { 
                while($atletas = $query_atletas->fetch_assoc()){
                
                $telefone = "Não informado";    
                if(!empty($atletas['telefone'])) {
                    $telefone = formatar_telefone($atletas['telefone']);
                }  
                $nascimento = "Não informado";
                if(!empty($atletas['data_nascimento'])) {
                    $nascimento = formatar_data($atletas['data_nascimento']);
                }
                $cep = "Não informado";
                if(!empty($atletas['cep'])) {
                    $cep = formatar_cep($atletas['cep']);
                }
                $cpf = "Não informado";
                if(!empty($atletas['cpf'])) {
                    $cpf = formatar_cpf($atletas['cpf']);
                }
              
                //$data_cadastro = date("d/m/y H:i", strtotime($atleta['data_cadastro']));
            ?>
                  <tr>
                    <td><?php echo $atletas['id']; ?></td>
                    <td><?php echo $atletas['nome']; ?></td>
                    <td><?php echo $atletas['rg']; ?></td>
                    <td><?php echo $cpf; ?></td>
                    <td><?php echo $atletas['endereco']; ?></td>
                    <td><?php echo $atletas['numero']; ?></td>
                    <td><?php echo $cep; ?></td>
                    <td><?php echo $atletas['municipio']; ?></td>
                    <td><?php echo $atletas['cidade']; ?></td>
                    <td><?php echo $nascimento; ?></td>
                    <td><?php echo $atletas['email']; ?></td>
                    <td><?php echo $telefone; ?></td>
                    <td>
                        <a href="editar_atletas.php?id=<?php echo $atletas['id'];?>">Editar</a>
                        <a href="deletar_atletas.php?id=<?php echo $atletas['id'];?>">Deletar</a>
                    </td>
                  </tr> 
            <?php 
                }
            } 
            ?>            
        </tbody>
    </table><br>
    <a href="cadastrar_atletas.php">Voltar para o cadastro</a>
</body>
</html>