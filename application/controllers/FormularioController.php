<?php

//if (Zend_Registry::get('permissao') > 2) exit();

class FormularioController extends Zend_Controller_Action
{

	public function indexAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$this->view->registros = Formulario::lista();
	}

	public function cadastroAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		$id_formulario = (int) isset($_REQUEST["id_formulario"]) ? $_REQUEST["id_formulario"] : 0;

		$this->view->registro = $id_formulario ? Formulario::buscaId($id_formulario) : false;

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');
	}

	// a remoção não é deletado, é apenas desativado o registro
	public function removerAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_formulario = (int) $_REQUEST["id"];

		$result = Formulario::delete($id_formulario);

		if (!$result) echo Formulario::$erro;
	}

	public function salvarAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_formulario = (int) isset($_REQUEST["id_formulario"]) ? $_REQUEST["id_formulario"] : 0;

		$nome = !empty($_REQUEST["nome"]) ? $_REQUEST["nome"] : null;

		$campos  = [];

		$campos['id_usuario'] = !empty($_REQUEST["id_usuario"]) ? $_REQUEST["id_usuario"] : Zend_Registry::get('id_usuario');

		if (!$id_formulario) {
			$result = Formulario::insert($nome, $campos);
		} else {
			$result = Formulario::update($id_formulario, $nome, $campos);
		}
		if (!$result) echo Formulario::$erro;
	}
}
