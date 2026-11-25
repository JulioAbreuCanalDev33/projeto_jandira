<?php
require_once __DIR__ . '/../includes/layout.php';
exigir_login();
require_once __DIR__ . '/conexao.php';

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function caminho_publico_arquivo(string $path): bool
{
    return filter_var($path, FILTER_VALIDATE_URL) !== false
        && str_contains($path, '/storage/v1/object/public/arquivos/');
}

require_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletar'])) {
    $id = (int) $_POST['deletar'];

    $arquivo = $db->selectOne('arquivos', '*', ['id' => $id]);

    if (!$arquivo) {
        flash('danger', 'Arquivo não encontrado no banco de dados.');
    } else {
        $removidos = $db->delete('arquivos', ['id' => $id]);

        if ($removidos > 0) {
            $db->removerArquivoPublico((string) ($arquivo['path'] ?? ''));
            flash('success', 'Arquivo excluído com sucesso.');
        } else {
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

    $tiposPermitidos = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
    foreach ($arquivos['name'] as $index => $nomeEnvio) {
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

        $nomeOriginal = basename(str_replace(["\r", "\n"], '', (string) $nomeEnvio));
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        if (!isset($tiposPermitidos[$extensao])) {
            $falhas++;
            continue;
        }

        $infoImagem = @getimagesize($arquivos['tmp_name'][$index]);
        if ($infoImagem === false || ($infoImagem['mime'] ?? '') !== $tiposPermitidos[$extensao]) {
            $falhas++;
            continue;
        }

        if ($nomeOriginal === '') {
            $nomeOriginal = 'arquivo.' . $extensao;
        }
        $nomeOriginal = mb_substr($nomeOriginal, 0, 255);

        $nomeArquivo = bin2hex(random_bytes(12)) . '.' . $extensao;
        $objeto = 'arquivos/' . $nomeArquivo;

        try {
            $conteudo = file_get_contents($arquivos['tmp_name'][$index]);
            if ($conteudo === false) {
                $falhas++;
                continue;
            }

            $path = $db->storageUpload('arquivos', $objeto, $conteudo, $tiposPermitidos[$extensao]);
            $registro = $db->insert('arquivos', [
                'nome' => $nomeOriginal,
                'path' => $path,
            ]);

            if ($registro !== []) {
                $enviados++;
            } else {
                $db->storageDelete('arquivos', $objeto);
                $falhas++;
            }
        } catch (Throwable) {
            $falhas++;
        }
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

$arquivos = $db->select('arquivos', '*', [], 'data_upload DESC, id DESC');
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
                            <div class="table-toolbar mb-3">
                                <div class="search-box">
                                    <i class="bi bi-search" aria-hidden="true"></i>
                                    <label class="visually-hidden" for="buscaArquivos">Buscar arquivo</label>
                                    <input
                                        type="search"
                                        class="form-control"
                                        id="buscaArquivos"
                                        placeholder="Buscar por nome..."
                                        data-file-search
                                        data-file-target="#galeriaArquivos"
                                        autocomplete="off"
                                    >
                                </div>
                                <span class="result-count" data-file-count>
                                    <?= $totalArquivos ?> <?= $totalArquivos === 1 ? 'arquivo encontrado' : 'arquivos encontrados' ?>
                                </span>
                            </div>

                            <div class="gallery-grid" id="galeriaArquivos">
                                <?php foreach ($arquivos as $arquivo): ?>
                                    <?php
                                    $path = (string) ($arquivo['path'] ?? '');
                                    $existe = caminho_publico_arquivo($path);
                                    ?>
                                    <article class="file-card" data-search="<?= e(mb_strtolower((string) $arquivo['nome'])) ?>">
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
                            <div class="empty-state d-none" data-file-empty>
                                <span class="empty-state-icon"><i class="bi bi-search" aria-hidden="true"></i></span>
                                <h3>Nenhum arquivo encontrado</h3>
                                <p>Tente outro termo para a busca.</p>
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
