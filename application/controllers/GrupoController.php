<?php

//if (Zend_Registry::get('permissao') > 2) exit();

class GrupoController extends Zend_Controller_Action
{

	public function indexAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$this->view->registros = Grupo::lista();
	}

	public function cadastroAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		$id_grupo = (int) isset($_REQUEST["id_grupo"]) ? $_REQUEST["id_grupo"] : 0;

		$this->view->registro = $id_grupo ? Grupo::buscaId($id_grupo) : false;

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');
	}

	// a remoção não é deletado, é apenas desativado o registro
	public function removerAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_grupo = (int) $_REQUEST["id"];

		$result = Grupo::delete($id_grupo);

		if (!$result) echo Grupo::$erro;
	}

	public function salvarAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_grupo = (int) isset($_REQUEST["id_grupo"]) ? $_REQUEST["id_grupo"] : 0;

		$nome = !empty($_REQUEST["nome"]) ? $_REQUEST["nome"] : null;

		$campos  = [];

		$campos['id_usuario'] = !empty($_REQUEST["id_usuario"]) ? $_REQUEST["id_usuario"] : Zend_Registry::get('id_usuario');

		if (!$id_grupo) {
			$result = Grupo::insert($nome, $campos);
		} else {
			$result = Grupo::update($id_grupo, $nome, $campos);
		}
		if (!$result) echo Grupo::$erro;
	}
}
