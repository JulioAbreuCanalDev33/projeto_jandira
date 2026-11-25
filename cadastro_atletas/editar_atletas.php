<?php
require_once __DIR__ . '/../includes/layout.php';
exigir_login();
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
        'O registro solicitado não existe ou foi removido do sistema.',
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

$erro = null;
$valores = [
    'nome' => (string) ($atleta['nome'] ?? ''),
    'rg' => (string) ($atleta['rg'] ?? ''),
    'cpf' => (string) ($atleta['cpf'] ?? ''),
    'endereco' => (string) ($atleta['endereco'] ?? ''),
    'numero' => (string) ($atleta['numero'] ?? ''),
    'cep' => (string) ($atleta['cep'] ?? ''),
    'municipio' => (string) ($atleta['municipio'] ?? ''),
    'cidade' => (string) ($atleta['cidade'] ?? ''),
    'data_nascimento' => (string) ($atleta['data_nascimento'] ?? ''),
    'email' => (string) ($atleta['email'] ?? ''),
    'telefone' => (string) ($atleta['telefone'] ?? ''),
    'modalidade_esporte_id' => (string) ($atleta['modalidade_esporte_id'] ?? ''),
];

$cpfAtual = preg_replace('/\D/', '', (string) $atleta['cpf']);
$telefoneAtual = preg_replace('/\D/', '', (string) $atleta['telefone']);
$cepAtual = preg_replace('/\D/', '', (string) $atleta['cep']);

$valores['cpf'] = strlen($cpfAtual) === 11 ? formatar_cpf($cpfAtual) : (string) $atleta['cpf'];
$valores['telefone'] = in_array(strlen($telefoneAtual), [10, 11], true) ? formatar_telefone($telefoneAtual) : (string) $atleta['telefone'];
$valores['cep'] = strlen($cepAtual) === 8 ? formatar_cep($cepAtual) : (string) $atleta['cep'];

$fotoAtual = (string) ($atleta['foto'] ?? '');
$temFotoAtual = filter_var($fotoAtual, FILTER_VALIDATE_URL) !== false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }

    $cpf = preg_replace('/\D/', '', $valores['cpf']);
    $cep = preg_replace('/\D/', '', $valores['cep']);
    $telefone = preg_replace('/\D/', '', $valores['telefone']);
    $modalidadeEsporteId = (int) ($_POST['modalidade_esporte_id'] ?? 0);
    $senha = (string) ($_POST['senha'] ?? '');

    $dataNormalizada = null;
    if ($valores['data_nascimento'] !== '') {
        $data = DateTime::createFromFormat('Y-m-d', $valores['data_nascimento']);
        if (!$data) {
            $data = DateTime::createFromFormat('d/m/Y', $valores['data_nascimento']);
        }

        if ($data && checkdate((int) $data->format('m'), (int) $data->format('d'), (int) $data->format('Y'))) {
            $dataNormalizada = $data->format('Y-m-d');
            $valores['data_nascimento'] = $dataNormalizada;
        }
    }

    if ($valores['nome'] === '') {
        $erro = 'Informe o nome completo do atleta.';
    } elseif (!filter_var($valores['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif ($dataNormalizada === null) {
        $erro = 'Informe uma data de nascimento válida.';
    } elseif (!modalidade_esporte_existe($db, $modalidadeEsporteId)) {
        $erro = 'Selecione uma modalidade esportiva válida.';
    } elseif ($telefone !== '' && !in_array(strlen($telefone), [10, 11], true)) {
        $erro = 'Informe um telefone com DDD, no formato (11) 94647-6117.';
    } elseif ($cpf !== '' && strlen($cpf) !== 11) {
        $erro = 'Informe um CPF com 11 dígitos.';
    } elseif ($cep !== '' && strlen($cep) !== 8) {
        $erro = 'Informe um CEP com 8 dígitos.';
    } elseif ($senha !== '' && (strlen($senha) < 6 || strlen($senha) > 16)) {
        $erro = 'A nova senha deve ter entre 6 e 16 caracteres.';
    }

    $caminhoFoto = $fotoAtual;
    $novaFoto = false;

    if ($erro === null && isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $foto = $_FILES['foto'];

        if ($foto['error'] !== UPLOAD_ERR_OK) {
            $erro = 'Não foi possível receber a nova foto. Tente novamente.';
        } elseif ($foto['size'] > 2 * 1024 * 1024) {
            $erro = 'A foto deve ter no máximo 2 MB.';
        } else {
            $extensao = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($extensao, $extensoesPermitidas, true)) {
                $erro = 'A foto deve estar nos formatos JPG, JPEG, PNG, GIF ou WEBP.';
            } else {
                $infoImagem = @getimagesize($foto['tmp_name']);
                $mimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];

                if ($infoImagem === false || ($infoImagem['mime'] ?? '') !== $mimes[$extensao]) {
                    $erro = 'A foto enviada não é uma imagem válida.';
                } else {
                    try {
                        $caminhoFoto = salvar_imagem($db, $foto, 'atletas');
                        $novaFoto = true;
                    } catch (Throwable) {
                        $erro = 'Não foi possível salvar a nova foto. Tente novamente.';
                        $caminhoFoto = $fotoAtual;
                    }
                }
            }
        }
    }

    if ($erro === null) {
        $senhaHash = $senha !== '' ? password_hash($senha, PASSWORD_DEFAULT) : (string) $atleta['senha'];

        $atualizados = $db->update('cadastro_atletas', [
            'foto' => $caminhoFoto,
            'nome' => $valores['nome'],
            'rg' => $valores['rg'],
            'cpf' => $cpf,
            'endereco' => $valores['endereco'],
            'numero' => $valores['numero'],
            'cep' => $cep,
            'municipio' => $valores['municipio'],
            'cidade' => $valores['cidade'],
            'data_nascimento' => $dataNormalizada,
            'email' => $valores['email'],
            'senha' => $senhaHash,
            'telefone' => $telefone,
            'modalidade_esporte_id' => $modalidadeEsporteId,
        ], ['id' => $id]);

        if ($atualizados !== []) {
            if ($novaFoto) {
                remover_imagem($db, $fotoAtual);
            }

            header('Location: atletas.php?sucesso=atualizado');
            exit;
        }

        $erro = 'Erro ao atualizar o atleta. Tente novamente.';
    }
}

