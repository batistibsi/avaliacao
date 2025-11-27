<?php
if (Zend_Registry::get('permissao') > 2) exit();

class AvaliacaoController extends Zend_Controller_Action
{

	public function indexAction()
	{
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$id_grupo = Zend_Registry::get('permissao') > 1 ? Grupo::gerente(Zend_Registry::get('id_usuario')) : 0;

		if (Zend_Registry::get('permissao') > 1 && !$id_grupo) {
			die('Gerente sem grupo definido');
		}

		$this->view->grupos = $id_grupo ? false : Grupo::lista();

		$this->view->id_grupo = $id_grupo;
	}

	public function membrosAction()
	{
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		if (Zend_Registry::get('permissao') > 1) {
			$id_grupo = Grupo::gerente(Zend_Registry::get('id_usuario'));
		} else {
			$id_grupo = isset($_REQUEST["id_grupo"]) ? (int)  $_REQUEST["id_grupo"] : 0;
		}

		if (!$id_grupo) {
			die('Grupo indefinido');
		}

		$this->view->membros = Grupo::membros($id_grupo);
	}

	public function formularioAction()
	{
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$id_usuario = isset($_REQUEST["id_usuario"]) ? (int)  $_REQUEST["id_usuario"] : 0;

		if (!$id_usuario) {
			die('Usuário indefinido');
		}

		$usuario = Usuario::buscaId($id_usuario);

		if (!$usuario) {
			die('Usuário não encontrado');
		}

		$this->view->usuario = $usuario;

		$grupo = Grupo::membro($id_usuario);

		if (!$grupo) {
			die('Grupo não encontrado');
		}

		$this->view->grupo = $grupo;

		$formulario = Formulario::buscaId($grupo['id_formulario']);

		if (!$formulario) {
			die('Formulário não encontrado');
		}

		$this->view->formulario = $formulario;

		$this->view->blocos = Formulario::buscarBlocos($grupo['id_formulario']);
	}

	public function historicoAction()
	{
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');
	}

	public function salvarAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$campos = [];

		$campos['id_grupo'] = !empty($_REQUEST["id_grupo"]) ? (int)  $_REQUEST["id_grupo"] : null;
		$campos['id_formulario'] = !empty($_REQUEST["id_formulario"]) ? (int)  $_REQUEST["id_formulario"] : null;
		$campos['id_usuario'] = !empty($_REQUEST["id_usuario"]) ? (int)  $_REQUEST["id_usuario"] : null;
		$campos['id_avaliador'] = Zend_Registry::get('id_usuario');
		$campos['data_envio'] = date('Y-m-d H:i:s');

		$respostas = !empty($_REQUEST['itens'])?$_REQUEST['itens']:[];

		$result = Envio::insert($campos, $respostas);

		if (!$result) {
			echo Envio::$erro;
		}
	}
}
