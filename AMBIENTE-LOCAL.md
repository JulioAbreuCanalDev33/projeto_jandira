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

As conexões com o banco de atletas, arquivos e área restrita também leem `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS` do `.env` (com valores padrão para o ambiente local):

```dotenv
DB_HOST=127.0.0.1
DB_NAME=projeto_jandira
DB_USER=root
DB_PASS=1234
```

O arquivo `.env` está no `.gitignore` e nunca deve ser versionado.

## Módulos

| Módulo | Endereço | Situação |
| --- | --- | --- |
| Página inicial | `/` | Institucional, com navegação para os módulos |
| Atletas | `/cadastro_atletas/atletas.php` | Listagem, busca, filtro por modalidade e ações de edição/exclusão |
| Modalidades de esporte | `/cadastro_atletas/modalidades_esporte.php` | Listagem e gestão das modalidades esportivas |
| Nova modalidade | `/cadastro_atletas/cadastrar_modalidade_esporte.php` | Cadastro de modalidade de esporte |
| Editar/excluir modalidade | `/cadastro_atletas/editar_modalidade_esporte.php?id=ID` e `deletar_modalidade_esporte.php?id=ID` | Atualização e exclusão segura (bloqueia modalidades em uso) |
| Novo atleta | `/cadastro_atletas/cadastrar_atletas.php` | Cadastro com validação, modalidade e upload de foto |
| Editar atleta | `/cadastro_atletas/editar_atletas.php?id=ID` | Atualização de dados e troca de foto |
| Excluir atleta | `/cadastro_atletas/deletar_atletas.php?id=ID` | Confirmação antes da exclusão |
| Eventos | `/cadastro_atletas/adicionar_eventos.php` | Cadastro e listagem da agenda |
| Usuários / login | `/criptografia/login.php` | Autenticação da equipe gestora |
| Painel administrativo | `/criptografia/index.php` | Cadastro de novos acessos |
| Uploads | `/upload/index.php` | Galeria com busca, envio validado e exclusão segura de imagens |
| E-mail | `/email/enviar.php` | Teste de envio SMTP mediante formulário POST |

## Tabelas do banco

- `cadastro_atletas` — 2 registros
- `modalidades_esporte` — 5 registros
- `eventos` — 1 registro
- `senhas` — 5 registros
- `arquivos` — 3 registros

A antiga tabela `veiculos`, usada apenas como teste de busca, foi removida.

Para bancos já existentes, execute `cadastro_atletas/migracao_modalidades_esporte.sql`: ela renomeia `tipos_esporte` para `modalidades_esporte` e `tipo_esporte_id` para `modalidade_esporte_id`, preservando os dados e podendo ser rodada mais de uma vez. Em instalações do zero, importe `modalidades_esporte.sql` antes de `cadastro_atletas.sql`, pois a tabela de atletas referencia as modalidades.

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
