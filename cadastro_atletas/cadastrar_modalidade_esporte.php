<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/conexao.php';

require_csrf();

$erro = null;
$valores = ['nome' => '', 'descricao' => ''];

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
        $consulta = $mysqli->prepare('SELECT id FROM modalidades_esporte WHERE nome = ? LIMIT 1');
        $consulta->bind_param('s', $nome);
        $consulta->execute();
        $consulta->store_result();
        $duplicado = $consulta->num_rows > 0;
        $consulta->close();

        if ($duplicado) {
            $erro = 'Já existe uma modalidade com esse nome.';
        }
    }

    if ($erro === null) {
        $stmt = $mysqli->prepare('INSERT INTO modalidades_esporte (nome, descricao) VALUES (?, ?)');
        $descricao = $valores['descricao'] !== '' ? $valores['descricao'] : null;
        $stmt->bind_param('ss', $nome, $descricao);

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: modalidades_esporte.php?sucesso=cadastrado');
            exit;
        }

        $erro = 'Não foi possível cadastrar a modalidade. Tente novamente.';
        $stmt->close();
    }
}

render_layout_start('Nova modalidade', 'esportes', 'Cadastre uma nova modalidade esportiva.');
?>

<?php
render_page_header(
    'Gestão esportiva',
    'Nova modalidade',
    'Informe o nome e uma breve descrição da modalidade esportiva.',
    [
        'Modalidades de esporte' => app_url('cadastro_atletas/modalidades_esporte.php'),
        'Nova modalidade' => null,
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
                        <h2><i class="bi bi-trophy me-2 text-primary" aria-hidden="true"></i> Nova modalidade</h2>
                        <p>Esta modalidade de esporte passará a aparecer no cadastro de atletas.</p>
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
                                Cadastrar modalidade
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
