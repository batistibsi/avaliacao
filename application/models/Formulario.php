<?php
class Formulario
{

	public static $erro;

	public static $tamanho_maximo_resposta = 2000;

	public function __construct() {}


	public static function render_card($nomeOuPath)
	{
		$extension = Util::ext($nomeOuPath);
		$url = '../../../../download.php?arquivo=' . $nomeOuPath;
		$title = $nomeOuPath; // título exibido; não encode aqui para usar no atributo com Util::h()

		// Wrapper do card (Bootstrap 4)
		$open  = '<div class="col-12 col-sm-6 col-md-6 col-lg-6 mb-3"><div class="card shadow-sm h-100"><div class="card-body d-flex flex-column">';
		$close = '</div></div></div>';

		// Cabeçalho
		$head = '<h6 class="card-title text-truncate" title="' . Util::h($title) . '">' . Util::h($title) . '</h6>';

		// Caixa do preview (mantém proporção)
		$boxOpen = '<div class="mb-2" style="aspect-ratio:4/3;overflow:hidden;border:1px solid #eee;border-radius:.5rem;display:flex;align-items:center;justify-content:center;">';
		$boxClose = '</div>';

		// Botão simples (opcional)
		$btn = '<a class="btn btn-outline-primary btn-sm mt-auto align-self-end" href="' . Util::h($url) . '" target="_blank" rel="noopener">ABRIR</a>';

		// Decide preview pelo tipo
		if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
			$content = '<img src="' . Util::h($url) . '" alt="' . Util::h($title) . '" style="max-width:100%;max-height:100%;object-fit:contain;">';
		} elseif ($extension === 'pdf') {
			// Tenta inline + link fallback
			$content = '<iframe src="' . Util::h($url) . '" loading="lazy" style="border:0;width:100%;height:100%;"></iframe>';
		} else {
			// Não suportado: só link
			$content = '<a href="' . Util::h($url) . '" target="_blank" rel="noopener" style="font-size:.9rem;text-align:center;display:block;">Baixar arquivo</a>';
		}

