<?php

class AvaliacaoController extends Zend_Controller_Action
{

	public function indexAction()
	{
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$id_grupo = Zend_Registry::get('permissao') > 1 ? Grupo::gerente(Zend_Registry::get('id_usuario')) : 0;

		if(Zend_Registry::get('permissao') > 1 && !$id_grupo){
			die('Gerente sem grupo definido');
		}

		$this->view->grupos = $id_grupo ? false : Grupo::lista();

		$this->view->id_grupo = $id_grupo;
	}

	public function membrosAction() {
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		if(Zend_Registry::get('permissao') > 1){
			$id_grupo = Grupo::gerente(Zend_Registry::get('id_usuario'));
		}else{
			$id_grupo = isset($_REQUEST["id_grupo"]) ? (int)  $_REQUEST["id_grupo"] : 0;
		}

		if(!$id_grupo){
			die('Grupo indefinido');
		}

		$this->view->membros = Grupo::membros($id_grupo);

	}

	public function formularioAction() {
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		

	}

	public function historicoAction() {
		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

	}
}
