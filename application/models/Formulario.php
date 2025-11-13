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
                                                $query = "INSERT INTO avaliacao_pergunta(id_formulario, pergunta, id_bloco, ordem)
									VALUES (" . $id_formulario . ",
											'" . $pergunta->nome . "',
                                                                                        " . $id_bloco . ",
											" . $ordem . ") 
									RETURNING id_pergunta;";

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

                $query = "INSERT INTO avaliacao_formulario(nome)
                        VALUES ('" . $nome . "')
                    	RETURNING id_formulario;";

                $registros = $db->fetchAll($query);

                $id_formulario = $registros[0]['id_formulario'];

                return self::estrutura($id_formulario, $estrutura);
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

                $data = array(
                        "nome" => $nome
                );

                $db->update("avaliacao_formulario", $data, "id_formulario = " . $id_formulario);

                return true;
        }

        public static function delete($id_formulario)
        {
                $db = Zend_Registry::get('db');

                $db->delete("avaliacao_formulario", "id_formulario = " . $id_formulario);
        }


        public static function lista()
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_formulario a order by a.nome";

                $retorno = $db->fetchAll($select);

                return $retorno;
        }
}
