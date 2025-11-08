<?php
session_start();
set_time_limit(0);
ini_set('memory_limit', '1024M');

/*if (isset($_SERVER['REMOTE_ADDR']) && (!isset($_SESSION['usuario_transportes']) || !$_SESSION['usuario_transportes'])) {
	http_response_code(403);
	exit(0);
}*/

$arquivo = isset($_REQUEST["arquivo"]) ? urldecode($_REQUEST["arquivo"]) : '';

$arquivo = '/var/www/avaliacao/uploads/'.$arquivo;

// Verifica se o arquivo existe
if (file_exists($arquivo)) {

	// Tenta detectar o tipo MIME
	$tipo = mime_content_type($arquivo); // Alternativa: use finfo abaixo

	// Força o download
	header('Content-Description: File Transfer');
	header('Content-Type: ' . $tipo);
	header('Content-Disposition: inline; filename="' . basename($arquivo) . '"');
	header('Content-Length: ' . filesize($arquivo));
	header('Pragma: public');
	header('Cache-Control: must-revalidate');

	// Limpa o buffer e envia
	flush();
	readfile($arquivo);
	exit;
} else {
	http_response_code(404);
	exit(0);
}
