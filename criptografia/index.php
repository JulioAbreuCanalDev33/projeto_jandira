<?php
require_once __DIR__ . '/../includes/layout.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/conexao.php';

require_csrf();

$usuarioId = (int) $_SESSION['usuario'];
$stmtUsuario = $mysqli->prepare('SELECT id, email FROM senhas WHERE id = ? LIMIT 1');
$stmtUsuario->bind_param('i', $usuarioId);
$stmtUsuario->execute();
$usuarioAtual = $stmtUsuario->get_result()->fetch_assoc();
$stmtUsuario->close();

if (!$usuarioAtual) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$erro = null;
$emailNovo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailNovo = trim((string) ($_POST['email'] ?? ''));
    $senhaNova = (string) ($_POST['senha'] ?? '');
    $confirmacao = (string) ($_POST['confirmar_senha'] ?? '');

    if (!filter_var($emailNovo, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (strlen($senhaNova) < 6 || strlen($senhaNova) > 16) {
        $erro = 'A senha deve ter entre 6 e 16 caracteres.';
    } elseif ($senhaNova !== $confirmacao) {
        $erro = 'A confirmação de senha não confere.';
    } else {
        $verifica = $mysqli->prepare('SELECT id FROM senhas WHERE email = ? LIMIT 1');
        $verifica->bind_param('s', $emailNovo);
        $verifica->execute();
        $verifica->store_result();

        if ($verifica->num_rows > 0) {
            $erro = 'Já existe um usuário cadastrado com este e-mail.';
        }

        $verifica->close();

        if ($erro === null) {
            $senhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);
            $insert = $mysqli->prepare('INSERT INTO senhas (email, senha) VALUES (?, ?)');
            $insert->bind_param('ss', $emailNovo, $senhaHash);

            if ($insert->execute()) {
                $insert->close();
                header('Location: index.php?sucesso=usuario');
                exit;
            }

            $erro = 'Não foi possível cadastrar o novo acesso. Tente novamente.';
            $insert->close();
        }
    }
}

$queryUsuarios = $mysqli->query('SELECT id, email FROM senhas ORDER BY email ASC');
$usuarios = $queryUsuarios ? $queryUsuarios->fetch_all(MYSQLI_ASSOC) : [];

$acoesPainel = '<a href="' . e(app_url('cadastro_atletas/cadastrar_atletas.php')) . '" class="btn btn-brand"><i class="bi bi-person-plus" aria-hidden="true"></i> Novo atleta</a>'
    . '<form method="post" action="' . e(app_url('criptografia/logout.php')) . '" class="d-inline">'
    . csrf_field()
    . '<button type="submit" class="btn btn-soft"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sair</button>'
    . '</form>';

render_layout_start('Painel', '', 'Painel administrativo do sistema de gestão esportiva.');
?>

<?php
render_page_header(
    'Painel administrativo',
    'Olá, equipe gestora',
    'Gerencie os acessos ao sistema e navegue rapidamente pelos módulos do projeto.',
    ['Área restrita' => null],
    $acoesPainel
);
?>

<section class="page-content">
    <div class="container">
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="mb-4">
                <?php render_alert('success', 'Novo acesso cadastrado com sucesso.', 'check-circle'); ?>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-12 col-md-4">
                <a class="access-card" href="<?= e(app_url('cadastro_atletas/atletas.php')) ?>">
                    <div class="access-card-top">
                        <span class="icon-tile"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                    </div>
                    <h3>Atletas</h3>
                    <p>Consulte e atualize os cadastros da equipe.</p>
                    <span class="access-link">Gerenciar <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a class="access-card" href="<?= e(app_url('cadastro_atletas/adicionar_eventos.php')) ?>">
                    <div class="access-card-top">
                        <span class="icon-tile accent"><i class="bi bi-calendar-event-fill" aria-hidden="true"></i></span>
                    </div>
                    <h3>Eventos</h3>
                    <p>Organize a agenda esportiva do município.</p>
                    <span class="access-link">Gerenciar <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a class="access-card" href="<?= e(app_url('upload/index.php')) ?>">
                    <div class="access-card-top">
                        <span class="icon-tile green"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
                    </div>
                    <h3>Arquivos</h3>
                    <p>Envie e organize fotos e documentos.</p>
                    <span class="access-link">Gerenciar <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="form-card">
                    <div class="form-card-header">
                        <h2><i class="bi bi-person-plus me-2 text-primary" aria-hidden="true"></i> Novo acesso</h2>
                        <p>Cadastre uma nova pessoa na equipe gestora.</p>
                    </div>
                    <div class="form-card-body">
                        <?php if ($erro !== null): ?>
                            <div class="mb-4">
                                <?php render_alert('danger', $erro, 'exclamation-triangle'); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail <span class="required">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= e($emailNovo) ?>" placeholder="novo.acesso@exemplo.com" autocomplete="off" required>
                            </div>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha <span class="required">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="senha" name="senha" minlength="6" maxlength="16" placeholder="Entre 6 e 16 caracteres" autocomplete="new-password" required>
                                    <button class="btn btn-soft" type="button" data-toggle-password="#senha" aria-label="Mostrar senha">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="confirmar_senha" class="form-label">Confirmar senha <span class="required">*</span></label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" minlength="6" maxlength="16" placeholder="Repita a senha" autocomplete="new-password" required>
                            </div>
                            <button type="submit" class="btn btn-brand w-100">
                                <i class="bi bi-shield-plus" aria-hidden="true"></i>
                                Cadastrar acesso
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="surface-card">
                    <div class="surface-card-header">
                        <div>
                            <h2><i class="bi bi-people me-2 text-primary" aria-hidden="true"></i> Usuários com acesso</h2>
                            <p>Pessoas autorizadas a entrar no painel administrativo.</p>
                        </div>
                        <span class="result-count"><?= count($usuarios) ?> <?= count($usuarios) === 1 ? 'usuário' : 'usuários' ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-modern align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">E-mail</th>
                                    <th scope="col">Perfil</th>
                                    <th scope="col" class="text-end">Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td>
                                            <div class="person-cell">
                                                <span class="avatar avatar-initials" aria-hidden="true"><?= e(iniciais((string) $usuario['email'])) ?></span>
                                                <div>
                                                    <span class="person-name"><?= e($usuario['email']) ?></span>
                                                    <span class="person-meta">Acesso administrativo</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge text-bg-light">Equipe gestora</span></td>
                                        <td class="text-end">
                                            <span class="badge rounded-pill text-bg-success">Ativo</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="surface-card-footer">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        As senhas são armazenadas com criptografia e não podem ser visualizadas.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
