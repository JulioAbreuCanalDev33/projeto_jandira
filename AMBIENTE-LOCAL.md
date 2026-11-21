# Ambiente local — Projeto Jandira

Este documento descreve o ambiente de desenvolvimento configurado nesta máquina.

## Acesso

- **Site:** http://localhost:8000
- **Banco:** MySQL em `127.0.0.1:3306`
- **Banco de dados:** `projeto_jandira`
- **Usuário:** `root`
- **Senha:** `1234`

## Como iniciar

No PowerShell, dentro da pasta do projeto:

```powershell
powershell -ExecutionPolicy Bypass -File .\iniciar-local.ps1
```

O script:

1. Sobe o MySQL local (se ainda não estiver ativo).
2. Sobe o servidor PHP embutido na porta `8000`.
3. Abre o navegador em http://localhost:8000.

Também é possível dar dois cliques em `iniciar-local.ps1` (Executar com PowerShell).

## Como parar

```powershell
powershell -ExecutionPolicy Bypass -File .\parar-local.ps1
```

## Estrutura do ambiente

| Item | Caminho |
| --- | --- |
| PHP 8.4 (portátil) | `C:\Users\WTINFO PC\.local\jandira\php` |
| Dados do MySQL | `C:\Users\WTINFO PC\.local\jandira\mysql\data` |
| Configuração do MySQL | `C:\Users\WTINFO PC\.local\jandira\mysql\my.ini` |
| Log de erros do MySQL | `C:\Users\WTINFO PC\.local\jandira\mysql\error.log` |

O PHP e o MySQL ficam fora do repositório, então não entram no Git.

## Variáveis de ambiente

As credenciais de e-mail não ficam mais no código. Para configurar o envio:

```powershell
Copy-Item .env.example .env
```

Depois preencha o `.env`:

```dotenv
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=seu-usuario@exemplo.com
MAIL_PASSWORD=sua-senha-de-aplicativo
MAIL_FROM_ADDRESS=seu-usuario@exemplo.com
MAIL_FROM_NAME="Projeto Jandira"
MAIL_TO_ADDRESS=destino@exemplo.com
MAIL_TO_NAME="Equipe Projeto Jandira"
MAIL_DEBUG=0
```

O arquivo `.env` está no `.gitignore` e nunca deve ser versionado.

## Módulos

| Módulo | Endereço | Situação |
| --- | --- | --- |
| Página inicial | `/` | Institucional, com navegação para os módulos |
| Atletas | `/cadastro_atletas/atletas.php` | Listagem, busca e ações de edição/exclusão |
| Novo atleta | `/cadastro_atletas/cadastrar_atletas.php` | Cadastro com validação e upload de foto |
| Editar atleta | `/cadastro_atletas/editar_atletas.php?id=ID` | Atualização de dados e troca de foto |
| Excluir atleta | `/cadastro_atletas/deletar_atletas.php?id=ID` | Confirmação antes da exclusão |
| Eventos | `/cadastro_atletas/adicionar_eventos.php` | Cadastro e listagem da agenda |
| Usuários / login | `/criptografia/login.php` | Autenticação da equipe gestora |
| Painel administrativo | `/criptografia/index.php` | Cadastro de novos acessos |
| Uploads | `/upload/index.php` | Galeria, envio e exclusão de imagens |
| E-mail | `/email/enviar.php` | Teste de envio SMTP mediante formulário POST |

## Tabelas do banco

- `cadastro_atletas` — 2 registros
- `eventos` — 1 registro
- `senhas` — 5 registros
- `arquivos` — 3 registros

A antiga tabela `veiculos`, usada apenas como teste de busca, foi removida.

## Identidade visual e temas

- Tema claro com a paleta institucional de Jandira (azul, branco e amarelo).
- Tema escuro com a mesma identidade, alternável pelo botão no topo.
- A preferência de tema é salva no `localStorage` do navegador.
- Quando não há preferência salva, o sistema respeita o tema do sistema operacional.
- Layout compartilhado em `includes/layout.php`.
- Estilos em `assets/css/site.css`.
- Interações em `assets/js/site.js`.
- Componentes: cabeçalho, rodapé, breadcrumbs, cards, tabelas, formulários, galeria e autenticação.

## Segurança

- Formulários POST protegidos por token CSRF de sessão.
- Requisições sem token válido retornam a página “Sessão expirada” com HTTP 419.
- Login com `password_hash`/`password_verify` e regeneração do ID de sessão.
- Logout somente via POST com token CSRF.
- Consultas com prepared statements.
- Uploads validados por extensão e tamanho.
- Saída de dados escapada com `htmlspecialchars`.
- Credenciais SMTP carregadas de variáveis de ambiente.

## Observações

- Os arquivos `conexao.php` ainda usam `root` / `1234`, iguais às credenciais do MySQL local.
- O servidor PHP embutido é indicado para desenvolvimento, não para produção.
- O módulo de e-mail só dispara mensagens após o envio do formulário; abrir a página não envia nada.