		return $open
			. $head
			. $boxOpen . $content . $boxClose
			. $btn
			. $close;
	}

	public static function buscaId($id_formulario)
	{
		$db = Zend_Registry::get('db');

		$select = "SELECT *
					FROM avaliacao_formulario a
					where a.id_formulario = " . $id_formulario;

		$registros = $db->fetchAll($select);

		if (count($registros) == 0) {
			Formulario::$erro = "Registro não encontrado.";
			return false;
		} else {
			return $registros[0];
		}
	}

	public static function perguntas($id_formulario)
	{
		$db = Zend_Registry::get('db');

		$select = "SELECT a.descricao as nome_formulario, b.*
                FROM avaliacao_formulario a
                INNER JOIN avaliacao_pergunta b on a.id_formulario = b.id_formulario
                WHERE a.id_formulario = " . $id_formulario . "
                order by ordem;";

		$registros = $db->fetchAll($select);

		$arrAux = [];

		if (count($registros)) {
			foreach ($registros as $value) {
				$arrAux[$value['id_pergunta']] = $value;
			}
		}

		return $arrAux;
	}

	public static function gerarProtocolo($id_envio, $id_empresa)
	{
		return Protocolo::protocolo_make($id_envio, $id_empresa);
	}

	public static function buscarEnvio($id_envio)
	{
		$db = Zend_Registry::get('db');

		$select = "SELECT a.*, b.descricao as nome_formulario, c.titulo as empresa, b.cor
                FROM avaliacao_envio a
				INNER JOIN avaliacao_formulario b on a.id_formulario = b.id_formulario
				INNER JOIN avaliacao_empresa c on c.id_empresa = a.id_empresa
                WHERE a.id_envio = " . $id_envio . ";";

		$retorno = $db->fetchAll($select);

		$envio = false;

		if (count($retorno)) {
			$envio = $retorno[0];

			$envio['protocolo'] = self::gerarProtocolo($id_envio, $envio['id_empresa']);

			$select = "SELECT a.*
				FROM avaliacao_resposta a
				where a.id_envio = " . $id_envio . "
				order by a.ordem;";

			$retorno = $db->fetchAll($select);

			$envio['respostas'] = $retorno;

			$select = "SELECT max(a.id_tarefa) as id_tarefa
				FROM avaliacao_tarefa a
				where a.id_envio = " . $id_envio . ";";

			$retorno = $db->fetchAll($select);

			$id_tarefa_atual = $retorno[0]['id_tarefa'];

			$envio['tarefa_atual'] = Tarefa::buscaId($id_tarefa_atual);

			$select = "SELECT max(a.id_tarefa) as id_tarefa
				FROM avaliacao_tarefa a
				where a.id_envio = " . $id_envio . " and a.id_tarefa <> " . $id_tarefa_atual . ";";

			$retorno = $db->fetchAll($select);

			$id_tarefa_anterior = count($retorno) ? $retorno[0]['id_tarefa'] : null;

			$envio['tarefa_anterior'] = $id_tarefa_anterior ? Tarefa::buscaId($id_tarefa_anterior) : null;
		}

		return $envio;
	}

	public static function limparContato($id_envio)
	{
		$db = Zend_Registry::get('db');

		$data = array(
			'email' => null,
			'telefone' => null,
			'nome' => null,
			'consent_email' => false,
			'consent_tel' => false
		);

		$db->insert("avaliacao_envio", $data, 'id_envio = ' . $id_envio);

		return true;
	}

	public static function salvarRespotas($campos, $respostas)
	{
		$db = Zend_Registry::get('db');

		$db->beginTransaction();

		$data = array(
			'id_empresa' => $campos['id_empresa'],
			'id_formulario' => $campos['id_formulario'],
			'nome' => $campos['nome'],
			'telefone' => $campos['telefone'],
			'email' => $campos['email'],
			'data_envio' => date('Y-m-d H:i:s'),
			'consent_email' => $campos['consent_email'],
			'consent_tel' => $campos['consent_tel'],
			'arquivos' => $campos['arquivos']
		);

		$db->insert("avaliacao_envio", $data);

		$id_envio = $db->lastInsertId();

		if (!$id_envio) {
			self::$erro = 'Erro ao gerar o envio!';
			return false;
		}

		$perguntas = self::perguntas($campos['id_formulario']);

		foreach ($respostas as $key => $value) {
			$comentario = (isset($_REQUEST['comentario']) && isset($_REQUEST['comentario'][$key])) ? $_REQUEST['comentario'][$key] : null;

			$pergunta = isset($perguntas[$key]) ? $perguntas[$key] : null;

			if (!$pergunta)	continue;

			$data = array(
				'id_envio' => $id_envio,
				'resposta' => substr($value, 0, self::$tamanho_maximo_resposta),
				'observacao' => substr($comentario, 0, self::$tamanho_maximo_resposta),
				'pergunta' => $pergunta['pergunta'],
				'tipo' => $pergunta['tipo'],
				'opcoes' => $pergunta['opcoes'],
				'grupo' => $pergunta['grupo'],
				'ordem' => $pergunta['ordem']
			);

			$db->insert("avaliacao_resposta", $data);
		}

		if (!Tarefa::criar($id_envio, Tarefa::NAO_INICIADA)) {
			self::$erro = Tarefa::$erro;
			return false;
		}

		$db->commit();

		$protocolo = self::gerarProtocolo($id_envio, $campos['id_empresa']);

		if ($campos['consent_email'] && !empty($campos['email'])) {

			$link_descadastrar = self::urlDescadastrar($campos['id_empresa'], $protocolo);
			$link = Empresa::urlSite($campos['id_empresa'], 'protocolo.php') . '&protocolo=' . $protocolo;

			$msg = '<p>Você acaba de registrar uma manifestação na plataforma avaliacao através do <strong>' . $protocolo . '</strong>.'
				. '<p>Você poderá consultar o status dele a qualquer momento clicando no link abaixo</p>'
				. '<p><a target="_blank" href="' . $link . '">' . $link . '</a></p>'
				. '<br></br><p>Para não receber mais mensagens, clique neste link:</p>'
				. '<p><a target="_blank" href="' . $link_descadastrar . '">' . $link_descadastrar . '</a></p>';

			Email::enviar($campos['email'], 'Registro confirmado', $msg);
		}

		return $protocolo;
	}



	public static function urlDescadastrar($id_empresa, $protocolo)
	{
		$url = Util::request_origin(false);

		return $url . '/site/descadastrar.php?token=' . Token::gerarToken($id_empresa) . '&protocolo=' . $protocolo;
	}


	public static function lista()
	{

		$db = Zend_Registry::get('db');

		$select = "SELECT *
					FROM avaliacao_formulario a
					order by a.id_formulario;";

		$registros = $db->fetchAll($select);

		$arrAux = [];

		if (count($registros)) {
			foreach ($registros as $value) {
				$arrAux[$value['id_formulario']] = $value;
			}
		}

		return $arrAux;
	}

	public static function consulta($inicio, $fim, $id_empresa)
	{
		$db = Zend_Registry::get('db');

		$where = " where a.data_envio between '" . $inicio . " 00:00:00' and '" . $fim . " 23:59:59' ";
		$where .= $id_empresa ? " and a.id_empresa = " . $id_empresa . " " : "";

		$select = "SELECT a.*, 
							b.descricao as nome_formulario,
							c.titulo as empresa, 
							e.nome as tipo_tarefa,
							t.id_tarefa as id_tarefa_atual,
							coalesce(t.data_fechamento, t.data_abertura) as data_movimento
                FROM avaliacao_envio a
				INNER JOIN avaliacao_formulario b on a.id_formulario = b.id_formulario
				INNER JOIN avaliacao_empresa c on c.id_empresa = a.id_empresa
				INNER JOIN 
				(select max(id_tarefa) as id_tarefa, id_envio from avaliacao_tarefa group by id_envio) d on d.id_envio = a.id_envio
				INNER JOIN avaliacao_tarefa t on t.id_tarefa = d.id_tarefa
				INNER JOIN avaliacao_tipo_tarefa e on t.id_tipo_tarefa = e.id_tipo_tarefa " . $where . ";";

		$retorno = $db->fetchAll($select);

		return $retorno;
	}

	public static function ano($ano)
	{
		$db = Zend_Registry::get('db');

		$select = "SELECT id_empresa,
				EXTRACT(MONTH FROM data_envio) AS mes,
				count(*) as quantidade
			FROM avaliacao_envio a
			where data_envio between '" . $ano . "-01-01 00:00:00' and '" . $ano . "-12-31 23:59:59'
			group by id_empresa, mes;";

		$retorno = $db->fetchAll($select);

		//print_r($retorno);die();

		return $retorno;
	}

	public static function estatistica($inicio, $fim, $id_empresa)
	{
		$db = Zend_Registry::get('db');

		$where = " where a.data_envio between '" . $inicio . " 00:00:00' and '" . $fim . " 23:59:59' ";
		$where .= $id_empresa ? " and a.id_empresa = " . $id_empresa . " " : "";

		$select = "SELECT count(*) as total,
						  coalesce(sum(case when c.id_envio is not null then 1 else 0 end),0) as trabalhados,
						  coalesce(sum(case when b.id_envio is not null then 1 else 0 end),0) as improcedentes,
						  coalesce(sum(case when d.id_envio is not null then 1 else 0 end),0) as concluidos
                FROM avaliacao_envio a 
				left join (select distinct id_envio from avaliacao_tarefa where id_tipo_tarefa = 9) b on a.id_envio = b.id_envio
				left join (select distinct id_envio from avaliacao_tarefa where data_fechamento is not null) c on a.id_envio = c.id_envio
				left join (select distinct id_envio from avaliacao_tarefa where id_tipo_tarefa = 10) d on a.id_envio = d.id_envio
				" . $where . ";";

		$retorno = $db->fetchAll($select);

		$estatistica = $retorno[0];

		$estatistica['procedentes'] = $estatistica['trabalhados'] - $estatistica['improcedentes'];

		$formularios = self::lista();

		if (count($formularios)) {
			foreach ($formularios as $id_formulario => $formulario) {
				$formularios[$id_formulario]['quantidade'] = 0;
			}
		}

		$maior = null;

		$select = "SELECT count(*) as total,
						  b.id_formulario
                FROM avaliacao_envio a 
				INNER JOIN avaliacao_formulario b on a.id_formulario = b.id_formulario
				" . $where . "
				group by b.id_formulario
				order by total;";

		$retorno = $db->fetchAll($select);

		if (count($retorno)) {
			foreach ($retorno as $value) {
				$formularios[$value['id_formulario']]['quantidade'] = $value['total'];
				$maior = $formularios[$value['id_formulario']];
			}
		}

		$estatistica['formularios'] = $formularios;
		$estatistica['maior'] = $maior;

		return $estatistica;
	}
}
