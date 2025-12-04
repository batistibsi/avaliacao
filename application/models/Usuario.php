<?php
class Usuario
{

	public static $erro;

	public function __construct() {}

	public static function buscaId($id_usuario)
	{
		$db = Zend_Registry::get('db');

		$select = "select a.*, b.*, c.nome as grupo
				from avaliacao_usuario a 
				left join avaliacao_grupo_usuario b on a.id_usuario = b.id_usuario
				left join avaliacao_grupo c on b.id_grupo = c.id_grupo
				where a.id_usuario = " . $id_usuario;

		$registros = $db->fetchAll($select);

		if (count($registros) == 0) {
			Usuario::$erro = "Usuário não encontrado.";
			return false;
		} else {
			return $registros[0];
		}
	}

	public static function estatistica($inicio, $fim, $id_grupo, $id_usuario)
	{

		$blocos = [];

		$db = Zend_Registry::get('db');

		$where = " and b.data_envio between '" . $inicio . " 00:00:00' and '" . $fim . " 23:59:59' ";

		$select = "select a.bloco, avg(a.resposta::integer*a.peso) as total
			from avaliacao_resposta a
			inner join avaliacao_envio b on a.id_envio = b.id_envio
			where b.id_grupo = " . $id_grupo . " ".$where."
			group by a.bloco;";

		$registros = $db->fetchAll($select);

		if (count($registros)) {
			foreach ($registros as $key => $value) {
				$blocos[$value['bloco']]['grupo'] = $value['total'];
				$blocos[$value['bloco']]['usuario'] = 0;
			}
		}

		$select = "select a.bloco, avg(a.resposta::integer*a.peso) as total
			from avaliacao_resposta a
			inner join avaliacao_envio b on a.id_envio = b.id_envio
			where b.id_usuario = " . $id_usuario . " ".$where."
			group by a.bloco;";

		$registros = $db->fetchAll($select);

		if (count($registros)) {
			foreach ($registros as $key => $value) {
				if (!isset($blocos[$value['bloco']])) {
					$blocos[$value['bloco']]['grupo'] = 0;
				}
				$blocos[$value['bloco']]['usuario'] = $value['total'];
			}
		}

		$dimensoes = [];

		if (count($blocos)) {
			foreach ($blocos as $key => $value) {
				$dimensoes['labels'][] = $key;
				$dimensoes['colab'][] = $value['usuario'];
				$dimensoes['grupo'][] = $value['grupo'];
			}
		}

		$select = "select to_char(b.data_envio, 'YYYY-MM') as mes, avg(a.resposta::integer*a.peso) as total
			from avaliacao_resposta a
			inner join avaliacao_envio b on a.id_envio = b.id_envio
			where b.id_grupo = " . $id_grupo . " ".$where."
			group by mes;";

		$registros = $db->fetchAll($select);

		$evolucao = [];

		if (count($registros)) {
			foreach ($registros as $key => $value) {
				$evolucao['labels'][] = $value['mes'];
				$evolucao['valores'][] = (float) round($value['total'],2);
			}
		}

		$mediaColab = 0;
		$mediaGrupo = 0;

		$select = "select avg(a.resposta::integer*a.peso) as total
			from avaliacao_resposta a
			inner join avaliacao_envio b on a.id_envio = b.id_envio
			where b.id_grupo = " . $id_grupo . " ".$where.";";

		$registros = $db->fetchAll($select);

		if (count($registros)) {
			foreach ($registros as $key => $value) {
				$mediaGrupo = (float) $value['total'];
			}
		}

		$select = "select a.bloco, avg(a.resposta::integer*a.peso) as total
			from avaliacao_resposta a
			inner join avaliacao_envio b on a.id_envio = b.id_envio
			where b.id_usuario = " . $id_usuario . " ".$where."
			group by a.bloco;";

		$registros = $db->fetchAll($select);

		if (count($registros)) {
			foreach ($registros as $key => $value) {
				$mediaColab = (float) $value['total'];
			}
		}


		$estatistica = [
			'dimensoes' => $dimensoes,
			'evolucao' => $evolucao,
			'mediaColab' => $mediaColab,
			'mediaGrupo' => $mediaGrupo
		];

		return $estatistica;
	}

