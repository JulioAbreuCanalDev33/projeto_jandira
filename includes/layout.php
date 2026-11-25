<?php
declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once __DIR__ . '/env.php';
load_env(APP_ROOT . '/.env');
require_once __DIR__ . '/supabase.php';

if (session_status() === PHP_SESSION_NONE) {
    // A Vercel não mantém arquivos de sessão entre funções: guardamos no Supabase.
    session_set_save_handler(new SupabaseSessionHandler(supabase_db()), true);
    session_start();
}

/**
 * Caminho relativo da aplicação em relação à raiz do servidor.
 * Funciona tanto em http://localhost:8000/ quanto em subpastas.
 */
function app_base_path(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $appRoot = realpath(APP_ROOT);
    $base = '';

    if ($docRoot && $appRoot) {
        $docRoot = str_replace('\\', '/', $docRoot);
        $appRoot = str_replace('\\', '/', $appRoot);

        if (str_starts_with($appRoot, $docRoot)) {
            $base = substr($appRoot, strlen($docRoot));
        }
    }

    return $base = rtrim($base, '/');
}

function app_url(string $path = ''): string
{
    $path = ltrim($path, '/');

    return app_base_path() . ($path === '' ? '/' : '/' . $path);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_logged_in(): bool
{
    return isset($_SESSION['usuario']);
}

/**
 * Gera (uma única vez por sessão) e retorna o token CSRF.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Campo oculto pronto para inclusão em formulários POST.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(mixed $token): bool
{
    return is_string($token)
        && $token !== ''
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Interrompe requisições POST sem token CSRF válido com uma página amigável.
 */
function require_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (csrf_verify($_POST['csrf_token'] ?? null)) {
        return;
    }

    http_response_code(419);
    render_layout_start(
        'Sessão expirada',
        '',
        'Não foi possível confirmar a autenticidade do formulário enviado.'
    );
    render_page_header(
        'Segurança',
        'Sessão expirada',
        'O token de segurança do formulário não é mais válido. Isso pode acontecer após um longo período de inatividade.',
        ['Sessão expirada' => null]
    );
    ?>
    <section class="page-content">
        <div class="container">
            <div class="surface-card">
                <div class="empty-state">
                    <span class="empty-state-icon"><i class="bi bi-shield-exclamation" aria-hidden="true"></i></span>
                    <h3>Formulário não verificado</h3>
                    <p>Recarregue a página para gerar um novo token de segurança e envie os dados novamente.</p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <button type="button" class="btn btn-brand" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Recarregar página
                        </button>
                        <a href="<?= e(app_url('')) ?>" class="btn btn-soft">
                            <i class="bi bi-house-door" aria-hidden="true"></i> Voltar ao início
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    render_layout_end();
    exit;
}

function formatar_data_br(?string $data): string
{
    if (empty($data)) {
        return '—';
    }

    $timestamp = strtotime($data);

    return $timestamp ? date('d/m/Y', $timestamp) : $data;
}

function formatar_data_hora_br(?string $data): string
{
    if (empty($data)) {
        return '—';
    }

    $timestamp = strtotime($data);

    return $timestamp ? date('d/m/Y \à\s H:i', $timestamp) : $data;
}

function iniciais(string $nome): string
{
    $partes = preg_split('/\s+/', trim($nome)) ?: [];
    $partes = array_values(array_filter($partes));

    if (count($partes) === 0) {
        return '?';
    }

    $primeira = mb_substr($partes[0], 0, 1);
    $ultima = count($partes) > 1 ? mb_substr($partes[count($partes) - 1], 0, 1) : '';

    return mb_strtoupper($primeira . $ultima);
}

