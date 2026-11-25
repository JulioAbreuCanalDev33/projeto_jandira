<?php
require_once __DIR__ . '/includes/layout.php';

$visitante = !is_logged_in();
$linkAtletas = $visitante ? login_url(app_url('cadastro_atletas/atletas.php')) : app_url('cadastro_atletas/atletas.php');
$linkCadastrar = $visitante ? login_url(app_url('cadastro_atletas/cadastrar_atletas.php')) : app_url('cadastro_atletas/cadastrar_atletas.php');
$linkEventos = $visitante ? login_url(app_url('cadastro_atletas/adicionar_eventos.php')) : app_url('cadastro_atletas/adicionar_eventos.php');
$linkArquivos = $visitante ? login_url(app_url('upload/index.php')) : app_url('upload/index.php');

render_layout_start('Início', 'inicio', 'Plataforma de gestão do esporte de Jandira: atletas, eventos e documentos em um só lugar.');
?>

<section class="home-hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-copy">
                <span class="hero-eyebrow">
                    <span class="dot" aria-hidden="true"></span>
                    Esporte que transforma
                </span>

                <h1 class="hero-title">
                    Jandira em<br>
                    <span class="highlight">movimento</span>
                </h1>

                <p class="hero-lead">
                    Um só lugar para cuidar de quem faz o esporte acontecer. Organize atletas,
                    acompanhe eventos e mantenha as informações da equipe sempre por perto.
                </p>

                <div class="hero-actions">
                    <a href="<?= e($linkAtletas) ?>" class="btn btn-brand">
                        Explorar atletas
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                    <a href="<?= e($linkCadastrar) ?>" class="btn btn-outline-brand">
                        <i class="bi bi-person-plus" aria-hidden="true"></i>
                        Cadastrar atleta
                    </a>
                </div>

                <div class="hero-points">
                    <span><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Cadastro unificado</span>
                    <span><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Gestão de eventos</span>
                    <span><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Documentos organizados</span>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-photo">
                    <img src="<?= e(app_url('assets/img/hero-run.jpg')) ?>" alt="Atletas correndo ao amanhecer em uma pista de atletismo">
                    <div class="hero-photo-tag">
                        <i class="bi bi-sunrise-fill" aria-hidden="true"></i>
                        Jandira · Esporte e Cidadania
                    </div>
                </div>

                <div class="hero-float-card">
                    <span class="hero-float-icon">
                        <i class="bi bi-trophy-fill" aria-hidden="true"></i>
                    </span>
                    <div>
                        <strong>Gestão esportiva completa</strong>
                        <small>Atletas, eventos e arquivos em um único ambiente.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-soft" aria-labelledby="acesso-rapido">
    <div class="container">
        <div class="section-heading">
            <span class="section-eyebrow">Acesso rápido</span>
            <h2 id="acesso-rapido">Tudo o que a equipe precisa, em um só lugar</h2>
            <p>Navegue pelos módulos do sistema e encontre rapidamente o que precisa para acompanhar o dia a dia do esporte.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6 col-xl-3">
                <a class="access-card" href="<?= e($linkAtletas) ?>">
                    <div class="access-card-top">
                        <span class="icon-tile"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                    </div>
                    <h3>Atletas</h3>
                    <p>Consulte a equipe, atualize dados e mantenha os cadastros sempre completos.</p>
                    <span class="access-link">Ver atletas <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <a class="access-card" href="<?= e($linkEventos) ?>">
                    <div class="access-card-top">
                        <span class="icon-tile accent"><i class="bi bi-calendar-event-fill" aria-hidden="true"></i></span>
                    </div>
                    <h3>Eventos</h3>
                    <p>Organize competições, encontros e atividades esportivas do município.</p>
                    <span class="access-link">Ver eventos <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <a class="access-card" href="<?= e($linkArquivos) ?>">
                    <div class="access-card-top">
                        <span class="icon-tile green"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
                    </div>
                    <h3>Arquivos</h3>
                    <p>Guarde fotos, documentos e materiais de apoio com segurança e organização.</p>
                    <span class="access-link">Abrir arquivos <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <a class="access-card" href="<?= e(app_url('criptografia/login.php')) ?>">
                    <div class="access-card-top">
                        <span class="icon-tile"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></span>
                    </div>
                    <h3>Área restrita</h3>
                    <p>Acesso exclusivo da equipe gestora para administrar o sistema com segurança.</p>
                    <span class="access-link">Entrar no painel <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="gestao-completa">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-eyebrow">Feito para o dia a dia</span>
            <h2 id="gestao-completa">Uma gestão mais simples, próxima e eficiente</h2>
            <p>Menos planilhas espalhadas e mais tempo para o que realmente importa: o esporte e as pessoas.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="feature-card">
                    <div class="feature-number">01 · Organização</div>
                    <h3>Informações centralizadas</h3>
                    <p>Dados de atletas, eventos e documentos reunidos em um ambiente único e fácil de consultar.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="feature-card">
                    <div class="feature-number">02 · Agilidade</div>
                    <h3>Processos mais rápidos</h3>
                    <p>Cadastros, atualizações e consultas pensados para reduzir o trabalho manual da equipe.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="feature-card">
                    <div class="feature-number">03 · Confiança</div>
                    <h3>Acesso seguro</h3>
                    <p>Área administrativa protegida para que apenas a equipe responsável gerencie as informações.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <div class="cta-band">
            <div class="cta-band-content">
                <div>
                    <h2>O próximo grande nome do esporte pode estar aqui.</h2>
                    <p>Cadastre um novo atleta e ajude a construir uma história de conquistas para Jandira.</p>
                </div>
                <a href="<?= e($linkCadastrar) ?>" class="btn btn-accent btn-lg">
                    <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                    Cadastrar atleta
                </a>
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
