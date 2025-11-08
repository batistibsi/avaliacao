<?php

class TarefaController extends Zend_Controller_Action
{

	public function cadastroAction()
	{

		$this->view->header = "header.phtml";
		$this->view->footer = "footer.phtml";

		// Passando o usuário logado para a view
		$this->view->usuario = Zend_Registry::get('usuario');
		$this->view->idUsuario = Zend_Registry::get('id_usuario');
		$this->view->permissao = Zend_Registry::get('permissao');
		$this->view->id_empresa = Zend_Registry::get('id_empresa');

		$id_tarefa = isset($_REQUEST["id_tarefa"]) ? (int) $_REQUEST["id_tarefa"] : 0;

		$tarefa = Tarefa::buscaId($id_tarefa);

		if (!$tarefa) {
			die(Tarefa::$erro);
		}

		$this->view->registro = $tarefa;

		$tiposAcessiveis = Tarefa::listaTipos(true);

		$this->view->editavel = (isset($tiposAcessiveis[$tarefa['id_tipo_tarefa']]));

		$this->view->envio = Formulario::buscarEnvio($tarefa['id_envio']);

		$this->view->proximos_passos = Zend_Registry::get('permissao') == 1 ? Tarefa::listaTipos(false) : false;

		$this->view->historico = Tarefa::historico($tarefa['id_envio']);
	}


	public function salvarAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$id_tarefa = isset($_REQUEST["id_tarefa"]) ? (int) $_REQUEST["id_tarefa"] : 0;
		if (!$id_tarefa) {
			die('Tarefa não informada');
		}
		$id_proximo_passo = !empty($_REQUEST["id_proximo_passo"]) ? (int)$_REQUEST["id_proximo_passo"] : null;

		$campos = [];

		$campos['comentario'] = !empty($_REQUEST["comentario"]) ? $_REQUEST["comentario"] : null;
		$campos['comentario_interno'] = !empty($_REQUEST["comentario_interno"]) ? $_REQUEST["comentario_interno"] : null;

		$arquivos = Tarefa::uploadArquivos();
		$campos['arquivos'] = count($arquivos) ? implode('|:|', $arquivos) : null;

		$result = Tarefa::salvar($id_tarefa, $campos, $id_proximo_passo);

		if (!$result) die(Tarefa::$erro);
	}
}
