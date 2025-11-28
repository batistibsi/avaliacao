<?php

if (Zend_Registry::get('permissao') > 2) exit();

class DashboardController extends Zend_Controller_Action
{

	public function indexAction()
	{
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

	}


	public function grupoAction()
	{

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		
	}

	public function grupodetalheAction()
	{

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		
	}

	public function usuarioAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');


	}

	public function usuariodetalheAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');


	}
}
