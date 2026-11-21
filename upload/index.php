<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/conexao.php';

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

require_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletar'])) {
    $id = (int) $_POST['deletar'];

    $stmt = $mysqli->prepare('SELECT * FROM arquivos WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $arquivo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$arquivo) {
        flash('danger', 'Arquivo não encontrado no banco de dados.');
    } else {
        $delete = $mysqli->prepare('DELETE FROM arquivos WHERE id = ?');
        $delete->bind_param('i', $id);

        if ($delete->execute()) {
            $delete->close();

            $path = (string) ($arquivo['path'] ?? '');
            if ($path !== '' && str_starts_with($path, 'arquivos/')) {
                $arquivoFisico = __DIR__ . '/' . $path;
                if (file_exists($arquivoFisico)) {
                    @unlink($arquivoFisico);
                }
            }

            flash('success', 'Arquivo excluído com sucesso.');
        } else {
            $delete->close();
            flash('danger', 'Não foi possível excluir o arquivo.');
        }
    }

    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivos'])) {
    $arquivos = $_FILES['arquivos'];
    $enviados = 0;
    $falhas = 0;

    if (!is_array($arquivos['name'])) {
        $arquivos = [
            'name' => [$arquivos['name']],
            'type' => [$arquivos['type']],
            'tmp_name' => [$arquivos['tmp_name']],
            'error' => [$arquivos['error']],
            'size' => [$arquivos['size']],
        ];
    }

    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    foreach ($arquivos['name'] as $index => $nomeOriginal) {
        if (($arquivos['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($arquivos['error'][$index] !== UPLOAD_ERR_OK) {
            $falhas++;
            continue;
        }

        if ($arquivos['size'][$index] > 2 * 1024 * 1024) {
            $falhas++;
            continue;
        }

        $extensao = strtolower(pathinfo((string) $nomeOriginal, PATHINFO_EXTENSION));

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            $falhas++;
            continue;
        }

        $nomeArquivo = uniqid('', true) . '.' . $extensao;
        $path = 'arquivos/' . $nomeArquivo;
        $destino = __DIR__ . '/' . $path;

        if (!move_uploaded_file($arquivos['tmp_name'][$index], $destino)) {
            $falhas++;
            continue;
        }

        $stmt = $mysqli->prepare('INSERT INTO arquivos (nome, path) VALUES (?, ?)');
        $stmt->bind_param('ss', $nomeOriginal, $path);

        if ($stmt->execute()) {
            $enviados++;
        } else {
            $falhas++;
            @unlink($destino);
        }

        $stmt->close();
    }

    if ($enviados > 0) {
        flash('success', $enviados === 1 ? 'Arquivo enviado com sucesso.' : $enviados . ' arquivos enviados com sucesso.');
    }

    if ($falhas > 0) {
        flash('warning', $falhas === 1 ? 'Um arquivo não pôde ser enviado. Verifique o formato e o tamanho.' : $falhas . ' arquivos não puderam ser enviados. Verifique formato e tamanho.');
    }

    if ($enviados === 0 && $falhas === 0) {
        flash('info', 'Selecione ao menos um arquivo para enviar.');
    }

    header('Location: index.php');
    exit;
}

$flashes = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);

$queryArquivos = $mysqli->query('SELECT * FROM arquivos ORDER BY data_upload DESC, id DESC');
$arquivos = $queryArquivos ? $queryArquivos->fetch_all(MYSQLI_ASSOC) : [];
$totalArquivos = count($arquivos);

render_layout_start('Arquivos', 'arquivos', 'Envie e organize fotos e documentos do projeto esportivo.');
?>

<?php
render_page_header(
    'Documentos e imagens',
    'Arquivos',
    'Centralize fotos, documentos e materiais de apoio do projeto em um só lugar.',
    ['Arquivos' => null]
);
?>

<section class="page-content">
    <div class="container">
        <?php foreach ($flashes as $flash): ?>
            <div class="mb-3">
                <?php render_alert($flash['type'], $flash['message'], $flash['type'] === 'success' ? 'check-circle' : 'info-circle'); ?>
            </div>
        <?php endforeach; ?>

        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="form-card">
                    <div class="form-card-header">
                        <h2><i class="bi bi-cloud-arrow-up me-2 text-primary" aria-hidden="true"></i> Enviar arquivos</h2>
                        <p>Selecione uma ou mais imagens para enviar.</p>
                    </div>
                    <div class="form-card-body">
                        <form method="post" action="" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="upload-drop">
                                <span class="upload-drop-icon"><i class="bi bi-images" aria-hidden="true"></i></span>
                                <h3>Fotos e materiais</h3>
                                <p>JPG, PNG, GIF ou WEBP · até 2 MB por arquivo.</p>
                                <input type="file" class="form-control" name="arquivos[]" accept="image/jpeg,image/png,image/gif,image/webp" aria-label="Selecionar arquivos para envio" multiple required>
                            </div>

                            <button type="submit" class="btn btn-brand w-100 mt-3">
                                <i class="bi bi-upload" aria-hidden="true"></i>
                                Enviar arquivos
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="surface-card">
                    <div class="surface-card-header">
                        <div>
                            <h2><i class="bi bi-folder2-open me-2 text-primary" aria-hidden="true"></i> Arquivos enviados</h2>
                            <p>Galeria de imagens disponíveis no sistema.</p>
                        </div>
                        <span class="result-count"><?= $totalArquivos ?> <?= $totalArquivos === 1 ? 'arquivo' : 'arquivos' ?></span>
                    </div>

                    <div class="surface-card-body">
                        <?php if ($totalArquivos === 0): ?>
                            <div class="empty-state">
                                <span class="empty-state-icon"><i class="bi bi-images" aria-hidden="true"></i></span>
                                <h3>Nenhum arquivo enviado</h3>
                                <p>Use o formulário ao lado para adicionar as primeiras imagens.</p>
                            </div>
                        <?php else: ?>
                            <div class="gallery-grid">
                                <?php foreach ($arquivos as $arquivo): ?>
                                    <?php
                                    $path = (string) ($arquivo['path'] ?? '');
                                    $existe = $path !== '' && file_exists(__DIR__ . '/' . $path);
                                    ?>
                                    <article class="file-card">
                                        <div class="file-thumb">
                                            <?php if ($existe): ?>
                                                <img src="<?= e($path) ?>" alt="<?= e($arquivo['nome']) ?>" loading="lazy">
                                            <?php else: ?>
                                                <i class="bi bi-file-earmark-image file-icon" aria-hidden="true"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="file-body">
                                            <span class="file-name" title="<?= e($arquivo['nome']) ?>"><?= e($arquivo['nome']) ?></span>
                                            <span class="file-meta">
                                                <i class="bi bi-clock" aria-hidden="true"></i>
                                                <?= e(formatar_data_hora_br($arquivo['data_upload'])) ?>
                                            </span>
                                            <div class="file-actions">
                                                <?php if ($existe): ?>
                                                    <a class="action-btn" href="<?= e($path) ?>" target="_blank" rel="noopener" title="Abrir arquivo" aria-label="Abrir <?= e($arquivo['nome']) ?>">
                                                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <form method="post" action="" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="deletar" value="<?= (int) $arquivo['id'] ?>">
                                                    <button
                                                        type="submit"
                                                        class="action-btn danger"
                                                        title="Excluir arquivo"
                                                        aria-label="Excluir <?= e($arquivo['nome']) ?>"
                                                        data-confirm="Tem certeza que deseja excluir este arquivo? Esta ação não pode ser desfeita."
                                                    >
                                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
