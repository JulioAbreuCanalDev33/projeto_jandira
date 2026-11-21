<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/conexao.php';

$sqlAtletas = 'SELECT * FROM cadastro_atletas ORDER BY nome ASC';
$queryAtletas = $mysqli->query($sqlAtletas);

if (!$queryAtletas) {
    die('Erro na consulta ao banco de dados: ' . e($mysqli->error));
}

$atletas = $queryAtletas->fetch_all(MYSQLI_ASSOC);
$totalAtletas = count($atletas);

$mensagens = [
    'cadastrado' => ['success', 'Atleta cadastrado com sucesso.'],
    'atualizado' => ['success', 'Dados do atleta atualizados com sucesso.'],
    'excluido' => ['success', 'Atleta excluído com sucesso.'],
];

$mensagem = $mensagens[$_GET['sucesso'] ?? ''] ?? null;

render_layout_start('Atletas', 'atletas', 'Consulte e gerencie os atletas cadastrados no projeto.');
?>

<?php
render_page_header(
    'Gestão de atletas',
    'Atletas',
    'Consulte a equipe cadastrada, atualize informações e mantenha os dados sempre em dia.',
    ['Atletas' => null],
    '<a href="' . e(app_url('cadastro_atletas/cadastrar_atletas.php')) . '" class="btn btn-brand"><i class="bi bi-person-plus" aria-hidden="true"></i> Cadastrar atleta</a>'
);
?>

<section class="page-content">
    <div class="container">
        <?php if ($mensagem !== null): ?>
            <div class="mb-4">
                <?php render_alert($mensagem[0], $mensagem[1], $mensagem[0] === 'success' ? 'check-circle' : 'info-circle'); ?>
            </div>
        <?php endif; ?>

        <div class="surface-card">
            <div class="table-toolbar">
                <div class="search-box">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <label class="visually-hidden" for="buscaAtletas">Buscar atleta</label>
                    <input
                        type="search"
                        class="form-control"
                        id="buscaAtletas"
                        placeholder="Buscar por nome, CPF, e-mail ou cidade..."
                        data-table-search
                        data-table-target="#tabelaAtletas"
                        autocomplete="off"
                    >
                </div>
                <span class="result-count" data-result-count>
                    <?= $totalAtletas ?> <?= $totalAtletas === 1 ? 'registro encontrado' : 'registros encontrados' ?>
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle" id="tabelaAtletas">
                    <thead>
                        <tr>
                            <th scope="col">Atleta</th>
                            <th scope="col">CPF</th>
                            <th scope="col">Telefone</th>
                            <th scope="col">Cidade</th>
                            <th scope="col">Nascimento</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalAtletas === 0): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <span class="empty-state-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                                        <h3>Nenhum atleta cadastrado</h3>
                                        <p>Comece agora e adicione o primeiro atleta da equipe.</p>
                                        <a href="<?= e(app_url('cadastro_atletas/cadastrar_atletas.php')) ?>" class="btn btn-brand">
                                            <i class="bi bi-person-plus" aria-hidden="true"></i> Cadastrar atleta
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($atletas as $atleta): ?>
                                <?php
                                $fotoRelativa = (string) ($atleta['foto'] ?? '');
                                $temFoto = $fotoRelativa !== '' && file_exists(__DIR__ . '/' . $fotoRelativa);
                                $textoBusca = implode(' ', [
                                    $atleta['nome'] ?? '',
                                    $atleta['email'] ?? '',
                                    $atleta['cpf'] ?? '',
                                    $atleta['cidade'] ?? '',
                                    $atleta['telefone'] ?? '',
                                ]);
                                ?>
                                <tr data-search="<?= e($textoBusca) ?>">
                                    <td>
                                        <div class="person-cell">
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
                                    </td>
                                    <td class="mono"><?= e(!empty($atleta['cpf']) ? formatar_cpf($atleta['cpf']) : '—') ?></td>
                                    <td class="mono"><?= e(!empty($atleta['telefone']) ? formatar_telefone($atleta['telefone']) : '—') ?></td>
                                    <td>
                                        <?= e($atleta['cidade'] ?: '—') ?>
                                        <?php if (!empty($atleta['municipio'])): ?>
                                            <span class="person-meta"><?= e($atleta['municipio']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="mono"><?= e(formatar_data_br($atleta['data_nascimento'])) ?></td>
                                    <td class="text-end">
                                        <div class="row-actions">
                                            <a
                                                href="editar_atletas.php?id=<?= (int) $atleta['id'] ?>"
                                                class="action-btn"
                                                title="Editar atleta"
                                                aria-label="Editar <?= e($atleta['nome']) ?>"
                                            >
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </a>
                                            <a
                                                href="deletar_atletas.php?id=<?= (int) $atleta['id'] ?>"
                                                class="action-btn danger"
                                                title="Excluir atleta"
                                                aria-label="Excluir <?= e($atleta['nome']) ?>"
                                            >
                                                <i class="bi bi-trash3" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="d-none" data-empty-search>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <span class="empty-state-icon"><i class="bi bi-search" aria-hidden="true"></i></span>
                                        <h3>Nenhum atleta encontrado</h3>
                                        <p>Tente buscar por outro nome, CPF, e-mail ou cidade.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="surface-card-footer">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                Os dados completos de endereço e documentação podem ser consultados na edição de cada atleta.
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
