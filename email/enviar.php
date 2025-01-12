<?php

    use PHPMailer\PHPMailer\PHPMailer;

    require 'vendor/autoload.php';

    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->SMTPDebug = 2;
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = 'canaldev33@gmail.com';
    $mail->Password = 'blmihvxoagovcikq ';

    $mail->setLanguage('pt');
    $mail->setFrom('canaldev33@gmail.com', 'teste email');
    $mail->addReplyTo('canaldev33@gmail.com', 'Reply');
    $mail->addAddress('canaldev33@gmail.com', 'teste 2');
    $mail->isHTML(true);
    
    $mail->Subject = 'E-mail de teste';
    $mail->Body = "<h1>EMail enviado com sucesso!</h1><p>Parabéns Julio!! Deu tudo certo.</p>";
    $mail->CharSet = 'UTF-8';

    if ($mail->send())
        echo "E-mail enviado com sucesso!!";
    else
        echo "Falha ao enviar e-mail.";

    ?>