	public static function buscaEmail($email)
	{

		$db = Zend_Registry::get('db');

		$select = "select * from avaliacao_usuario where email = '" . $email . "' and ativo";

		$registros = $db->fetchAll($select);

		if (count($registros) == 0) {
			Usuario::$erro = "Usuário não encontrado.";
			return false;
		} else {
			return $registros[0];
		}
	}

	public static function alterarSenha($id_usuario, $senhaAtual, $novaSenha)
	{

		$hashAtual = Usuario::buscaId($id_usuario);
		$hashAtual = $hashAtual['senha'];

		if ($senhaAtual != $hashAtual) {
			Usuario::$erro = 'A senha atual informada não confere!';
			return false;
		}

		$db = Zend_Registry::get('db');

		$data = array(
			"senha" => $novaSenha
		);

		$db->update("avaliacao_usuario", $data, "id_usuario = '" . $id_usuario . "'");

		return true;
	}

	public static function uniqueEmail($id, $email)
	{
		$db = Zend_Registry::get('db');

		$select = "select * from avaliacao_usuario where id_usuario <> " . $id . " and email = '" . $email . "' and ativo";

		$registros = $db->fetchAll($select);

		if (count($registros) == 0) return true;

		return false;
	}

	public static function logLogin($id_usuario)
	{

		$db = Zend_Registry::get('db');

		$data = array(
			"id_usuario" => $id_usuario
		);

		$db->insert("avaliacao_login", $data);

		return true;
	}

	public static function insert($email, $nome, $idPerfil, $ativo, $senha, $confirmSenha)
	{

		if ($ativo) {
			$idPerfil = (int) $idPerfil;

			if (strlen($email) < 3 || strlen($email) > 80) {
				Usuario::$erro = 'Email inválido!';
				return false;
			}

			if (strlen($nome) > 80 || strlen($nome) < 1) {
				Usuario::$erro = 'Nome inválido!';
				return false;
			}

			if (!Usuario::uniqueEmail(0, $email)) {
				Usuario::$erro = 'Já existe um registro com o email: ' . $email . '.';
				return false;
			}

			if (!$idPerfil) {
				Usuario::$erro = 'Perfil inválido!';
				return false;
			}

			if ($senha != "" && $confirmSenha != $senha) {
				Usuario::$erro = 'Senhas informadas não conferem!';
				return false;
			}

			if ($senha == "") {
				Usuario::$erro = 'Informe uma senha para salvar!';
				return false;
			}
		}

		$db = Zend_Registry::get('db');


		$data = array(
			"email" => $email,
			"nome" => $nome,
			"senha" => $senha,
			"id_perfil" => $idPerfil,
			"ativo" => $ativo
		);

		$db->insert("avaliacao_usuario", $data);

		$link = $_SERVER['HTTP_HOST'];

		$msg = '<p>Parabéns, seu cadastro na plataforma avaliacao foi concluído com sucesso.</p>'
			. '<p>Acesse o sistema clicando no link abaixo</p>'
			. '<p><a href="' . $link . '">' . $link . '</a></p>';

		Email::enviar($email, 'Cadastro de acesso à Plataforma de avaliacao confirmado', $msg);

		return true;
	}

