<?php

if (Zend_Registry::get('permissao') != 1) exit();

class UsuarioController extends Zend_Controller_Action
{

	public function indexAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->id_usuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$this->view->hideDate = true;

		$this->view->registros = Usuario::lista();
	}

	public function cadastroAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->id_usuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$id_usuario = (int) isset($_REQUEST["id_usuario"]) ? $_REQUEST["id_usuario"] : 0;

		$registro = $id_usuario ? Usuario::buscaId($id_usuario) : false;

		$this->view->registro = $registro;

		$this->view->comboPerfil = Usuario::comboPerfil();
	}

	// a remoção não é deletado, é apenas desativado o registro
	public function removerAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_usuario = (int) $_REQUEST["id"];

		$result = Usuario::desativar($id_usuario);

		if (!$result) echo Usuario::$erro;
	}

	public function loginAction()
	{

		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->id_usuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$this->view->registros = Usuario::logins();
	}

	public function salvarAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_usuario = isset($_REQUEST["id_usuario"]) ? (int) $_REQUEST["id_usuario"] : 0;
		$id_empresa = isset($_REQUEST["id_empresa"]) ? (int) $_REQUEST["id_empresa"] : 0;

		$email = !empty($_REQUEST["email"]) ? $_REQUEST["email"] : null;
		$nome = !empty($_REQUEST["nome"]) ? $_REQUEST["nome"] : null;
		$senha = !empty($_REQUEST["senha"]) ? md5($_REQUEST["senha"]) : null;
		$confirmSenha = !empty($_REQUEST["confirm_senha"]) ? md5($_REQUEST["confirm_senha"]) : null;

		$idPerfil = (int) !empty($_REQUEST["id_perfil"]) ? $_REQUEST["id_perfil"] : null;

		$ativo = true;

		if (!$id_usuario) {
			$result = Usuario::insert($email, $nome, $idPerfil, $ativo, $senha, $confirmSenha, $id_empresa);
		} else {
			$result = Usuario::update($email, $nome, $idPerfil, $ativo, $senha, $id_usuario, $confirmSenha);
		}

		if (!$result) echo Usuario::$erro;
	}
}
