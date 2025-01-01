# Sistema de Gerenciamento de Atletas 🏋️‍♂️

![PHP Logo](https://www.php.net/images/logos/php-logo.svg)

Este projeto é um sistema de gerenciamento de atletas desenvolvido em PHP com Bootstrap. Ele permite criar, editar, listar e deletar registros de atletas em um banco de dados. O design é responsivo e amigável, utilizando o Bootstrap para garantir uma melhor experiência do usuário.

## Funcionalidades 🌟

- **Cadastrar Atletas**: Criação de registros no banco de dados.
- **Editar Atletas**: Atualização de registros existentes.
- **Deletar Atletas**: Exclusão segura de registros com confirmação do usuário.
- **Listar Atletas**: Exibição de todos os atletas cadastrados.

## Tecnologias Utilizadas 🛠️

- **PHP**: Backend e lógica do sistema.
- **MySQL**: Banco de dados relacional.
- **Bootstrap**: Design e estilização responsiva.
- **HTML5 e CSS3**: Estruturação e customização.

## Pré-requisitos 📋

Certifique-se de ter os seguintes softwares instalados na sua máquina:

- Servidor local (XAMPP, WAMP, ou similar);
- PHP >= 7.4;
- MySQL;
- Navegador web atualizado.

## Como Executar o Projeto ▶️

1. Clone este repositório:

   ```bash
   git clone https://github.com/JulioAbreuCanalDev33/sistema-gerenciamento-atletas.git
   ```

2. Configure o banco de dados:
   - Crie um banco de dados chamado `cadastro_atletas`.
   - Importe o arquivo `cadastro_atletas.sql` na pasta raiz para configurar a tabela necessária.

3. Configure a conexão com o banco de dados no arquivo `conexao.php`:

   ```php
   <?php
   $mysqli = new mysqli("localhost", "seu_usuario", "sua_senha", "cadastro_atletas");

   if ($mysqli->connect_error) {
       die("Erro de conexão: " . $mysqli->connect_error);
   }
   ?>
   ```

4. Inicie o servidor local e acesse o projeto via navegador:

   ```
   http://localhost/sistema-gerenciamento-atletas
   ```

## Estrutura do Projeto 📂

```
├── index.php            # Página inicial.
├── cadastrar.php        # Cadastro de novos atletas.
├── editar.php           # Edição de atletas existentes.
├── deletar.php          # Confirmação e exclusão de atletas.
├── conexao.php          # Conexão com o banco de dados.
├── cadastro_atletas.sql # Script para criação do banco de dados.
└── assets/              # Arquivos CSS, JS e imagens.
```

## Contribuindo 🤝

1. Faça um fork do projeto;
2. Crie uma branch com sua feature:

   ```bash
   git checkout -b minha-feature
   ```

3. Commit suas alterações:

   ```bash
   git commit -m 'Minha nova feature'
   ```

4. Faça um push para a branch:

   ```bash
   git push origin minha-feature
   ```

5. Abra um Pull Request.

## Autor ✍️

Desenvolvido por **[Julio Abreu](https://github.com/JulioAbreuCanalDev33)**.

## Licença 📄

Este projeto está sob a licença MIT. Sinta-se à vontade para usá-lo, modificá-lo e distribuí-lo.

---

Espero que este projeto seja útil! Se gostou, não esqueça de dar uma ⭐ no repositório.

