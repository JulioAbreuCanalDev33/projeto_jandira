<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/conexao.php';

require_csrf();

$erro = null;
$valores = [
    'titulo' => '',
    'descricao' => '',
    'data_inicio' => '',
    'data_fim' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }

    $inicio = DateTime::createFromFormat('Y-m-d\TH:i', $valores['data_inicio'])
        ?: DateTime::createFromFormat('Y-m-d\TH:i:s', $valores['data_inicio']);
    $fim = DateTime::createFromFormat('Y-m-d\TH:i', $valores['data_fim'])
        ?: DateTime::createFromFormat('Y-m-d\TH:i:s', $valores['data_fim']);

    if ($valores['titulo'] === '') {
        $erro = 'Informe o título do evento.';
    } elseif (!$inicio || !$fim) {
        $erro = 'Informe a data e o horário de início e término do evento.';
    } elseif ($fim <= $inicio) {
        $erro = 'O término do evento deve ser posterior ao início.';
    }

    if ($erro === null) {
        $dataInicio = $inicio->format('Y-m-d H:i:s');
        $dataFim = $fim->format('Y-m-d H:i:s');

        $stmt = $mysqli->prepare(
            'INSERT INTO eventos (titulo, descricao, data_inicio, data_fim) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('ssss', $valores['titulo'], $valores['descricao'], $dataInicio, $dataFim);

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: adicionar_eventos.php?sucesso=1');
            exit;
        }

        $erro = 'Não foi possível cadastrar o evento. Tente novamente.';
        $stmt->close();
    }
}

$queryEventos = $mysqli->query('SELECT * FROM eventos ORDER BY data_inicio DESC');
$eventos = $queryEventos ? $queryEventos->fetch_all(MYSQLI_ASSOC) : [];
$totalEventos = count($eventos);

$meses = [
    1 => 'JAN', 2 => 'FEV', 3 => 'MAR', 4 => 'ABR', 5 => 'MAI', 6 => 'JUN',
    7 => 'JUL', 8 => 'AGO', 9 => 'SET', 10 => 'OUT', 11 => 'NOV', 12 => 'DEZ',
];

render_layout_start('Eventos', 'eventos', 'Organize a agenda de eventos esportivos de Jandira.');
?>

<?php
render_page_header(
    'Agenda esportiva',
    'Eventos',
    'Cadastre competições, encontros e atividades e acompanhe a agenda do esporte de Jandira.',
    ['Eventos' => null]
);
?>

<section class="page-content">
    <div class="container">
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="mb-4">
                <?php render_alert('success', 'Evento adicionado com sucesso.', 'check-circle'); ?>
            </div>
        <?php endif; ?>

        <?php if ($erro !== null): ?>
            <div class="mb-4">
                <?php render_alert('danger', $erro, 'exclamation-triangle'); ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="form-card">
                    <div class="form-card-header">
                        <h2><i class="bi bi-calendar-plus me-2 text-primary" aria-hidden="true"></i> Novo evento</h2>
                        <p>Preencha as informações principais da atividade.</p>
                    </div>

                    <div class="form-card-body">
                        <form method="post" action="">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título do evento <span class="required">*</span></label>
                                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= e($valores['titulo']) ?>" placeholder="Ex.: Campeonato Regional de Judô" required>
                            </div>

                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="4" placeholder="Informações sobre o evento, local, público e programação."><?= e($valores['descricao']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="data_inicio" class="form-label">Início <span class="required">*</span></label>
                                <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio" value="<?= e($valores['data_inicio']) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="data_fim" class="form-label">Término <span class="required">*</span></label>
                                <input type="datetime-local" class="form-control" id="data_fim" name="data_fim" value="<?= e($valores['data_fim']) ?>" required>
                            </div>

                            <button type="submit" class="btn btn-brand w-100">
                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                Adicionar evento
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="surface-card">
                    <div class="surface-card-header">
                        <div>
                            <h2><i class="bi bi-calendar3 me-2 text-primary" aria-hidden="true"></i> Agenda de eventos</h2>
                            <p>Atividades e competições registradas na agenda.</p>
                        </div>
                        <span class="result-count"><?= $totalEventos ?> <?= $totalEventos === 1 ? 'evento' : 'eventos' ?></span>
                    </div>

                    <div class="surface-card-body">
                        <?php if ($totalEventos === 0): ?>
                            <div class="empty-state">
                                <span class="empty-state-icon"><i class="bi bi-calendar-x" aria-hidden="true"></i></span>
                                <h3>Nenhum evento cadastrado</h3>
                                <p>Use o formulário ao lado para adicionar a primeira atividade à agenda.</p>
                            </div>
                        <?php else: ?>
                            <div class="event-list">
                                <?php foreach ($eventos as $evento): ?>
                                    <?php
                                    $inicioTs = strtotime((string) $evento['data_inicio']);
                                    $fimTs = strtotime((string) $evento['data_fim']);
                                    ?>
                                    <article class="event-card">
                                        <div class="event-date" aria-hidden="true">
                                            <strong><?= $inicioTs ? date('d', $inicioTs) : '—' ?></strong>
                                            <small><?= $inicioTs ? $meses[(int) date('n', $inicioTs)] : '' ?></small>
                                        </div>
                                        <div class="event-info">
                                            <h3><?= e($evento['titulo']) ?></h3>
                                            <?php if (!empty($evento['descricao'])): ?>
                                                <p><?= e($evento['descricao']) ?></p>
                                            <?php endif; ?>
                                            <span class="event-period">
                                                <i class="bi bi-clock" aria-hidden="true"></i>
                                                <?= e(formatar_data_hora_br($evento['data_inicio'])) ?>
                                                <?php if (!empty($evento['data_fim'])): ?>
                                                    até <?= e(formatar_data_hora_br($evento['data_fim'])) ?>
                                                <?php endif; ?>
                                            </span>
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
