<?php
require_once __DIR__ . '/../includes/layout.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/conexao.php';

require_csrf();

$erro = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Informe o e-mail e a senha para entrar.';
    } else {
        $usuario = $db->selectOne('senhas', 'id, email, senha', ['email' => $email]);

        if ($usuario && password_verify($senha, (string) $usuario['senha'])) {
            session_regenerate_id(true);
            unset($_SESSION['csrf_token']);
            $_SESSION['usuario'] = (int) $usuario['id'];
            $_SESSION['usuario_email'] = $usuario['email'];

            header('Location: index.php');
            exit;
        }

        $erro = 'E-mail ou senha incorretos. Verifique os dados e tente novamente.';
    }
}

render_layout_start('Entrar', '', 'Acesse a área restrita do sistema de gestão esportiva de Jandira.');
?>

<section class="page-content">
    <div class="container">
        <nav aria-label="breadcrumb" class="page-breadcrumb mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= e(app_url('')) ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Início</a></li>
                <li class="breadcrumb-item active" aria-current="page">Área restrita</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-9">
                <div class="auth-card">
                    <aside class="auth-aside">
                        <div class="auth-aside-content">
                            <span class="auth-icon"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></span>
                            <h2>Área restrita</h2>
                            <p>
                                Espaço exclusivo da equipe gestora para administrar atletas, eventos e
                                documentos do projeto.
                            </p>
                        </div>
                        <div class="auth-aside-footer">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            Acesso protegido e monitorado.
                        </div>
                    </aside>

                    <div class="auth-form">
                        <h1>Bem-vindo de volta</h1>
                        <p class="auth-subtitle">Entre com suas credenciais para continuar.</p>

                        <?php if ($erro !== null): ?>
                            <div class="mb-4">
                                <?php render_alert('danger', $erro, 'exclamation-triangle'); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail <span class="required">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" placeholder="seu.email@exemplo.com" autocomplete="username" required autofocus>
                            </div>

                            <div class="mb-4">
                                <label for="senha" class="form-label">Senha <span class="required">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required>
                                    <button class="btn btn-soft" type="button" data-toggle-password="#senha" aria-label="Mostrar senha">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-brand w-100">
                                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                                Entrar no painel
                            </button>
                        </form>

                        <div class="auth-links">
                            <a href="<?= e(app_url('')) ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao início</a>
                            <a href="<?= e(app_url('cadastro_atletas/atletas.php')) ?>">Consultar atletas</a>
                        </div>

                        <div class="auth-note">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            O acesso é concedido pela equipe gestora do projeto.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
