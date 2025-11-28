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

		$grupos = Grupo::lista();

		if (!count($grupos)) {
			die('Sem grupo definido');
		}

		$this->view->grupos = $grupos;
		$this->view->id_grupo = isset($_REQUEST["id_grupo"]) ? (int)  $_REQUEST["id_grupo"] : 0;
		
	}

	public function grupodetalheAction()
	{

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$id_grupo = isset($_REQUEST["id_grupo"]) ? (int)  $_REQUEST["id_grupo"] : 0;

		if (Zend_Registry::get('permissao') > 1) {
			if (!Grupo::isGerente(Zend_Registry::get('id_usuario'), $id_grupo)) {
				die('Não permitido');
			}
		}

		if (!$id_grupo) {
			die('Grupo indefinido');
		}

		$this->view->grupo = Grupo::buscaId($id_grupo);

		
	}

	public function usuarioAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$usuarios = Usuario::lista('3');

		if (!count($usuarios)) {
			die('Sem usuário definido');
		}

		$this->view->usuarios = $usuarios;
		$this->view->id_usuario = isset($_REQUEST["id_usuario"]) ? (int)  $_REQUEST["id_usuario"] : 0;

	}

	public function usuariodetalheAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$id_usuario = isset($_REQUEST["id_usuario"]) ? (int)  $_REQUEST["id_usuario"] : 0;

		if (Zend_Registry::get('permissao') > 1) {
			if (!Grupo::isGerenteDe(Zend_Registry::get('id_usuario'), $id_usuario)) {
				die('Não permitido');
			}
		}

		if (!$id_usuario) {
			die('Usuário indefinido');
		}

		$this->view->usuario = Usuario::buscaId($id_usuario);

	}
}
