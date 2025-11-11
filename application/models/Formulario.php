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


        public static function insert($nome, $campos)
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

                $data = array(
                        "nome" => $nome
                );

                $db->insert("avaliacao_formulario", $data);

                return true;
        }

        public static function update($id_formulario, $nome, $campos)
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
