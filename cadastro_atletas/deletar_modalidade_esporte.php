<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/conexao.php';

require_csrf();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: modalidades_esporte.php');
    exit;
}

$stmt = $mysqli->prepare(
    'SELECT t.*,
        (SELECT COUNT(*) FROM cadastro_atletas a WHERE a.modalidade_esporte_id = t.id) AS total_atletas
     FROM modalidades_esporte t WHERE t.id = ? LIMIT 1'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$resultado = $stmt->get_result();
$modalidade = $resultado->fetch_assoc();
$stmt->close();

if (!$modalidade) {
    render_layout_start('Modalidade não encontrada', 'esportes');
    render_page_header(
        'Gestão esportiva',
        'Modalidade não encontrada',
        'O registro solicitado não existe ou foi removido do sistema.',
        ['Modalidades de esporte' => app_url('cadastro_atletas/modalidades_esporte.php'), 'Não encontrada' => null]
    );
    ?>
    <section class="page-content">
        <div class="container">
            <div class="surface-card">
                <div class="empty-state">
                    <span class="empty-state-icon"><i class="bi bi-trophy" aria-hidden="true"></i></span>
                    <h3>Não encontramos esta modalidade</h3>
                    <p>Volte para a lista e selecione um registro válido.</p>
                    <a href="<?= e(app_url('cadastro_atletas/modalidades_esporte.php')) ?>" class="btn btn-brand">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar para modalidades de esporte
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php
    render_layout_end();
    exit;
}

$totalAtletas = (int) $modalidade['total_atletas'];
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    if ($totalAtletas > 0) {
        $erro = 'Existem atletas vinculados a esta modalidade. Altere a modalidade de esporte desses atletas antes de excluir.';
    } else {
        $delete = $mysqli->prepare('DELETE FROM modalidades_esporte WHERE id = ?');
        $delete->bind_param('i', $id);

        if ($delete->execute()) {
            $delete->close();
            header('Location: modalidades_esporte.php?sucesso=excluido');
            exit;
        }

        $delete->close();
        $erro = 'Não foi possível excluir a modalidade. Tente novamente.';
    }
}

render_layout_start('Excluir modalidade', 'esportes', 'Confirme a exclusão da modalidade esportiva.');
?>

<?php
render_page_header(
    'Gestão esportiva',
    'Excluir modalidade',
    'Revise as informações abaixo antes de confirmar a exclusão.',
    [
        'Modalidades de esporte' => app_url('cadastro_atletas/modalidades_esporte.php'),
        'Excluir modalidade' => null,
    ]
);
?>

<section class="page-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <?php if ($erro !== null): ?>
                    <div class="mb-4">
                        <?php render_alert('danger', $erro, 'exclamation-triangle'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($totalAtletas > 0): ?>
                    <div class="mb-4">
                        <?php render_alert('warning', 'Esta modalidade está em uso por ' . $totalAtletas . ' ' . ($totalAtletas === 1 ? 'atleta' : 'atletas') . ' e não pode ser excluída.', 'exclamation-triangle'); ?>
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
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <span class="empty-state-icon mb-0"><i class="bi bi-trophy" aria-hidden="true"></i></span>
                            <div>
                                <span class="person-name d-block"><?= e($modalidade['nome']) ?></span>
                                <span class="person-meta"><?= e($modalidade['descricao'] ?: 'Sem descrição') ?></span>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6">
                                <span class="person-meta d-block">Atletas vinculados</span>
                                <strong class="mono"><?= $totalAtletas ?></strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="person-meta d-block">Cadastrada em</span>
                                <strong class="mono"><?= e(formatar_data_hora_br($modalidade['data_cadastro'])) ?></strong>
                            </div>
                        </div>

                        <div class="alert alert-warning app-alert mb-0" role="alert">
                            <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                            <span>Ao excluir, a modalidade desaparecerá das opções do cadastro de atletas.</span>
                        </div>
                    </div>

                    <div class="surface-card-footer">
                        <form method="post" action="" class="d-flex flex-wrap gap-2 justify-content-end">
                            <?= csrf_field() ?>
                            <a href="<?= e(app_url('cadastro_atletas/modalidades_esporte.php')) ?>" class="btn btn-soft">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                Cancelar
                            </a>
                            <?php if ($totalAtletas === 0): ?>
                                <button type="submit" name="confirmar" value="1" class="btn btn-danger">
                                    <i class="bi bi-trash3" aria-hidden="true"></i>
                                    Sim, excluir modalidade
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
