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

		$this->view->responsaveis = Usuario::lista('1,2');
		$this->view->usuarios = Usuario::lista('3');

		$this->view->formularios = Formulario::lista();

		$this->view->membros = ($id_grupo) ? Grupo::membros($id_grupo) : [];
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

		$campos['id_usuario'] = !empty($_REQUEST["id_usuario"]) ? $_REQUEST["id_usuario"] : null;
		$campos['id_formulario'] = !empty($_REQUEST["id_formulario"]) ? $_REQUEST["id_formulario"] : null;

		$membros = [];

		foreach ($_REQUEST as $key => $value) {
			if (preg_match('/^usuario_([0-9]{1,})$/', $key, $matches)) {
				if (!empty($value)) $membros[(int)$matches[1]] = (int)$matches[1];
			}
		}

		if (!$id_grupo) {
			$result = Grupo::insert($nome, $campos, $membros);
		} else {
			$result = Grupo::update($id_grupo, $nome, $campos, $membros);
		}
		if (!$result) echo Grupo::$erro;
	}
}
