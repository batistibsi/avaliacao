<?php

class IndexController extends Zend_Controller_Action
{

	public function indexAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');		

		$this->view->inicio = date('Y-01-01');
		$this->view->fim = date('Y-m-d');

		$this->view->empresa = [];
	}

	public function logoutAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		unset($_SESSION['usuario_avaliacao']);
		unset($_SESSION['id_usuario_avaliacao']);
		unset($_SESSION['permissao_avaliacao']);
		header("location: " . Zend_Registry::get('url') . "/");
	}

	public function novasenhaAction()
	{

		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');
	}

	public function salvarnovasenhaAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$idUsuario = (int) isset($_REQUEST["id_usuario"]) ? $_REQUEST["id_usuario"] : 0;

		$senhaAtual = !empty($_REQUEST["senhaAtual"]) ? md5($_REQUEST["senhaAtual"]) : null;
		$novaSenha = !empty($_REQUEST["novaSenha"]) ? md5($_REQUEST["novaSenha"]) : null;

		$result = Usuario::alterarSenha($idUsuario, $senhaAtual, $novaSenha);

		if (!$result) echo Usuario::$erro;
	}
}