function render_layout_start(string $title, string $active = '', string $description = ''): void
{
    $pageTitle = $title === 'Início'
        ? 'Projeto Jandira · Esporte e Cidadania'
        : $title . ' · Projeto Jandira';

    $navItems = [
        'inicio' => ['label' => 'Início', 'href' => app_url('')],
        'atletas' => ['label' => 'Atletas', 'href' => app_url('cadastro_atletas/atletas.php')],
        'esportes' => ['label' => 'Modalidades de esporte', 'href' => app_url('cadastro_atletas/modalidades_esporte.php')],
        'eventos' => ['label' => 'Eventos', 'href' => app_url('cadastro_atletas/adicionar_eventos.php')],
        'arquivos' => ['label' => 'Arquivos', 'href' => app_url('upload/index.php')],
    ];
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($description !== '' ? $description : 'Sistema de gestão esportiva de Jandira: atletas, eventos e documentos em um só lugar.') ?>">
    <meta name="theme-color" content="#0b4ea2">
    <title><?= e($pageTitle) ?></title>
    <script>
        (function () {
            try {
                var stored = window.localStorage.getItem('jandira-theme');
                var theme = stored === 'dark' || stored === 'light'
                    ? stored
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.setAttribute('data-bs-theme', theme);
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'light');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(app_url('assets/css/site.css')) ?>" rel="stylesheet">
</head>
<body>
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

<header class="site-header">
    <div class="topbar">
        <div class="container topbar-inner">
            <span class="topbar-location">
                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                Jandira · São Paulo
            </span>
            <div class="topbar-actions">
                <span class="topbar-note d-none d-md-inline">Esporte e Cidadania</span>
                <button
                    type="button"
                    class="theme-toggle"
                    data-theme-toggle
                    aria-label="Ativar tema escuro"
                    aria-pressed="false"
                    title="Alternar tema claro/escuro"
                >
                    <i class="bi bi-moon-stars" aria-hidden="true"></i>
                </button>
                <?php if (is_logged_in()): ?>
                    <a href="<?= e(app_url('criptografia/index.php')) ?>">
                        <i class="bi bi-speedometer2" aria-hidden="true"></i> Painel
                    </a>
                    <form method="post" action="<?= e(app_url('criptografia/logout.php')) ?>" class="topbar-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="topbar-link-button">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sair
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= e(app_url('criptografia/login.php')) ?>">
                        <i class="bi bi-lock-fill" aria-hidden="true"></i> Área restrita
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg mainbar" aria-label="Navegação principal">
        <div class="container">
            <a class="navbar-brand brand" href="<?= e(app_url('')) ?>">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48" role="img">
                        <rect width="48" height="48" rx="14" fill="currentColor"/>
                        <path d="M29 13v15.4c0 4.8-3 7.6-7.4 7.6-3.3 0-5.9-1.8-7.1-4.7" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round"/>
                        <circle cx="33.5" cy="13" r="3.2" fill="#f4b400"/>
                    </svg>
                </span>
                <span class="brand-text">
                    <strong>JANDIRA</strong>
                    <small>Esporte e Cidadania</small>
                </span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mainnav mx-auto">
                    <?php foreach ($navItems as $key => $item): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $active === $key ? ' active' : '' ?>" href="<?= e($item['href']) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
                                <?= e($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a class="btn btn-brand btn-nav-cta" href="<?= e(app_url('cadastro_atletas/cadastrar_atletas.php')) ?>">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Cadastrar atleta
                </a>
            </div>
        </div>
    </nav>
</header>

<main id="conteudo">
<?php
}

function render_layout_end(): void
{
    ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-5">
                <a class="brand brand-footer" href="<?= e(app_url('')) ?>">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 48 48" role="img">
                            <rect width="48" height="48" rx="14" fill="currentColor"/>
                            <path d="M29 13v15.4c0 4.8-3 7.6-7.4 7.6-3.3 0-5.9-1.8-7.1-4.7" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round"/>
                            <circle cx="33.5" cy="13" r="3.2" fill="#f4b400"/>
                        </svg>
                    </span>
                    <span class="brand-text">
                        <strong>JANDIRA</strong>
                        <small>Esporte e Cidadania</small>
                    </span>
                </a>
                <p class="footer-description">
                    Uma plataforma para organizar o esporte de Jandira: atletas, eventos e documentos
                    reunidos em um ambiente simples, seguro e acessível.
                </p>
            </div>
            <div class="col-6 col-lg-3">
                <h4>Navegação</h4>
                <ul class="footer-links">
                    <li><a href="<?= e(app_url('')) ?>">Início</a></li>
                    <li><a href="<?= e(app_url('cadastro_atletas/atletas.php')) ?>">Atletas</a></li>
                    <li><a href="<?= e(app_url('cadastro_atletas/modalidades_esporte.php')) ?>">Modalidades de esporte</a></li>
                    <li><a href="<?= e(app_url('cadastro_atletas/adicionar_eventos.php')) ?>">Eventos</a></li>
                    <li><a href="<?= e(app_url('upload/index.php')) ?>">Arquivos</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-4">
                <h4>Institucional</h4>
                <ul class="footer-links">
                    <li><i class="bi bi-geo-alt" aria-hidden="true"></i> Jandira · São Paulo</li>
                    <li><i class="bi bi-people" aria-hidden="true"></i> Secretaria de Esporte e Lazer</li>
                    <li><a href="<?= e(app_url('criptografia/login.php')) ?>"><i class="bi bi-lock" aria-hidden="true"></i> Área restrita</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> Projeto Jandira. Todos os direitos reservados.</span>
            <span>Desenvolvido como projeto de portfólio.</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(app_url('assets/js/site.js')) ?>"></script>
</body>
</html>
<?php
}

/**
 * Cabeçalho padrão das páginas internas, com breadcrumb e ações.
 *
 * @param array<string, string|null> $breadcrumbs
 */
function render_page_header(
    string $eyebrow,
    string $title,
    string $description = '',
    array $breadcrumbs = [],
    string $actions = ''
): void {
    ?>
<section class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb" class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= e(app_url('')) ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Início</a>
                </li>
                <?php foreach ($breadcrumbs as $label => $url): ?>
                    <?php if ($url): ?>
                        <li class="breadcrumb-item"><a href="<?= e($url) ?>"><?= e($label) ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= e($label) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>

        <div class="page-header-row">
            <div class="page-header-copy">
                <span class="eyebrow"><?= e($eyebrow) ?></span>
                <h1><?= e($title) ?></h1>
                <?php if ($description !== ''): ?>
                    <p><?= e($description) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($actions !== ''): ?>
                <div class="page-header-actions"><?= $actions ?></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
}

function render_alert(string $type, string $message, string $icon = 'info-circle'): void
{
    $allowed = ['success', 'danger', 'warning', 'info'];
    $type = in_array($type, $allowed, true) ? $type : 'info';
    ?>
<div class="alert alert-<?= e($type) ?> alert-dismissible fade show app-alert" role="alert">
    <i class="bi bi-<?= e($icon) ?>-fill" aria-hidden="true"></i>
    <span><?= e($message) ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>
<?php
}
