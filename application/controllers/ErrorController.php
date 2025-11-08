<?php

class ErrorController extends Zend_Controller_Action {

	public function errorAction(){

		if(!empty($_GET["sessionvar"])) $exception = unserialize($_SESSION[$_GET['sessionvar']]);
		else $exception = $this->_getParam('error_handler')->exception;
		
		if(!Zend_Registry::get('producao')){
					
			// Mostrar erro na tela
			$this->view->showMessages = true;
			$this->view->message = $exception->getMessage();
			$this->view->trace = $exception->getTraceAsString();

		}else{
			// Mostrar mensagem de envio de email:
			$this->view->showMessages = false;
			$this->view->supportMessage = "Um email foi enviado para o Suporte com os detalhes do erro.<br/>";
			
			// Descrevendo o erro
			$log =  date("[Y/M/d H:i:s]\n\n").
					"IP: ".$_SERVER['REMOTE_ADDR']."\n\n".
					"Usuario: ".$_SESSION['usuario']."\n\n".
			       	"Message:\n ".$exception->getMessage()."\n\n".
					"Stack Trace:\n".
					$exception->getTraceAsString()."\n\n\n\n\n\n";
					
			// Enviar por email
			/*
			$msg = "Foi encontrato um erro no FsCall, seguem os detalhes: \n\n";
			$mail = new Zend_Mail();
			$mail->setBodyText($msg.$log);
			$mail->setFrom(Zend_Registry::get("email_cliente"));
			$mail->addTo(Zend_Registry::get("email_suporte"));
			$mail->setSubject("FsCall - ".Zend_Registry::get('nome_cliente')." - Report de erro no sistema");
			$mail->send();
			*/
			
			// Logar o erro					
			file_put_contents(Zend_Registry::get('basedir')."application/logs/error.log",$log,FILE_APPEND);
		}
		
		// Tratando erros em requisições ajax
		if(Zend_Registry::get('ajax') && !Zend_Registry::get('producao')){
					
				$this->_helper->viewRenderer->setNoRender();
				
				$response = new stdClass();
				
				$response->error = true;
				$response->sessionVar = "error";
				$response->msg = $exception->getMessage();
				$response->trace = $exception->getTraceAsString();
			
				$_SESSION[$response->sessionVar] = serialize($exception);
				
				echo json_encode($response);
			
		}
		//mail('jeferson@eox.com.br',"email de teste","testando o postfix...");
		
	}	
	
}
?>