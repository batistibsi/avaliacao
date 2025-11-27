<?php

if (Zend_Registry::get('permissao') != 1) exit();

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
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		$id_formulario = (int) isset($_REQUEST["id_formulario"]) ? $_REQUEST["id_formulario"] : 0;

		$this->view->registro = $id_formulario ? Formulario::buscaId($id_formulario) : false;

		if($id_formulario){
			$this->view->blocos = Formulario::buscarBlocos($id_formulario);
		}
	}

	// a remoção não é deletado, é apenas desativado o registro
	public function removerAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_formulario = (int) $_REQUEST["id"];

		$result = Formulario::desativar($id_formulario);

		if (!$result) echo Formulario::$erro;
	}

	public function salvarAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_formulario = (int) isset($_REQUEST["id_formulario"]) ? $_REQUEST["id_formulario"] : 0;

		$nome = !empty($_REQUEST["nome"]) ? $_REQUEST["nome"] : null;

		$campos  = [];

		$estrutura = json_decode($_REQUEST["estrutura"]);

		if (!$id_formulario) {
			$result = Formulario::insert($nome, $campos, $estrutura);
		} else {
			$result = Formulario::update($id_formulario, $nome, $campos, $estrutura);
		}
		if (!$result) echo Formulario::$erro;
	}
}
