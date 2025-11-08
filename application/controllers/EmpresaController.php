<?php
//if (Zend_Registry::get('permissao') != 1) exit();

class EmpresaController extends Zend_Controller_Action
{

	public function indexAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$this->view->empresa = Empresa::buscaId(Zend_Registry::get('id_empresa'));
	}

	public function salvarAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$campos['titulo']= !empty($_REQUEST["titulo"]) ? $_REQUEST["titulo"] : null;
		$campos['logo'] = !empty($_REQUEST["logo"]) ? $_REQUEST["logo"] : '';
		$campos['cor'] = !empty($_REQUEST["cor"]) ? $_REQUEST["cor"] : '';

		Empresa::salvar(Zend_Registry::get('id_empresa'),$campos);
	}
}
