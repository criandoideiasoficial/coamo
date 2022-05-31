<?php
use PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 2;
$mail->Host = 'smtp.seusite.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = 'test@seusite.com';
$mail->Password = 'SUA SENHA AQUI';
$mail->setFrom('test@seusite.com', 'Seu nome');
$mail->addReplyTo('test@seusite.com', 'Seu nome');
$mail->addAddress('exemplo@email.com', 'Nome do recebedor');
$mail->Subject = 'Testing PHPMailer';
$mail->msgHTML(file_get_contents('message.html'), __DIR__);
$mail->Body = 'Este é um corpo de mensagem de texto simples';
//$mail->addAttachment('test.txt');
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Email Enviado.';
}
?>