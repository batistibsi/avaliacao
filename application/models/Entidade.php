<?php
class Entidade{

	public static $erro;
	
	public static $particular = false;
	
	public function __construct(){

	}
	
	public static function permissao($entidade, $id_usuario, $id_perfil, $operacao){
		
		if($id_perfil == 1) return true;
		
		$db = Zend_Registry::get('db');

		if(is_numeric($entidade)){
				$select = "select a.particular, b.id_dono
						from avaliacao_entidade_perfil a
						inner join avaliacao_entidade b on a.tipo = b.tipo
						where b.id_entidade = ".$entidade."
						and a.operacao = '".$operacao."'
						and a.id_perfil = ".$id_perfil.";";
			
		}else{
		
			$select = "select a.particular, 1 as id_dono
						from avaliacao_entidade_perfil a
						where a.operacao = '".$operacao."'
						and a.id_perfil = ".$id_perfil."
						and a.tipo = '".$entidade."';";	
		}
			//die($select);
			
        $registros = $db -> fetchAll($select);
				
		if(count($registros) == 0){
			Entidade::$erro = "Não permitido!";
			return false;
		}else if(is_numeric($entidade) && $registros[0]['particular'] && $registros[0]['id_dono'] != $id_usuario){
			Entidade::$erro = "Não permitido, somente o dono do registro pode realizar a operação!";
			return false;
		}else{
			self::$particular = $registros[0]['particular'];
			return true;
		}
		
	}
	
	public static function buscaId($idEntidade){

        $db = Zend_Registry::get('db');

        $select = "select a.*
					from avaliacao_entidade a  
					where a.id_entidade = ".$idEntidade;
			
        $registros = $db -> fetchAll($select);
				
		if(count($registros) == 0){
			Entidade::$erro = "Entidade não encontrado.";
			return false;
		} else{
			return $registros[0];
		}
	}
		
	public static function buscaLink($id_link){

        $db = Zend_Registry::get('db');

        $select = "select a.*
					from avaliacao_entidade_link a  
					where a.id_link = ".$id_link;
			
        $registros = $db -> fetchAll($select);
				
		if(count($registros) == 0){
			Entidade::$erro = "Link não encontrado.";
			return false;
		} else{
			return $registros[0];
		}
	}
	
	public static function listaLinks($id_entidade){

        $db = Zend_Registry::get('db');

        $select = "select a.*
					from avaliacao_entidade_link a  
					where not a.deleted and a.id_entidade= ".$id_entidade;
			
        $registros = $db -> fetchAll($select);
				
		return $registros;
	}
	
	
	public static function insert($db,$tipo,$id_dono,$label,$descricao,$tag,$campos){
		  
		$result = $db -> fetchAll("select nextval('avaliacao_entidade_id_entidade_seq') as id");
		
		$id_entidade = $result[0]['id'];
		
		$data = array(
			"id_entidade"=>$id_entidade,
			"id_criador"=>Zend_Registry::get('id_usuario'),
			"id_dono"=>$id_dono,
			"tipo"=>$tipo,
			"label"=>$label,
			"descricao"=>$descricao,
			"tag"=>$tag
		);
		
		$db->insert("avaliacao_entidade",$data);

		return $id_entidade;
		
	}
	

	public static function update($db,$tipo,$id_entidade,$id_dono,$label,$descricao,$tag,$campos){
		
		$date = new DateTime();
		
		$select = "select * from avaliacao_entidade where id_entidade =".$id_entidade.";";
		$result = $db -> fetchAll($select);
		
		if(count($result) == 0){
			Entidade::$erro = "Registro não encontrado.";
			return false;
		}
		
		$entidadeAntiga = $result[0];

		$data = array(
			"id_alterador"=>Zend_Registry::get('id_usuario'),
			"id_dono"=>$id_dono,
			"descricao"=>$descricao,
			"data_modificacao"=>$date->format('Y-m-d H:i:s'),
			"tag"=>$tag,
			"label"=>$label,
			"deleted"=>false
		);
				
		$db->update("avaliacao_entidade",$data,"id_entidade = ".$id_entidade);
		
		$select = "select ".implode(',', array_keys($campos))." from avaliacao_".$tipo::$tableName." where id =".$id_entidade.";";
		$result = $db -> fetchAll($select);
		
		if(count($result) == 0){
			Entidade::$erro = "Registro não encontrado.";
			return false;
		}	

		$camposAntigos = $result[0];		
		
		/*
		$campos['descricao'] = $descricao;				
		$camposAntigos['descricao'] = $entidadeAntiga['descricao'];
		*/
		
		foreach($campos as $key=>$value){
			
			if($value == $camposAntigos[$key]) continue;
			
			$dataHistorico = array(
				"campo"=>$key,
				"valor_antigo"=>$camposAntigos[$key],
				"valor_novo"=>$value,
				"id_alterador_novo"=>Zend_Registry::get('id_usuario'), 
				"id_alterador_antigo"=>($entidadeAntiga['id_alterador']?$entidadeAntiga['id_alterador']:$entidadeAntiga['id_criador']), 
				"id_entidade"=>$id_entidade
			);
			$db->insert("avaliacao_historico",$dataHistorico);
		}

		return true;
	}
	
