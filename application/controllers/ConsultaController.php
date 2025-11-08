<?php
//if (Zend_Registry::get('permissao') != 1) exit();

class ConsultaController extends Zend_Controller_Action
{

	public function indexAction()
	{
		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');

		$inicio = !empty($_REQUEST['inicio']) ? $_REQUEST['inicio'] : 'first day of this month';
		$fim = !empty($_REQUEST['fim']) ? $_REQUEST['fim'] : 'last day of this month';

		$dtInicio = new DateTime($inicio);
		$inicio = $dtInicio->format('Y-m-d');

		$dtFim = new DateTime($fim);
		$fim = $dtFim->format('Y-m-d');

		$this->view->inicio = $inicio;
		$this->view->fim = $fim;

		$id_empresa = Zend_Registry::get('permissao') > 1 ? Zend_Registry::get('id_empresa') : null;

		$this->view->registros = Formulario::consulta($inicio, $fim, $id_empresa);
	}
}
