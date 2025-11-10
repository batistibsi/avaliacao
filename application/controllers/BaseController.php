<?php

//if (Zend_Registry::get('permissao') > 2) exit();

class BaseController extends Zend_Controller_Action
{

	public function indexAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$this->view->registros = Base::lista();
	}

	public function cadastroAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		$id_base = (int) isset($_REQUEST["id_base"]) ? $_REQUEST["id_base"] : 0;

		$this->view->registro = $id_base ? Base::buscaId($id_base) : false;

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');
	}

	// a remoção não é deletado, é apenas desativado o registro
	public function removerAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_base = (int) $_REQUEST["id"];

		$result = Base::delete($id_base);

		if (!$result) echo Base::$erro;
	}

	public function salvarAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_base = (int) isset($_REQUEST["id_base"]) ? $_REQUEST["id_base"] : 0;

		$nome = !empty($_REQUEST["nome"]) ? $_REQUEST["nome"] : null;

		$campos  = [];

		$campos['id_usuario'] = !empty($_REQUEST["id_usuario"]) ? $_REQUEST["id_usuario"] : Zend_Registry::get('id_usuario');

		if (!$id_base) {
			$result = Base::insert($nome, $campos);
		} else {
			$result = Base::update($id_base, $nome, $campos);
		}
		if (!$result) echo Base::$erro;
	}
}
