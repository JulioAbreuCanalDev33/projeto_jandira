<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/conexao.php';

require_csrf();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: atletas.php');
    exit;
}

$atleta = $db->selectOne('cadastro_atletas', '*', ['id' => $id]) ?? [];

if (!$atleta) {
    render_layout_start('Atleta não encontrado', 'atletas');
    render_page_header(
        'Gestão de atletas',
        'Atleta não encontrado',
        'O registro solicitado não existe ou já foi removido do sistema.',
        ['Atletas' => app_url('cadastro_atletas/atletas.php'), 'Atleta não encontrado' => null]
    );
    ?>
    <section class="page-content">
        <div class="container">
            <div class="surface-card">
                <div class="empty-state">
                    <span class="empty-state-icon"><i class="bi bi-person-x" aria-hidden="true"></i></span>
                    <h3>Não encontramos este atleta</h3>
                    <p>Volte para a lista e selecione um registro válido.</p>
                    <a href="<?= e(app_url('cadastro_atletas/atletas.php')) ?>" class="btn btn-brand">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar para atletas
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php
    render_layout_end();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $removidos = $db->delete('cadastro_atletas', ['id' => $id]);

    if ($removidos > 0) {
        remover_imagem($db, (string) ($atleta['foto'] ?? ''));

        header('Location: atletas.php?sucesso=excluido');
        exit;
    }

    $erro = 'Não foi possível excluir o atleta. Tente novamente.';
}

$fotoRelativa = (string) ($atleta['foto'] ?? '');
$temFoto = filter_var($fotoRelativa, FILTER_VALIDATE_URL) !== false;

render_layout_start('Excluir atleta', 'atletas', 'Confirme a exclusão do atleta.');
?>

<?php
render_page_header(
    'Gestão de atletas',
    'Excluir atleta',
    'Revise as informações abaixo antes de confirmar a exclusão.',
    [
        'Atletas' => app_url('cadastro_atletas/atletas.php'),
        'Excluir atleta' => null,
    ]
);
?>

<section class="page-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <?php if (isset($erro)): ?>
                    <div class="mb-4">
                        <?php render_alert('danger', $erro, 'exclamation-triangle'); ?>
                    </div>
                <?php endif; ?>

                <div class="surface-card">
                    <div class="surface-card-header">
                        <div>
                            <h2><i class="bi bi-exclamation-triangle me-2 text-danger" aria-hidden="true"></i> Confirmação de exclusão</h2>
                            <p>Esta ação é permanente e não poderá ser desfeita.</p>
                        </div>
                    </div>

                    <div class="surface-card-body">
                        <div class="person-cell mb-4">
                            <?php if ($temFoto): ?>
                                <img class="avatar" src="<?= e($fotoRelativa) ?>" alt="Foto de <?= e($atleta['nome']) ?>">
                            <?php else: ?>
                                <span class="avatar avatar-initials" aria-hidden="true"><?= e(iniciais((string) $atleta['nome'])) ?></span>
                            <?php endif; ?>
                            <div>
                                <span class="person-name"><?= e($atleta['nome']) ?></span>
                                <span class="person-meta"><?= e($atleta['email'] ?: 'E-mail não informado') ?></span>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6">
                                <span class="person-meta d-block">CPF</span>
                                <strong class="mono"><?= e(!empty($atleta['cpf']) ? formatar_cpf($atleta['cpf']) : '—') ?></strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="person-meta d-block">Telefone</span>
                                <strong class="mono"><?= e(!empty($atleta['telefone']) ? formatar_telefone($atleta['telefone']) : '—') ?></strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="person-meta d-block">Cidade</span>
                                <strong><?= e($atleta['cidade'] ?: '—') ?></strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="person-meta d-block">Data de nascimento</span>
                                <strong class="mono"><?= e(formatar_data_br($atleta['data_nascimento'])) ?></strong>
                            </div>
                        </div>

                        <div class="alert alert-warning app-alert mb-0" role="alert">
                            <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                            <span>Ao excluir este atleta, a foto e os dados do cadastro serão removidos permanentemente.</span>
                        </div>
                    </div>

                    <div class="surface-card-footer">
                        <form method="post" action="" class="d-flex flex-wrap gap-2 justify-content-end">
                            <?= csrf_field() ?>
                            <a href="<?= e(app_url('cadastro_atletas/atletas.php')) ?>" class="btn btn-soft">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                Cancelar
                            </a>
                            <button type="submit" name="confirmar" value="1" class="btn btn-danger">
                                <i class="bi bi-trash3" aria-hidden="true"></i>
                                Sim, excluir atleta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
