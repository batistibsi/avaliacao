<?php
if (empty($_REQUEST["h-captcha-response"])) {
	die("Erro: hCaptcha inválido!");
}

$captchaResponse = $_REQUEST["h-captcha-response"]; // Resposta do usuário
$secretKey = Zend_Registry::get('capcha_key'); // Substitua pela sua chave secreta
$remoteIp = $_SERVER["REMOTE_ADDR"]; // Captura o IP do cliente


// Configurar os dados para enviar à API
$data = [
	"secret" => $secretKey,
	"response" => $captchaResponse,
	"remoteip" => $remoteIp
];

// Iniciar cURL
$ch = curl_init("https://api.hcaptcha.com/siteverify");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Executar a requisição e obter resposta
$response = curl_exec($ch);
curl_close($ch);

// Converter JSON para array PHP
$responseData = json_decode($response, true);

// Verificar se o hCaptcha foi validado
if (empty($responseData["success"]) || !$responseData["success"]) {
	die("Erro: hCaptcha inválido!");
}
