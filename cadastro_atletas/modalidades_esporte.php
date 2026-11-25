<?php
require_once __DIR__ . '/../includes/layout.php';
exigir_login();
require_once __DIR__ . '/conexao.php';

require_csrf();

$acao = (string) ($_GET['acao'] ?? '');
if (!in_array($acao, ['novo', 'editar', 'excluir'], true)) {
    $acao = '';
}
$id = (int) ($_GET['id'] ?? 0);

$erro = null;
$valores = ['nome' => '', 'descricao' => ''];
$modalidadeSelecionada = null;
$totalAtletasVinculados = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAcao = (string) ($_POST['acao'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }

    $nome = $valores['nome'];
    $descricao = $valores['descricao'] !== '' ? $valores['descricao'] : null;

    if ($postAcao === 'excluir') {
        if ($id <= 0) {
            header('Location: modalidades_esporte.php');
            exit;
        }

        $vinculados = $db->count('cadastro_atletas', ['modalidade_esporte_id' => $id]);

        if ($vinculados > 0) {
            header('Location: modalidades_esporte.php?aviso=em-uso');
            exit;
        }

        $removidas = $db->delete('modalidades_esporte', ['id' => $id]);

        if ($removidas > 0) {
            header('Location: modalidades_esporte.php?sucesso=excluido');
            exit;
        }

        $erro = 'Não foi possível excluir a modalidade. Tente novamente.';
        $acao = 'excluir';
    } else {
        $acao = $postAcao === 'atualizar' ? 'editar' : 'novo';

        if ($nome === '') {
            $erro = 'Informe o nome da modalidade.';
        } elseif (mb_strlen($nome) > 80) {
            $erro = 'O nome da modalidade deve ter no máximo 80 caracteres.';
        } else {
            if ($postAcao === 'atualizar' && $id > 0) {
                $duplicado = $db->count('modalidades_esporte', [
                    'nome' => $nome,
                    'id' => 'neq.' . $id,
                ]) > 0;
            } else {
                $duplicado = $db->count('modalidades_esporte', ['nome' => $nome]) > 0;
            }

            if ($duplicado) {
                $erro = $postAcao === 'atualizar'
                    ? 'Já existe outra modalidade com esse nome.'
                    : 'Já existe uma modalidade com esse nome.';
            }
        }

        if ($erro === null) {
            if ($postAcao === 'atualizar' && $id > 0) {
                $salvo = $db->update('modalidades_esporte', [
                    'nome' => $nome,
                    'descricao' => $descricao,
                ], ['id' => $id]);
                $sucesso = 'atualizado';
            } else {
                $salvo = $db->insert('modalidades_esporte', [
                    'nome' => $nome,
                    'descricao' => $descricao,
                ]);
                $sucesso = 'cadastrado';
            }

            if ($salvo !== []) {
                header('Location: modalidades_esporte.php?sucesso=' . $sucesso);
                exit;
            }

            $erro = 'Não foi possível salvar a modalidade. Tente novamente.';
        }
    }
}

