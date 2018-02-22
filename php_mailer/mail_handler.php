<?php
require_once('email_config.php');
require_once('phpmailer/PHPMailer/PHPMailerAutoload.php');

$mail = new PHPMailer();

// Configure SMTP
$mail->IsSMTP();
$mail->SMTPDebug  = 0;          // verbose information
$mail->SMTPAuth = true;
$mail->SMTPSecure = "tls";
$mail->Host = "smtp.gmail.com";
$mail->Port = 587;
$mail->Encoding = '7bit';

// Auth
$mail->Username   = EMAIL_USER;
$mail->Password   = EMAIL_PASS;

// Check
$mail->Subject = "SMS activation pin";
$mail->Body = $rand;
$output['Im alive'] = true;


$mail->AddAddress( "7149483092@tmomail.net" );
//var_dump( $mail->send() );



?>
