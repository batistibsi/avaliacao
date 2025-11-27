<?php
class Formulario
{
        public static $erro;

        public static function buscaId($id_formulario)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_formulario a where a.id_formulario = " . $id_formulario;

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return $registros[0];
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }

        public static function buscarBlocos($id_formulario)
        {
                $db = Zend_Registry::get('db');

                $select = "select * from avaliacao_bloco where id_formulario = " . $id_formulario . " order by ordem ";

                $registros = $db->fetchAll($select);

                if (count($registros) > 0) {
                        foreach ($registros as $key => $value) {

                                $s = "select a.*
                                        from avaliacao_pergunta a
                                        where a.id_bloco = " . $value['id_bloco'] . " order by a.ordem ";

                                $r = $db->fetchAll($s);

                                $registros[$key]['itens'] = $r;
                        }
                }

                return $registros;
        }

        public static function uniqueNome($id_formulario, $nome)
        {
                $db = Zend_Registry::get('db');

                $select = "select * from avaliacao_formulario where id_formulario <> " . $id_formulario . " and nome = '" . $nome . "'";

                $registros = $db->fetchAll($select);

                if (count($registros) == 0) return true;

                return false;
        }


        private static function estrutura($id_formulario, $estrutura)
        {
                if (count($estrutura)) {
                        $db = Zend_Registry::get('db');
                        $ordem = 0;
                        foreach ($estrutura as $bloco) {

                                $query = "INSERT INTO avaliacao_bloco(nome, id_formulario, ordem)
                        VALUES ('" . $bloco->nome . "'," . $id_formulario . ", " . $ordem . ")
                    	RETURNING id_bloco;";

                                $registros = $db->fetchAll($query);

                                $id_bloco = $registros[0]['id_bloco'];

                                if (count($bloco->itens)) {
                                        foreach ($bloco->itens as $pergunta) {
                                                $ordem++;
                                                $query = "INSERT INTO avaliacao_pergunta(id_formulario, pergunta, id_bloco, peso, ordem)
									VALUES (" . $id_formulario . ",
											'" . $pergunta->nome . "',
                                                                                        " . $id_bloco . ",
                                                                                        " . $pergunta->peso . ",
											" . $ordem . ") 
									RETURNING id_pergunta;";

                                                // die($query);

                                                $registros = $db->fetchAll($query);

                                                // $id_pergunta = $registros[0]['id_pergunta'];
                                        }
                                }
                        }
                }

                return true;
        }

        public static function insert($nome, $campos, $estrutura)
        {
                if (strlen($nome) > 255 || strlen($nome) < 2) {
                        self::$erro = 'Nome inválido!';
                        return false;
                }

                if (!self::uniqueNome(0, $nome)) {
                        Usuario::$erro = 'Já existe um registro com o nome: ' . $nome . '.';
                        return false;
                }

                $db = Zend_Registry::get('db');
                $db->beginTransaction();

                $query = "INSERT INTO avaliacao_formulario(nome)
                        VALUES ('" . $nome . "')
                    	RETURNING id_formulario;";

                $registros = $db->fetchAll($query);

                $id_formulario = $registros[0]['id_formulario'];

                if (!self::estrutura($id_formulario, $estrutura)) {
                        $db->rollback();
                        return false;
                }

                $db->commit();

                return true;
        }

        public static function update($id_formulario, $nome, $campos, $estrutura)
        {

                if (strlen($nome) > 255 || strlen($nome) < 2) {
                        self::$erro = 'Nome inválido!';
                        return false;
                }

                if (!self::uniqueNome($id_formulario, $nome)) {
                        Usuario::$erro = 'Já existe um registro com o nome: ' . $nome . '.';
                        return false;
                }

                $db = Zend_Registry::get('db');
                $db->beginTransaction();

                $data = array(
                        "nome" => $nome
                );

                $db->update("avaliacao_formulario", $data, "id_formulario = " . $id_formulario);

                $db->delete("avaliacao_bloco", "id_formulario=" . $id_formulario);

                if (!self::estrutura($id_formulario, $estrutura)) {
                        $db->rollback();
                        return false;
                }

                $db->commit();

                return true;
        }

        public static function desativar($id_formulario)
	{
		$db = Zend_Registry::get('db');

		$data = array(
			"ativo" => false
		);

		$db->update("avaliacao_formulario", $data, "id_formulario = " . $id_formulario);

		return true;
	}


        public static function lista()
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_formulario a where a.ativo order by a.nome";

                $retorno = $db->fetchAll($select);

                return $retorno;
        }
}