$modalidadesEsporte = buscar_modalidades_esporte($db);

render_layout_start('Editar atleta', 'atletas', 'Atualize os dados do atleta no sistema.');
?>

<?php
render_page_header(
    'Gestão de atletas',
    'Editar atleta',
    'Atualize a ficha do atleta. A senha só será alterada se você preencher o campo.',
    [
        'Atletas' => app_url('cadastro_atletas/atletas.php'),
        'Editar atleta' => null,
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

        <form method="post" action="" enctype="multipart/form-data" class="form-card">
            <?= csrf_field() ?>
            <div class="form-card-header">
                <h2><i class="bi bi-person-gear me-2 text-primary" aria-hidden="true"></i> <?= e($atleta['nome']) ?></h2>
                <p>Cadastrado em <?= e(formatar_data_hora_br($atleta['data_cadastro'] ?? null)) ?>.</p>
            </div>

            <div class="form-card-body">
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-person" aria-hidden="true"></i> Dados pessoais</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="nome" class="form-label">Nome completo <span class="required">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= e($valores['nome']) ?>" autocomplete="name" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="rg" class="form-label">RG</label>
                            <input type="text" class="form-control" id="rg" name="rg" value="<?= e($valores['rg']) ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" value="<?= e($valores['cpf']) ?>" inputmode="numeric" data-mask="cpf">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="data_nascimento" class="form-label">Data de nascimento <span class="required">*</span></label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?= e($valores['data_nascimento']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-trophy" aria-hidden="true"></i> Modalidade esportiva</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="modalidade_esporte_id" class="form-label">Modalidade de esporte <span class="required">*</span></label>
                            <select class="form-select" id="modalidade_esporte_id" name="modalidade_esporte_id" required>
                                <option value="">Selecione uma modalidade</option>
                                <?php foreach ($modalidadesEsporte as $modalidade): ?>
                                    <option value="<?= (int) $modalidade['id'] ?>"<?= (int) $valores['modalidade_esporte_id'] === (int) $modalidade['id'] ? ' selected' : '' ?>>
                                        <?= e($modalidade['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <a href="<?= e(app_url('cadastro_atletas/modalidades_esporte.php')) ?>">Gerenciar modalidades de esporte</a>.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-geo-alt" aria-hidden="true"></i> Endereço</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" value="<?= e($valores['cep']) ?>" inputmode="numeric" data-mask="cep">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" value="<?= e($valores['endereco']) ?>" autocomplete="street-address">
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" value="<?= e($valores['numero']) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="municipio" class="form-label">Município</label>
                            <input type="text" class="form-control" id="municipio" name="municipio" value="<?= e($valores['municipio']) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" value="<?= e($valores['cidade']) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-envelope" aria-hidden="true"></i> Contato e acesso</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label">E-mail <span class="required">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= e($valores['email']) ?>" autocomplete="email" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= e($valores['telefone']) ?>" inputmode="tel" data-mask="phone">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="senha" class="form-label">Nova senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="senha" name="senha" minlength="6" maxlength="16" placeholder="Deixe em branco para manter" autocomplete="new-password">
                                <button class="btn btn-soft" type="button" data-toggle-password="#senha" aria-label="Mostrar senha">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="form-text">Preencha apenas se desejar definir uma nova senha.</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-camera" aria-hidden="true"></i> Foto do atleta</h3>
                    <div class="photo-upload">
                        <img
                            id="fotoPreview"
                            class="photo-preview<?= $temFotoAtual ? '' : ' d-none' ?>"
                            src="<?= $temFotoAtual ? e($fotoAtual) : '' ?>"
                            alt="Foto atual de <?= e($atleta['nome']) ?>"
                        >
                        <span class="photo-placeholder<?= $temFotoAtual ? ' d-none' : '' ?>" id="fotoPlaceholder"><i class="bi bi-person" aria-hidden="true"></i></span>
                        <p>Envie uma nova foto para substituir a atual. Formatos JPG, PNG, GIF ou WEBP, até 2 MB.</p>
                        <input
                            type="file"
                            class="form-control"
                            id="foto"
                            name="foto"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            data-photo-input="#fotoPreview"
                            data-photo-placeholder="#fotoPlaceholder"
                            aria-label="Selecionar nova foto do atleta"
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        Salvar alterações
                    </button>
                    <a href="<?= e(app_url('cadastro_atletas/atletas.php')) ?>" class="btn btn-soft">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</section>

<?php
render_layout_end();
