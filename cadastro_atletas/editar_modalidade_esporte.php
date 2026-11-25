<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/conexao.php';

require_csrf();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: modalidades_esporte.php');
    exit;
}

$stmt = $mysqli->prepare('SELECT * FROM modalidades_esporte WHERE id = ? LIMIT 1');
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

$erro = null;
$valores = [
    'nome' => (string) $modalidade['nome'],
    'descricao' => (string) ($modalidade['descricao'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }

    $nome = $valores['nome'];

    if ($nome === '') {
        $erro = 'Informe o nome da modalidade.';
    } elseif (mb_strlen($nome) > 80) {
        $erro = 'O nome da modalidade deve ter no máximo 80 caracteres.';
    } else {
        $consulta = $mysqli->prepare('SELECT id FROM modalidades_esporte WHERE nome = ? AND id <> ? LIMIT 1');
        $consulta->bind_param('si', $nome, $id);
        $consulta->execute();
        $consulta->store_result();
        $duplicado = $consulta->num_rows > 0;
        $consulta->close();

        if ($duplicado) {
            $erro = 'Já existe outra modalidade com esse nome.';
        }
    }

    if ($erro === null) {
        $descricao = $valores['descricao'] !== '' ? $valores['descricao'] : null;
        $update = $mysqli->prepare('UPDATE modalidades_esporte SET nome = ?, descricao = ? WHERE id = ?');
        $update->bind_param('ssi', $nome, $descricao, $id);

        if ($update->execute()) {
            $update->close();
            header('Location: modalidades_esporte.php?sucesso=atualizado');
            exit;
        }

        $erro = 'Não foi possível atualizar a modalidade. Tente novamente.';
        $update->close();
    }
}

render_layout_start('Editar modalidade', 'esportes', 'Atualize os dados da modalidade esportiva.');
?>

<?php
render_page_header(
    'Gestão esportiva',
    'Editar modalidade',
    'Altere o nome ou a descrição conforme necessário.',
    [
        'Modalidades de esporte' => app_url('cadastro_atletas/modalidades_esporte.php'),
        'Editar modalidade' => null,
    ]
);
?>

<section class="page-content">
    <div class="container">
        <?php if ($erro !== null): ?>
            <div class="mb-4">
                <?php render_alert('danger', $erro, 'exclamation-triangle'); ?>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <form method="post" action="" class="form-card">
                    <?= csrf_field() ?>
                    <div class="form-card-header">
                        <h2><i class="bi bi-trophy me-2 text-primary" aria-hidden="true"></i> <?= e($modalidade['nome']) ?></h2>
                        <p>Cadastrada em <?= e(formatar_data_hora_br($modalidade['data_cadastro'])) ?>.</p>
                    </div>

                    <div class="form-card-body">
                        <div class="form-section">
                            <h3 class="form-section-title"><i class="bi bi-pencil" aria-hidden="true"></i> Dados da modalidade</h3>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="nome" class="form-label">Nome <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nome" name="nome" value="<?= e($valores['nome']) ?>" maxlength="80" required>
                                </div>
                                <div class="col-12">
                                    <label for="descricao" class="form-label">Descrição</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="4"><?= e($valores['descricao']) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                                Salvar alterações
                            </button>
                            <a href="<?= e(app_url('cadastro_atletas/modalidades_esporte.php')) ?>" class="btn btn-soft">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
