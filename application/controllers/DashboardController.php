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

		$id_grupo = isset($_REQUEST["id_grupo"]) ? $_REQUEST["id_grupo"] : null;

		$inicio = isset($_REQUEST['inicio']) ? $_REQUEST['inicio'] : false;
		$fim = isset($_REQUEST['fim']) ? $_REQUEST['fim'] : false;

		if (!$inicio || !$fim) {
			die('Período inválido!');
		}

		if (Zend_Registry::get('permissao') > 1) {
			if (!Grupo::isGerente(Zend_Registry::get('id_usuario'), $id_grupo)) {
				die('Não permitido');
			}
		}

		if (!empty($id_grupo)) {
			$id_grupo = explode(",", $id_grupo);
			$id_grupo = array_map('intval', $id_grupo);
			$id_grupo = implode(",", $id_grupo);
		}

		$this->view->estatistica = Grupo::estatistica($inicio,$fim,$id_grupo);
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

		$inicio = isset($_REQUEST['inicio']) ? $_REQUEST['inicio'] : false;
		$fim = isset($_REQUEST['fim']) ? $_REQUEST['fim'] : false;

		if (!$inicio || !$fim) {
			die('Período inválido!');
		}

		if (Zend_Registry::get('permissao') > 1) {
			if (!Grupo::isGerenteDe(Zend_Registry::get('id_usuario'), $id_usuario)) {
				die('Não permitido');
			}
		}

		if (!$id_usuario) {
			die('Usuário indefinido');
		}

		$usuario = Usuario::buscaId($id_usuario);

		if (!$usuario) {
			die(Usuario::$erro);
		}

		$this->view->usuario = $usuario;

		$this->view->envios = Envio::lista($inicio, $fim, $usuario['id_grupo'], $id_usuario);

		$this->view->estatistica = Usuario::estatistica($inicio, $fim, $usuario['id_grupo'], $id_usuario);

		//echo '<pre>';print_r($this->view->estatistica);die();
	}
}
