<?php

// topic: send email using php
$to = "canaldev33@gmail.com";
$subject = "Test mail";
$message = "Hello! This is a simple email message.";
$from = "juliocasp38@gmail.com";
$headers = "From:" . $from;

function sendEmail($to, $subject, $message, $from){
    $headers = "From:" . $from;
    mail($to,$subject,$message,$headers);
}

echo "Funcionou. <br> Mail Sent.";


?>