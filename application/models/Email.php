<?php
//require '/var/www/avaliacao/vendor/autoload.php';

//use PHPMailer\PHPMailer\PHPMailer;

class Email
{

	public static $erro;

	public static function enviar($destino, $titulo, $msg)
	{
		
		return true;

		$config = Config::busca();

		$mail = new PHPMailer(true);
		$mail->CharSet   = 'UTF-8';            // charset do corpo/headers
		$mail->isSMTP();
		$mail->Host       = $config['smtp'];   // troque para seu SMTP
		$mail->SMTPAuth   = true;
		$mail->Username   = $config['email'];
		$mail->Password   = $config['senha']; // app password / senha SMTP
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port       = 587;

		$mail->setFrom($config['email'], 'avaliacao');
		$mail->addAddress($destino);
		$mail->isHTML(true);
		$mail->Subject = $titulo;
		$mail->Body    = $msg;
		$mail->send();

		return true;
	}
}