	public static function update($email, $nome, $idPerfil, $ativo, $senha, $id_usuario, $confirmSenha)
	{

		if (strlen($email) < 3 || strlen($email) > 80) {
			Usuario::$erro = 'Email inválido!';
			return false;
		}

		if (strlen($nome) > 80 || strlen($nome) < 1) {
			Usuario::$erro = 'Nome inválido!';
			return false;
		}

		if (!Usuario::uniqueEmail($id_usuario, $email)) {
			Usuario::$erro = 'Já existe um registro com o email: ' . $email . '.';
			return false;
		}

		if (!$idPerfil) {
			Usuario::$erro = 'Perfil inválido!';
			return false;
		}


		if ($senha != "" && $confirmSenha != $senha) {
			Usuario::$erro = 'Senhas informadas não conferem!';
			return false;
		}

		$db = Zend_Registry::get('db');

		$data = array(
			"email" => $email,
			"nome" => $nome,
			"id_perfil" => $idPerfil,
			"ativo" => $ativo
		);

		if ($senha != "") {
			$data['senha'] = $senha;
			self::emailSenha($email, $senha);
		}

		$db->update("avaliacao_usuario", $data, "id_usuario = " . $id_usuario);

		return true;
	}

	public static function emailSenha($email, $senha)
	{
		$msg = '<p>ATENÇÃO!</p>'
			. '<p>Sua nova Senha de acesso à Plataforma de avaliacao é:</p>'
			. '<p><strong>' . $senha . '</strong></p>';

		Email::enviar($email, 'Mudança de senha', $msg);
	}

	public static function desativar($id_usuario)
	{

		if ($id_usuario == 1) {
			Usuario::$erro = 'Este usuário não pode ser removido!';
			return false;
		}

		$db = Zend_Registry::get('db');

		$data = array(
			"ativo" => false
		);

		$db->update("avaliacao_usuario", $data, "id_usuario = " . $id_usuario);

		return true;
	}

	public static function lista($id_perfil = '1,2,3')
	{

		$db = Zend_Registry::get('db');

		if (Zend_Registry::get('permissao') == 1) {

			$select = "select avaliacao_usuario.*,
					avaliacao_perfil.descricao as descricao_perfil
				  from avaliacao_usuario
				  left join avaliacao_perfil on avaliacao_usuario.id_perfil = avaliacao_perfil.id_perfil
				   where avaliacao_usuario.ativo and avaliacao_perfil.id_perfil in(" . $id_perfil . ") and avaliacao_usuario.id_usuario <> 1
				  order by avaliacao_usuario.nome";
		} else {

			$select = "select a.*,
					p.descricao as descricao_perfil
				  from avaliacao_usuario a
				  inner join avaliacao_perfil p on p.id_perfil = a.id_perfil
				  inner join avaliacao_grupo_usuario gu on a.id_usuario = gu.id_usuario
				  inner join avaliacao_grupo g on gu.id_grupo = g.id_grupo
				  where a.ativo 
				   and g.id_usuario = " . Zend_Registry::get('id_usuario') . "
				   and a.id_perfil in(" . $id_perfil . ")
				  order by a.nome";
		}

		$retorno = $db->fetchAll($select);

		return $retorno;
	}

	public static function logins()
	{

		$db = Zend_Registry::get('db');

		$select = "select a.nome,
					l.*
				  from avaliacao_usuario a
				  inner join avaliacao_login l on l.id_usuario = a.id_usuario
				  where a.id_usuario <> 1
				  order by a.nome";

		$retorno = $db->fetchAll($select);

		return $retorno;
	}

	public static function pesquisar($parametro)
	{

		$db = Zend_Registry::get('db');

		$select = "select 'u'||a.id_usuario as id_entidade, 						
							a.nome as tag, 
							'u'||a.id_usuario as id, 
							a.nome,
							'Usuario' as tipo
				  from avaliacao_usuario a
				  where a.ativo
				  and REPLACE(upper(unaccent(a.nome)),'''',' ') like '" . strtoupper(Util::tirarAcentos($parametro)) . "%'
				  order by a.nome";

		$retorno = $db->fetchAll($select);

		return $retorno;
	}

	public static function comboPerfil()
	{
		$db = Zend_Registry::get('db');

		$select = "select * from avaliacao_perfil order by id_perfil";

		$retorno = $db->fetchAll($select);

		return $retorno;
	}
}