if (($acao === 'editar' || $acao === 'excluir') && $id > 0) {
    $modalidadeSelecionada = $db->selectOne('modalidades_esporte', '*', ['id' => $id]) ?? [];

    if (!$modalidadeSelecionada) {
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

    $totalAtletasVinculados = $db->count('cadastro_atletas', ['modalidade_esporte_id' => $id]);
    if ($acao === 'editar' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $valores = [
            'nome' => (string) $modalidadeSelecionada['nome'],
            'descricao' => (string) ($modalidadeSelecionada['descricao'] ?? ''),
        ];
    }
}

if (($acao === 'editar' || $acao === 'excluir') && $id <= 0) {
    header('Location: modalidades_esporte.php');
    exit;
}

$modalidades = $db->select('modalidades_esporte', '*', [], 'nome ASC');
$atletasPorModalidade = $db->select('cadastro_atletas', 'modalidade_esporte_id');
$contagemAtletas = [];
foreach ($atletasPorModalidade as $atleta) {
    $chave = (int) ($atleta['modalidade_esporte_id'] ?? 0);
    $contagemAtletas[$chave] = ($contagemAtletas[$chave] ?? 0) + 1;
}
foreach ($modalidades as &$modalidade) {
    $modalidade['total_atletas'] = $contagemAtletas[(int) $modalidade['id']] ?? 0;
}
unset($modalidade);

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

$titulos = [
    'novo' => 'Nova modalidade',
    'editar' => 'Editar modalidade',
    'excluir' => 'Excluir modalidade',
];
$tituloPagina = $titulos[$acao] ?? 'Modalidades de esporte';
$urlBase = app_url('cadastro_atletas/modalidades_esporte.php');

render_layout_start($tituloPagina, 'esportes', 'Gerencie as modalidades esportivas disponíveis no cadastro de atletas de Jandira.');
?>

<?php
$breadcrumb = ['Modalidades de esporte' => $acao === '' ? null : $urlBase];
if ($acao !== '') {
    $breadcrumb[$tituloPagina] = null;
}

render_page_header(
    'Gestão esportiva',
    $tituloPagina,
    $acao === ''
        ? 'Cadastre e organize as modalidades praticadas pelos atletas de Jandira.'
        : 'Informe os dados da modalidade esportiva e confirme a operação.',
    $breadcrumb,
    $acao === ''
        ? '<a href="' . e($urlBase . '?acao=novo') . '" class="btn btn-brand"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nova modalidade</a>'
        : '<a href="' . e($urlBase) . '" class="btn btn-soft"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar</a>'
);
?>

<section class="page-content">
    <div class="container">
        <?php if ($mensagem !== null): ?>
            <div class="mb-4">
                <?php render_alert($mensagem[0], $mensagem[1], $mensagem[2]); ?>
            </div>
        <?php endif; ?>

        <?php if ($erro !== null): ?>
            <div class="mb-4">
                <?php render_alert('danger', $erro, 'exclamation-triangle'); ?>
            </div>
        <?php endif; ?>

        <?php if ($acao === 'novo' || $acao === 'editar'): ?>
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 col-xl-7">
                    <form method="post" action="" class="form-card">
                        <?= csrf_field() ?>
                        <input type="hidden" name="acao" value="<?= $acao === 'editar' ? 'atualizar' : 'cadastrar' ?>">
                        <?php if ($acao === 'editar'): ?>
                            <input type="hidden" name="id" value="<?= (int) $id ?>">
                        <?php endif; ?>
                        <div class="form-card-header">
                            <h2><i class="bi bi-trophy me-2 text-primary" aria-hidden="true"></i> <?= e($tituloPagina) ?></h2>
                            <p>
                                <?php if ($acao === 'editar'): ?>
                                    Cadastrada em <?= e(formatar_data_hora_br($modalidadeSelecionada['data_cadastro'])) ?>.
                                <?php else: ?>
                                    Esta modalidade de esporte passará a aparecer no cadastro de atletas.
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="form-card-body">
                            <div class="form-section">
                                <h3 class="form-section-title"><i class="bi bi-info-circle" aria-hidden="true"></i> Dados da modalidade</h3>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="nome" class="form-label">Nome <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="nome" name="nome" value="<?= e($valores['nome']) ?>" placeholder="Ex.: Futebol, Judô, Natação" maxlength="80" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="descricao" class="form-label">Descrição</label>
                                        <textarea class="form-control" id="descricao" name="descricao" rows="4" placeholder="Detalhes sobre a prática dessa modalidade."><?= e($valores['descricao']) ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-brand">
                                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                                    <?= $acao === 'editar' ? 'Salvar alterações' : 'Cadastrar modalidade' ?>
                                </button>
                                <a href="<?= e($urlBase) ?>" class="btn btn-soft">
                                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($acao === 'excluir'): ?>
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 col-xl-7">
                    <?php if ($totalAtletasVinculados > 0): ?>
                        <div class="mb-4">
                            <?php render_alert('warning', 'Esta modalidade está em uso por ' . $totalAtletasVinculados . ' ' . ($totalAtletasVinculados === 1 ? 'atleta' : 'atletas') . ' e não pode ser excluída.', 'exclamation-triangle'); ?>
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
                                    <span class="person-name d-block"><?= e($modalidadeSelecionada['nome']) ?></span>
                                    <span class="person-meta"><?= e($modalidadeSelecionada['descricao'] ?: 'Sem descrição') ?></span>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-12 col-sm-6">
                                    <span class="person-meta d-block">Atletas vinculados</span>
                                    <strong class="mono"><?= $totalAtletasVinculados ?></strong>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <span class="person-meta d-block">Cadastrada em</span>
                                    <strong class="mono"><?= e(formatar_data_hora_br($modalidadeSelecionada['data_cadastro'])) ?></strong>
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
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= (int) $id ?>">
                                <a href="<?= e($urlBase) ?>" class="btn btn-soft">
                                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                    Cancelar
                                </a>
                                <?php if ($totalAtletasVinculados === 0): ?>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                        Sim, excluir modalidade
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
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
                                            <a href="<?= e($urlBase . '?acao=novo') ?>" class="btn btn-brand">
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
                                                    href="<?= e($urlBase . '?acao=editar&id=' . (int) $modalidade['id']) ?>"
                                                    class="action-btn"
                                                    title="Editar modalidade"
                                                    aria-label="Editar <?= e($modalidade['nome']) ?>"
                                                >
                                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                                </a>
                                                <a
                                                    href="<?= e($urlBase . '?acao=excluir&id=' . (int) $modalidade['id']) ?>"
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
        <?php endif; ?>
    </div>
</section>

<?php
render_layout_end();
