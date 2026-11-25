# Ambiente local — Projeto Jandira

Este documento descreve como executar o projeto localmente e como ele se conecta ao Supabase.

## Acesso

- **Site local:** http://localhost:8000
- **Site publicado:** https://jandira-esportes.vercel.app
- **Banco e arquivos:** Supabase (projeto `fitaluno`, plano free)

O MySQL local não é mais usado pela aplicação. Os scripts `iniciar-local.ps1` e `parar-local.ps1` ainda podem iniciar/encerrar o MySQL por hábito do ambiente antigo, mas o Jandira lê e grava direto no Supabase.

## Como iniciar

No Linux ou macOS, dentro da pasta do projeto:

```bash
php -S 127.0.0.1:8000 -t .
```

No Windows, o script existente continua válido para subir o PHP na porta 8000:

```powershell
powershell -ExecutionPolicy Bypass -File .\iniciar-local.ps1
```

Para encerrar o servidor:

```powershell
powershell -ExecutionPolicy Bypass -File .\parar-local.ps1
```

## Configuração

Copie o modelo e preencha com os dados do seu projeto Supabase:

```powershell
Copy-Item .env.example .env
```

```dotenv
APP_ENV=local
SUPABASE_URL=https://SEU-PROJETO.supabase.co
SUPABASE_SERVICE_KEY=sua-chave-secreta-do-supabase
```

A chave secreta vai **somente** no `.env` local e nas variáveis de ambiente da Vercel. Ela nunca é enviada ao navegador e não pode ser versionada. O `.env` está no `.gitignore`.

Para configurar o envio de e-mail, complete também:

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

## Banco no Supabase

1. Abra o SQL Editor do projeto Supabase.
2. Execute o arquivo `supabase/schema_jandira.sql` completo. Ele cria as tabelas, os índices, os dados iniciais, ativa RLS e cria o bucket público `arquivos`.
3. O schema pode ser executado mais de uma vez sem duplicar registros.

As variáveis usadas em produção na Vercel são:

| Variável | Tipo | Uso |
| --- | --- | --- |
| `SUPABASE_URL` | Secret | Endereço do projeto Supabase |
| `SUPABASE_SERVICE_KEY` | Secret | Chave secreta do servidor (acesso às tabelas e ao Storage) |

## Módulos

| Módulo | Endereço | Situação |
| --- | --- | --- |
| Página inicial | `/` | Institucional, com navegação para os módulos |
| Atletas | `/cadastro_atletas/atletas.php` | Listagem, busca, filtro por modalidade e ações de edição/exclusão |
| Modalidades de esporte | `/cadastro_atletas/modalidades_esporte.php` | Listagem, busca e gestão das modalidades esportivas |
| Nova modalidade | `/cadastro_atletas/modalidades_esporte.php?acao=novo` | Cadastro de modalidade de esporte |
| Editar/excluir modalidade | `/cadastro_atletas/modalidades_esporte.php?acao=editar&id=ID` e `?acao=excluir&id=ID` | Atualização e exclusão segura (bloqueia modalidades em uso) |
| Novo atleta | `/cadastro_atletas/cadastrar_atletas.php` | Cadastro com validação, modalidade e upload de foto |
| Editar atleta | `/cadastro_atletas/editar_atletas.php?id=ID` | Atualização de dados e troca de foto |
| Excluir atleta | `/cadastro_atletas/deletar_atletas.php?id=ID` | Confirmação antes da exclusão |
| Eventos | `/cadastro_atletas/adicionar_eventos.php` | Cadastro e listagem da agenda |
| Usuários / login | `/criptografia/login.php` | Autenticação da equipe gestora |
| Painel administrativo | `/criptografia/index.php` | Cadastro, edição e exclusão de integrantes da equipe gestora |
| Editar integrante | `/criptografia/index.php?acao=editar&id=ID` | Alteração de e-mail e senha opcional |
| Excluir integrante | `/criptografia/index.php?acao=excluir&id=ID` | Confirmação, bloqueio da autoexclusão e do último acesso |
| Uploads | `/upload/index.php` | Galeria com busca, envio validado e exclusão segura de imagens |
| E-mail | `/email/enviar.php` | Teste de envio SMTP mediante formulário POST |

Todos os módulos acima, exceto a página inicial e o login, exigem sessão autenticada. Um visitante que tocar em **Atletas**, **Modalidades de esporte**, **Eventos**, **Arquivos** ou **Cadastrar atleta** é redirecionado para `/criptografia/login.php`, com retorno seguro ao módulo solicitado após o login.

## Tabelas do banco

Após executar o schema e os seeds:

- `modalidades_esporte` — 5 registros
- `cadastro_atletas` — 2 registros
- `eventos` — 1 registro
- `senhas` — 3 registros
- `arquivos` — 7 registros
- `sessoes` — criadas automaticamente quando há usuários navegando

As imagens das galerias e as fotos de atletas ficam no bucket público `arquivos` do Supabase Storage. Os arquivos `.sql` dentro de `cadastro_atletas/`, `criptografia/` e `upload/` são referências históricas do antigo banco MySQL; a fonte atual é `supabase/schema_jandira.sql`.

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
- `exigir_login()` bloqueia o acesso direto aos módulos internos e redireciona visitantes ao login.
- O destino após o login é validado para evitar redirecionamento para sites externos.
- Login com `password_hash`/`password_verify` e regeneração do ID de sessão.
- Logout somente via POST com token CSRF.
- Consultas feitas via API REST do Supabase, sem concatenação de SQL.
- RLS ativado nas tabelas: apenas a chave secreta do servidor acessa os dados.
- Sessões gravadas no Supabase, necessárias porque a Vercel é stateless.
- Uploads validados por extensão, MIME real, tamanho e nome aleatório; remoção do objeto no Storage.
- Saída de dados escapada com `htmlspecialchars`.
- Credenciais e chaves carregadas de variáveis de ambiente.

## Observações

- O servidor PHP embutido é indicado para desenvolvimento, não para produção.
- Em produção, a Vercel executa cada página como uma função isolada; por isso as sessões não ficam em arquivos.
- O módulo de e-mail só dispara mensagens após o envio do formulário; abrir a página não envia nada.
