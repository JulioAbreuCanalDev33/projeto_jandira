<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/conexao.php';

$sqlModalidades = 'SELECT t.*,
    (SELECT COUNT(*) FROM cadastro_atletas a WHERE a.modalidade_esporte_id = t.id) AS total_atletas
    FROM modalidades_esporte t ORDER BY t.nome ASC';
$queryModalidades = $mysqli->query($sqlModalidades);

if (!$queryModalidades) {
    die('Erro na consulta ao banco de dados: ' . e($mysqli->error));
}

$modalidades = $queryModalidades->fetch_all(MYSQLI_ASSOC);
$totalModalidadesCadastradas = count($modalidades);
$totalAtletasComModalidade = array_sum(array_column($modalidades, 'total_atletas'));

$mensagens = [
    'cadastrado' => ['success', 'Modalidade de esporte cadastrada com sucesso.', 'check-circle'],
    'atualizado' => ['success', 'Modalidade de esporte atualizada com sucesso.', 'check-circle'],
    'excluido' => ['success', 'Modalidade de esporte excluída com sucesso.', 'check-circle'],
    'em-uso' => ['warning', 'Não foi possível excluir: existem atletas vinculados a esta modalidade.', 'exclamation-triangle'],
];

$chaveMensagem = $_GET['sucesso'] ?? $_GET['aviso'] ?? '';
$mensagem = $mensagens[$chaveMensagem] ?? null;

render_layout_start('Modalidades de esporte', 'esportes', 'Gerencie as modalidades esportivas disponíveis no cadastro de atletas de Jandira.');
?>

<?php
render_page_header(
    'Gestão esportiva',
    'Modalidades de esporte',
    'Cadastre e organize as modalidades praticadas pelos atletas de Jandira.',
    ['Modalidades de esporte' => null],
    '<a href="' . e(app_url('cadastro_atletas/cadastrar_modalidade_esporte.php')) . '" class="btn btn-brand"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nova modalidade</a>'
);
?>

<section class="page-content">
    <div class="container">
        <?php if ($mensagem !== null): ?>
            <div class="mb-4">
                <?php render_alert($mensagem[0], $mensagem[1], $mensagem[2]); ?>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="surface-card h-100">
                    <div class="surface-card-body d-flex align-items-center gap-3">
                        <span class="empty-state-icon mb-0"><i class="bi bi-trophy" aria-hidden="true"></i></span>
                        <div>
                            <span class="person-meta d-block">Modalidades cadastradas</span>
                            <strong class="fs-4"><?= $totalModalidadesCadastradas ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="surface-card h-100">
                    <div class="surface-card-body d-flex align-items-center gap-3">
                        <span class="empty-state-icon mb-0"><i class="bi bi-people" aria-hidden="true"></i></span>
                        <div>
                            <span class="person-meta d-block">Atletas com modalidade</span>
                            <strong class="fs-4"><?= $totalAtletasComModalidade ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="surface-card">
            <div class="table-toolbar">
                <div class="search-box">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <label class="visually-hidden" for="buscaModalidadesEsporte">Buscar modalidade</label>
                    <input
                        type="search"
                        class="form-control"
                        id="buscaModalidadesEsporte"
                        placeholder="Buscar por modalidade ou descrição..."
                        data-table-search
                        data-table-target="#tabelaModalidadesEsporte"
                        autocomplete="off"
                    >
                </div>
                <span class="result-count" data-result-count>
                    <?= $totalModalidadesCadastradas ?> <?= $totalModalidadesCadastradas === 1 ? 'registro encontrado' : 'registros encontrados' ?>
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle" id="tabelaModalidadesEsporte">
                    <thead>
                        <tr>
                            <th scope="col">Modalidade</th>
                            <th scope="col">Descrição</th>
                            <th scope="col">Atletas</th>
                            <th scope="col">Cadastro</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalModalidadesCadastradas === 0): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <span class="empty-state-icon"><i class="bi bi-trophy" aria-hidden="true"></i></span>
                                        <h3>Nenhuma modalidade cadastrada</h3>
                                        <p>Cadastre a primeira modalidade de esporte para vincular aos atletas.</p>
                                        <a href="<?= e(app_url('cadastro_atletas/cadastrar_modalidade_esporte.php')) ?>" class="btn btn-brand">
                                            <i class="bi bi-plus-lg" aria-hidden="true"></i> Nova modalidade
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($modalidades as $modalidade): ?>
                                <tr data-search="<?= e($modalidade['nome'] . ' ' . (string) ($modalidade['descricao'] ?? '')) ?>">
                                    <td>
                                        <span class="badge text-bg-primary"><?= e($modalidade['nome']) ?></span>
                                    </td>
                                    <td>
                                        <span class="person-meta"><?= e($modalidade['descricao'] ?: 'Sem descrição') ?></span>
                                    </td>
                                    <td class="mono"><?= (int) $modalidade['total_atletas'] ?></td>
                                    <td class="mono"><?= e(formatar_data_br($modalidade['data_cadastro'])) ?></td>
                                    <td class="text-end">
                                        <div class="row-actions">
                                            <a
                                                href="<?= e(app_url('cadastro_atletas/editar_modalidade_esporte.php?id=' . (int) $modalidade['id'])) ?>"
                                                class="action-btn"
                                                title="Editar modalidade"
                                                aria-label="Editar <?= e($modalidade['nome']) ?>"
                                            >
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </a>
                                            <a
                                                href="<?= e(app_url('cadastro_atletas/deletar_modalidade_esporte.php?id=' . (int) $modalidade['id'])) ?>"
                                                class="action-btn danger"
                                                title="Excluir modalidade"
                                                aria-label="Excluir <?= e($modalidade['nome']) ?>"
                                            >
                                                <i class="bi bi-trash3" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="d-none" data-empty-search>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <span class="empty-state-icon"><i class="bi bi-search" aria-hidden="true"></i></span>
                                        <h3>Nenhuma modalidade encontrada</h3>
                                        <p>Tente buscar por outro nome ou descrição.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="surface-card-footer">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                As modalidades cadastradas aparecem no formulário de atletas. Uma modalidade com atletas vinculados não pode ser excluída.
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
