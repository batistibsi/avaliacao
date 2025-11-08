<?php
session_start();
ini_set('display_errors', 0);

include_once "./capcha.php";
include_once "./zend.php";

$erro = true;
$message = "Sua nova senha foi enviada no email solicitado.";

if (!empty($_POST['login'])) {

	$email = str_replace("'", "", $_POST['login']);
	$registro = Usuario::buscaEmail($email);

	if ($registro) {
		$senha = substr(md5('bravocrm' . $email . '2025'), 0, 6);
		if (Usuario::alterarSenha($registro['id_usuario'], $registro['senha'], md5($senha))) {
			Usuario::emailSenha($email, $senha);
		} else {
			$erro = true;
			$message = "Erro ao mudar a senha!";
		}
	} else {
		$erro = true;
		$message = "Email não encontrado!";
	}
}

if ($erro) {
	header('Location: ../../logon.php?msg=' . $message);
} else {
	header('Location: ../../index');
}
