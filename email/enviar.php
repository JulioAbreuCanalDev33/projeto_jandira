<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
exigir_login();
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

require_csrf();

$configurado = env('MAIL_HOST') !== null
    && env('MAIL_USERNAME') !== null
    && env('MAIL_PASSWORD') !== null;

$destinatarioPadrao = (string) env('MAIL_TO_ADDRESS', (string) env('MAIL_USERNAME', ''));
$destinatario = $destinatarioPadrao;
$erro = null;
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destinatario = trim((string) ($_POST['destinatario'] ?? ''));

    if (!$configurado) {
        $erro = 'O servidor de e-mail ainda não foi configurado. Preencha as variáveis MAIL_* no arquivo .env.';
    } elseif (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um endereço de e-mail válido para o envio de teste.';
    } else {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Timeout = 20;
            $mail->Host = (string) env('MAIL_HOST');
            $mail->Port = (int) env('MAIL_PORT', '587');
            $mail->SMTPSecure = (string) env('MAIL_ENCRYPTION', 'tls');
            $mail->SMTPAuth = true;
            $mail->Username = (string) env('MAIL_USERNAME');
            $mail->Password = (string) env('MAIL_PASSWORD');
            $mail->CharSet = 'UTF-8';
            $mail->setLanguage('pt_br', __DIR__ . '/vendor/phpmailer/phpmailer/language/');

            $remetente = (string) env('MAIL_FROM_ADDRESS', (string) env('MAIL_USERNAME'));
            $mail->setFrom($remetente, (string) env('MAIL_FROM_NAME', 'Projeto Jandira'));
            $mail->addAddress($destinatario, (string) env('MAIL_TO_NAME', 'Equipe Projeto Jandira'));

            $mail->isHTML(true);
            $mail->Subject = 'Teste de envio · Projeto Jandira';
            $mail->Body = '
                <div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;padding:32px;background:#f4f7fb;border-radius:16px;color:#12233d">
                    <div style="background:#0b4ea2;border-radius:12px;padding:20px 24px;color:#ffffff">
                        <h1 style="margin:0;font-size:20px">Projeto Jandira</h1>
                        <p style="margin:6px 0 0;font-size:13px;opacity:.85">Esporte e Cidadania</p>
                    </div>
                    <div style="background:#ffffff;border-radius:12px;padding:24px;margin-top:16px">
                        <h2 style="margin:0 0 12px;font-size:18px;color:#0b4ea2">Canal de e-mail operacional</h2>
                        <p style="margin:0 0 10px;font-size:14px;line-height:1.6">
                            Este é um envio de teste disparado pelo painel do Projeto Jandira.
                        </p>
                        <p style="margin:0;font-size:14px;line-height:1.6">
                            Se você recebeu esta mensagem, as credenciais SMTP configuradas em ambiente estão funcionando corretamente.
                        </p>
                    </div>
                    <p style="margin:16px 0 0;font-size:12px;color:#5d6f88;text-align:center">
                        Projeto Jandira · Plataforma de gestão esportiva
                    </p>
                </div>';
            $mail->AltBody = "Canal de e-mail operacional\n\nEste é um envio de teste do Projeto Jandira. As credenciais SMTP configuradas em ambiente estão funcionando.";

            $mail->send();
            $sucesso = true;
        } catch (PHPMailerException $exception) {
            $erro = 'Não foi possível enviar o e-mail de teste. Verifique as credenciais SMTP e tente novamente.';

            if (env('APP_ENV', 'production') === 'local') {
                $erro .= ' Detalhe técnico: ' . $exception->getMessage();
            }
        }
    }
}

render_layout_start('E-mail', '', 'Teste seguro de envio de e-mail com credenciais SMTP em variáveis de ambiente.');
?>

<?php
render_page_header(
    'Integrações',
    'Teste de e-mail',
    'Valide a configuração SMTP do projeto sem expor credenciais no código-fonte.',
    ['E-mail' => null]
);
?>

<section class="page-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <?php if ($sucesso): ?>
                    <div class="mb-4">
                        <?php render_alert('success', 'E-mail de teste enviado com sucesso para ' . $destinatario . '.', 'check-circle'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($erro !== null): ?>
                    <div class="mb-4">
                        <?php render_alert('danger', $erro, 'exclamation-triangle'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!$configurado): ?>
                    <div class="mb-4">
                        <?php render_alert(
                            'warning',
                            'As variáveis MAIL_HOST, MAIL_USERNAME e MAIL_PASSWORD não estão definidas. Configure o arquivo .env antes de testar.',
                            'gear'
                        ); ?>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <div class="form-card-header">
                        <h2><i class="bi bi-envelope-paper me-2 text-primary" aria-hidden="true"></i> Envio de teste</h2>
                        <p>O disparo só acontece após o envio deste formulário.</p>
                    </div>

                    <div class="form-card-body">
                        <form method="post" action="">
                            <?= csrf_field() ?>

                            <div class="mb-4">
                                <label for="destinatario" class="form-label">Destinatário <span class="required">*</span></label>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="destinatario"
                                    name="destinatario"
                                    value="<?= e($destinatario) ?>"
                                    placeholder="equipe@exemplo.com"
                                    autocomplete="email"
                                    required
                                >
                                <div class="form-text">Use um endereço sob seu controle para validar a entrega.</div>
                            </div>

                            <div class="form-actions mt-0 pt-0 border-0">
                                <button type="submit" class="btn btn-brand"<?= $configurado ? '' : ' disabled' ?>>
                                    <i class="bi bi-send" aria-hidden="true"></i>
                                    Enviar e-mail de teste
                                </button>
                                <a href="<?= e(app_url('')) ?>" class="btn btn-soft">
                                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                    Voltar ao início
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="surface-card-footer">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                        As credenciais são carregadas de variáveis de ambiente e nunca aparecem na interface.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
render_layout_end();
