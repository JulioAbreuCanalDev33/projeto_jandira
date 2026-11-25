<?php
require_once __DIR__ . '/../includes/layout.php';
exigir_login();

require_once __DIR__ . '/conexao.php';

require_csrf();

$usuarioId = (int) $_SESSION['usuario'];
$usuarioAtual = $db->selectOne('senhas', 'id, email', ['id' => $usuarioId]);

if (!$usuarioAtual) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$erro = null;
$emailNovo = '';
$acao = (string) ($_GET['acao'] ?? '');
$idAlvo = (int) ($_GET['id'] ?? 0);
$usuarioEditar = null;
$usuarioExcluir = null;

if ($acao === 'editar' && $idAlvo > 0) {
    $usuarioEditar = $db->selectOne('senhas', 'id, email', ['id' => $idAlvo]);

    if (!$usuarioEditar) {
        header('Location: index.php');
        exit;
    }

    $emailNovo = (string) $usuarioEditar['email'];
} elseif ($acao === 'excluir' && $idAlvo > 0) {
    $usuarioExcluir = $db->selectOne('senhas', 'id, email', ['id' => $idAlvo]);

    if (!$usuarioExcluir) {
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailNovo = trim((string) ($_POST['email'] ?? ''));
    $senhaNova = (string) ($_POST['senha'] ?? '');
    $confirmacao = (string) ($_POST['confirmar_senha'] ?? '');

    if ($usuarioExcluir !== null) {
        if (isset($_POST['confirmar'])) {
            if ($idAlvo === $usuarioId) {
                $erro = 'Você não pode excluir o próprio acesso.';
            } elseif ($db->count('senhas') <= 1) {
                $erro = 'Não é possível excluir o único acesso ativo do sistema.';
            } else {
                $removidos = $db->delete('senhas', ['id' => $idAlvo]);

                if ($removidos > 0) {
                    header('Location: index.php?sucesso=excluido');
                    exit;
                }

                $erro = 'Não foi possível excluir o acesso. Tente novamente.';
            }
        }
    } elseif ($usuarioEditar !== null) {
        if (!filter_var($emailNovo, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } elseif ($senhaNova !== '' && (strlen($senhaNova) < 6 || strlen($senhaNova) > 16)) {
            $erro = 'A senha deve ter entre 6 e 16 caracteres.';
        } elseif ($senhaNova !== '' && $senhaNova !== $confirmacao) {
            $erro = 'A confirmação de senha não confere.';
        } else {
            $outroUsuario = $db->selectOne('senhas', 'id', ['email' => $emailNovo]);

            if ($outroUsuario && (int) $outroUsuario['id'] !== $idAlvo) {
                $erro = 'Já existe um usuário cadastrado com este e-mail.';
            }

            if ($erro === null) {
                $valores = ['email' => $emailNovo];

                if ($senhaNova !== '') {
                    $valores['senha'] = password_hash($senhaNova, PASSWORD_DEFAULT);
                }

                $atualizado = $db->update('senhas', $valores, ['id' => $idAlvo]);

                if ($atualizado !== []) {
                    if ($idAlvo === $usuarioId) {
                        $_SESSION['usuario_email'] = $emailNovo;
                    }

                    header('Location: index.php?sucesso=atualizado');
                    exit;
                }

                $erro = 'Não foi possível atualizar o acesso. Tente novamente.';
            }
        }
    } else {
        if (!filter_var($emailNovo, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } elseif (strlen($senhaNova) < 6 || strlen($senhaNova) > 16) {
            $erro = 'A senha deve ter entre 6 e 16 caracteres.';
        } elseif ($senhaNova !== $confirmacao) {
            $erro = 'A confirmação de senha não confere.';
        } else {
            if ($db->count('senhas', ['email' => $emailNovo]) > 0) {
                $erro = 'Já existe um usuário cadastrado com este e-mail.';
            }

            if ($erro === null) {
                $senhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);
                $novoUsuario = $db->insert('senhas', [
                    'email' => $emailNovo,
                    'senha' => $senhaHash,
                ]);

                if ($novoUsuario !== []) {
                    header('Location: index.php?sucesso=usuario');
                    exit;
                }

                $erro = 'Não foi possível cadastrar o novo acesso. Tente novamente.';
            }
        }
    }
}

$usuarios = $db->select('senhas', 'id, email', [], 'email ASC');

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
            <?php
            $mensagensSucesso = [
                'usuario' => 'Novo acesso cadastrado com sucesso.',
                'atualizado' => 'Acesso atualizado com sucesso.',
                'excluido' => 'Acesso excluído com sucesso.',
            ];
            $msgSucesso = $mensagensSucesso[$_GET['sucesso']] ?? null;
            ?>
            <?php if ($msgSucesso !== null): ?>
                <div class="mb-4">
                    <?php render_alert('success', $msgSucesso, 'check-circle'); ?>
                </div>
            <?php endif; ?>
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
                <?php if ($usuarioExcluir !== null): ?>
                    <div class="form-card">
                        <div class="form-card-header">
                            <h2><i class="bi bi-exclamation-triangle me-2 text-danger" aria-hidden="true"></i> Excluir acesso</h2>
                            <p>Confirme a remoção deste integrante da equipe gestora.</p>
                        </div>
                        <div class="form-card-body">
                            <?php if ($erro !== null): ?>
                                <div class="mb-4">
                                    <?php render_alert('danger', $erro, 'exclamation-triangle'); ?>
                                </div>
                            <?php endif; ?>

                            <div class="person-cell mb-4">
                                <span class="avatar avatar-initials" aria-hidden="true"><?= e(iniciais((string) $usuarioExcluir['email'])) ?></span>
                                <div>
                                    <span class="person-name"><?= e($usuarioExcluir['email']) ?></span>
                                    <span class="person-meta">Acesso administrativo</span>
                                </div>
                            </div>

                            <div class="alert alert-warning app-alert mb-4">
                                <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                                <span>Este integrante não poderá mais entrar no painel administrativo.</span>
                            </div>

                            <form method="post" action="" class="d-flex flex-wrap gap-2">
                                <?= csrf_field() ?>
                                <a href="<?= e(app_url('criptografia/index.php')) ?>" class="btn btn-soft">Cancelar</a>
                                <button type="submit" name="confirmar" value="1" class="btn btn-danger">
                                    <i class="bi bi-trash3" aria-hidden="true"></i>
                                    Sim, excluir acesso
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-card">
                        <div class="form-card-header">
                            <h2>
                                <i class="bi bi-<?= $usuarioEditar !== null ? 'person-gear' : 'person-plus' ?> me-2 text-primary" aria-hidden="true"></i>
                                <?= $usuarioEditar !== null ? 'Editar acesso' : 'Novo acesso' ?>
                            </h2>
                            <p><?= $usuarioEditar !== null ? 'Atualize os dados do integrante da equipe gestora.' : 'Cadastre uma nova pessoa na equipe gestora.' ?></p>
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
                                    <label for="senha" class="form-label">
                                        Senha
                                        <?php if ($usuarioEditar !== null): ?>
                                            <small class="text-muted">(opcional)</small>
                                        <?php else: ?>
                                            <span class="required">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="senha" name="senha" minlength="6" maxlength="16" placeholder="<?= $usuarioEditar !== null ? 'Deixe em branco para manter a senha atual' : 'Entre 6 e 16 caracteres' ?>" autocomplete="new-password" <?= $usuarioEditar !== null ? '' : 'required' ?>>
                                        <button class="btn btn-soft" type="button" data-toggle-password="#senha" aria-label="Mostrar senha">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="confirmar_senha" class="form-label">
                                        Confirmar senha
                                        <?php if ($usuarioEditar !== null): ?>
                                            <small class="text-muted">(opcional)</small>
                                        <?php else: ?>
                                            <span class="required">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" minlength="6" maxlength="16" placeholder="<?= $usuarioEditar !== null ? 'Repita a nova senha, se informada' : 'Repita a senha' ?>" autocomplete="new-password" <?= $usuarioEditar !== null ? '' : 'required' ?>>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-brand flex-grow-1">
                                        <i class="bi bi-<?= $usuarioEditar !== null ? 'check2-circle' : 'shield-plus' ?>" aria-hidden="true"></i>
                                        <?= $usuarioEditar !== null ? 'Atualizar acesso' : 'Cadastrar acesso' ?>
                                    </button>
                                    <?php if ($usuarioEditar !== null): ?>
                                        <a href="<?= e(app_url('criptografia/index.php')) ?>" class="btn btn-soft">Cancelar</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
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
                                    <th scope="col">Situação</th>
                                    <th scope="col" class="text-end">Ações</th>
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
                                        <td><span class="badge rounded-pill text-bg-success">Ativo</span></td>
                                        <td class="text-end">
                                            <div class="row-actions">
                                                <a href="<?= e(app_url('criptografia/index.php') . '?acao=editar&id=' . (int) $usuario['id']) ?>" class="action-btn" title="Editar acesso" aria-label="Editar acesso de <?= e($usuario['email']) ?>">
                                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                                </a>
                                                <?php if ((int) $usuario['id'] === $usuarioId): ?>
                                                    <span class="badge text-bg-light" title="Você está usando este acesso">Você</span>
                                                <?php else: ?>
                                                    <a href="<?= e(app_url('criptografia/index.php') . '?acao=excluir&id=' . (int) $usuario['id']) ?>" class="action-btn danger" title="Excluir acesso" aria-label="Excluir acesso de <?= e($usuario['email']) ?>">
                                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
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
