<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/conexao.php';

require_csrf();

$erro = null;
$valores = [
    'nome' => '',
    'rg' => '',
    'cpf' => '',
    'endereco' => '',
    'numero' => '',
    'cep' => '',
    'municipio' => '',
    'cidade' => '',
    'data_nascimento' => '',
    'email' => '',
    'telefone' => '',
    'modalidade_esporte_id' => '',
];

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
    } elseif (!modalidade_esporte_existe($mysqli, $modalidadeEsporteId)) {
        $erro = 'Selecione uma modalidade esportiva válida.';
    } elseif ($telefone !== '' && !in_array(strlen($telefone), [10, 11], true)) {
        $erro = 'Informe um telefone com DDD, no formato (11) 94647-6117.';
    } elseif ($cpf !== '' && strlen($cpf) !== 11) {
        $erro = 'Informe um CPF com 11 dígitos.';
    } elseif ($cep !== '' && strlen($cep) !== 8) {
        $erro = 'Informe um CEP com 8 dígitos.';
    } elseif (strlen($senha) < 6 || strlen($senha) > 16) {
        $erro = 'A senha deve ter entre 6 e 16 caracteres.';
    }

    $caminhoFoto = null;
    if ($erro === null) {
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $erro = 'Selecione uma foto do atleta.';
        } else {
            $foto = $_FILES['foto'];

            if ($foto['size'] > 2 * 1024 * 1024) {
                $erro = 'A foto deve ter no máximo 2 MB.';
            } else {
                $extensao = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
                $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array($extensao, $extensoesPermitidas, true)) {
                    $erro = 'A foto deve estar nos formatos JPG, JPEG, PNG, GIF ou WEBP.';
                } else {
                    $nomeArquivo = uniqid('', true) . '.' . $extensao;
                    $caminhoFoto = 'arquivos/' . $nomeArquivo;
                    $destino = __DIR__ . '/' . $caminhoFoto;

                    if (!move_uploaded_file($foto['tmp_name'], $destino)) {
                        $erro = 'Não foi possível salvar a foto. Tente novamente.';
                        $caminhoFoto = null;
                    }
                }
            }
        }
    }

    if ($erro === null && $caminhoFoto !== null) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare(
            'INSERT INTO cadastro_atletas
                (nome, rg, cpf, endereco, numero, cep, municipio, cidade, data_nascimento, email, senha, telefone, modalidade_esporte_id, foto)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->bind_param(
            'sssssssssssssi',
            $valores['nome'],
            $valores['rg'],
            $cpf,
            $valores['endereco'],
            $valores['numero'],
            $cep,
            $valores['municipio'],
            $valores['cidade'],
            $dataNormalizada,
            $valores['email'],
            $senhaHash,
            $telefone,
            $modalidadeEsporteId,
            $caminhoFoto
        );

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: atletas.php?sucesso=cadastrado');
            exit;
        }

        $erro = 'Erro ao cadastrar o atleta. Tente novamente.';
        $stmt->close();
    }
}

$modalidadesEsporte = buscar_modalidades_esporte($mysqli);

render_layout_start('Novo atleta', 'atletas', 'Cadastre um novo atleta na plataforma de gestão esportiva de Jandira.');
?>

<?php
render_page_header(
    'Gestão de atletas',
    'Novo atleta',
    'Preencha a ficha abaixo. Os campos marcados com * são obrigatórios.',
    [
        'Atletas' => app_url('cadastro_atletas/atletas.php'),
        'Novo atleta' => null,
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
                <h2><i class="bi bi-person-vcard me-2 text-primary" aria-hidden="true"></i> Ficha do atleta</h2>
                <p>Dados pessoais, endereço, contato e foto para identificação.</p>
            </div>

            <div class="form-card-body">
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-person" aria-hidden="true"></i> Dados pessoais</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="nome" class="form-label">Nome completo <span class="required">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= e($valores['nome']) ?>" placeholder="Ex.: Maria Oliveira Santos" autocomplete="name" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="rg" class="form-label">RG</label>
                            <input type="text" class="form-control" id="rg" name="rg" value="<?= e($valores['rg']) ?>" placeholder="00.000.000-0">
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" value="<?= e($valores['cpf']) ?>" placeholder="000.000.000-00" inputmode="numeric" data-mask="cpf">
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
                                <?php if (count($modalidadesEsporte) === 0): ?>
                                    Nenhuma modalidade cadastrada. <a href="<?= e(app_url('cadastro_atletas/cadastrar_modalidade_esporte.php')) ?>">Cadastre uma modalidade</a> antes de continuar.
                                <?php else: ?>
                                    Não encontrou a modalidade? <a href="<?= e(app_url('cadastro_atletas/cadastrar_modalidade_esporte.php')) ?>">Cadastre uma nova</a>.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-geo-alt" aria-hidden="true"></i> Endereço</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" value="<?= e($valores['cep']) ?>" placeholder="00000-000" inputmode="numeric" data-mask="cep">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" value="<?= e($valores['endereco']) ?>" placeholder="Rua, avenida ou praça" autocomplete="street-address">
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" value="<?= e($valores['numero']) ?>" placeholder="123">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="municipio" class="form-label">Município</label>
                            <input type="text" class="form-control" id="municipio" name="municipio" value="<?= e($valores['municipio']) ?>" placeholder="Jandira">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" value="<?= e($valores['cidade']) ?>" placeholder="São Paulo">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-envelope" aria-hidden="true"></i> Contato e acesso</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label">E-mail <span class="required">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= e($valores['email']) ?>" placeholder="atleta@email.com" autocomplete="email" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= e($valores['telefone']) ?>" placeholder="(11) 94647-6117" inputmode="tel" data-mask="phone">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="senha" class="form-label">Senha de acesso <span class="required">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="senha" name="senha" minlength="6" maxlength="16" placeholder="Entre 6 e 16 caracteres" autocomplete="new-password" required>
                                <button class="btn btn-soft" type="button" data-toggle-password="#senha" aria-label="Mostrar senha">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="form-text">Essa senha será armazenada com criptografia.</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-camera" aria-hidden="true"></i> Foto do atleta</h3>
                    <div class="photo-upload">
                        <span class="photo-placeholder" id="fotoPlaceholder"><i class="bi bi-person" aria-hidden="true"></i></span>
                        <img id="fotoPreview" class="photo-preview d-none" alt="Pré-visualização da foto">
                        <p>Envie uma foto nítida, nos formatos JPG, PNG, GIF ou WEBP, com até 2 MB.</p>
                        <input
                            type="file"
                            class="form-control"
                            id="foto"
                            name="foto"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            data-photo-input="#fotoPreview"
                            data-photo-placeholder="#fotoPlaceholder"
                            aria-label="Selecionar foto do atleta"
                            required
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        Cadastrar atleta
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