	public static function salvarDescricao($id_entidade,$descricao){
		$date = new DateTime();
		
		$db = Zend_Registry::get('db');
		
		$select = "select * from avaliacao_entidade where id_entidade =".$id_entidade.";";
		$result = $db -> fetchAll($select);
		
		if(count($result) == 0){
			Entidade::$erro = "Registro não encontrado.";
			return false;
		}
		
		if(!Entidade::permissao($id_entidade, Zend_Registry::get('id_usuario'), Zend_Registry::get('permissao'), 'update')){
			self::$erro = Entidade::$erro;
			return false;
		}
		
		$data = array(
			"id_alterador"=>Zend_Registry::get('id_usuario'),
			"descricao"=>$descricao,
			"data_modificacao"=>$date->format('Y-m-d H:i:s')
		);
				
		$db->update("avaliacao_entidade",$data,"id_entidade = ".$id_entidade);
		
		return true;
		
	}
	
	public static function buscaHistorico($id_entidade){
		
		$db = Zend_Registry::get('db');
		
		 $select = "select a.tipo, b.*, c.nome as alterador 
					from avaliacao_entidade a
					inner join avaliacao_historico b on a.id_entidade = b.id_entidade
					inner join avaliacao_usuario c on b.id_alterador_novo = c.id_usuario
					where a.id_entidade = ".$id_entidade."
					order by b.data desc";
			
        $retorno = $db -> fetchAll($select);
		
		if(count($retorno) > 0){
			/*foreach($retorno as $key=>$value){
				$retorno[$key]['cpf_cnpj_formatado'] = Util::mask($value['cpf_cnpj'],self::$mask[$value['tipo_pessoa']]);
			}*/
			return $retorno;
		}
		
		return false;	
		
	}
	
	
	public static function pesquisar($parametro, $onlyStart= false, $entidade = false){	

        $db = Zend_Registry::get('db');
		
		 $select = "select 	a.id_entidade as id, 
							a.label,
							a.tipo
					from avaliacao_entidade a
					where not a.deleted
					and ".($entidade?"tipo = '".$entidade."'":"tipo <> 'Indicacao'")."
					and REPLACE(upper(unaccent(a.tag)),'''',' ') like '".(!$onlyStart?'%':'').strtoupper(Util::tirarAcentos($parametro))."%'
					order by a.tipo, a.label";

        $retorno = $db -> fetchAll($select);
		
		/*if(count($retorno) > 0){
			foreach($retorno as $key=>$value){
				$retorno[$key]['text'] = '<strong>'.$value['tipo'].'</strong> '.$value['label'];
			}
		}*/
		
		
		return $retorno;
	}
	
	public static function desativar($id_entidade,$db=null){
			
		$db = $db?$db:Zend_Registry::get('db');
		
		$data = array(
			"deleted"=>true
		);
		
		$db->update("avaliacao_entidade",$data,"id_entidade = ".$id_entidade);

		return true;
	}
	
	public static function desativarLink($id_link,$db=null){
			
		$db = $db?$db:Zend_Registry::get('db');
		
		$data = array(
			"deleted"=>true
		);
		
		$db->update("avaliacao_entidade_link",$data,"id_link = ".$id_link);

		return true;
	}
	
	public static function insertLink($id_entidade,$label,$url,$descricao){
		
		if(!Entidade::permissao($id_entidade, Zend_Registry::get('id_usuario'), Zend_Registry::get('permissao'), 'update')){
			self::$erro = Entidade::$erro;
			return false;
		}		
				
		if(strlen($label) > 200 || strlen($label) < 1){
			self::$erro = 'Label inválido!';
			return false;
		}
		
		if(strlen($url) > 200 || strlen($url) < 1){
			self::$erro = 'URL inválida!';
			return false;
		}
		
		$db = Zend_Registry::get('db');	
		
		$db->beginTransaction();		
		
		$data = array(
			"id_entidade"=>$id_entidade,
			"label"=>$label,
			"url"=>$url,
			"id_criador"=>Zend_Registry::get('id_usuario'),
			"descricao"=>$descricao
		);	
		
		$db->insert("avaliacao_entidade_link",$data);		
		
		$db->commit();

		return true;
	}
	
	public static function updateLink($id_link,$label,$url,$descricao){
		
		$date = new DateTime();
		
		$link = self::buscaLink($id_link);
		
		if(!$link){
			self::$erro = 'Erro ao buscar o link!';
			return false;
		}
		
		if(!Entidade::permissao($link['id_entidade'], Zend_Registry::get('id_usuario'), Zend_Registry::get('permissao'), 'update')){
			self::$erro = Entidade::$erro;
			return false;
		}
	
		if(strlen($label) > 200 || strlen($label) < 1){
			self::$erro = 'Label inválido!';
			return false;
		}
		
		if(strlen($url) > 200 || strlen($url) < 1){
			self::$erro = 'URL inválida!';
			return false;
		}
			

		$db = Zend_Registry::get('db');
		
		$db->beginTransaction();
		
		$data = array(
			"label"=>$label,
			"url"=>$url,
			"id_alterador"=>Zend_Registry::get('id_usuario'),
			"data_modificacao"=>$date->format('Y-m-d H:i:s'),
			"descricao"=>$descricao
		);
				
		
		$db->update("avaliacao_entidade_link",$data,"id_link = ".$id_link);
		
		$db->commit();

		return true;
	}
	
}
?>