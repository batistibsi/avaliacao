<?php
class Base
{
        public static $erro;

        public static function buscaId($id_base)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_base a where a.id_base = " . $id_base;

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return $registros[0];
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }

        public static function uniqueNome($id_base, $nome)
        {
                $db = Zend_Registry::get('db');

                $select = "select * from avaliacao_base where id_base <> " . $id_base . " and nome = '" . $nome . "'";

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
                        "nome" => $nome,
                        "id_usuario" => $campos['id_usuario']
                );

                $db->insert("avaliacao_base", $data);

                return true;
        }

        public static function update($id_base, $nome, $campos)
        {

                if (strlen($nome) > 255 || strlen($nome) < 2) {
                        self::$erro = 'Nome inválido!';
                        return false;
                }

                if (!self::uniqueNome($id_base, $nome)) {
                        Usuario::$erro = 'Já existe um registro com o nome: ' . $nome . '.';
                        return false;
                }


                $db = Zend_Registry::get('db');

                $data = array(
                        "nome" => $nome,
                        "id_usuario" => $campos['id_usuario']
                );

                $db->update("avaliacao_base", $data, "id_base = " . $id_base);

                return true;
        }

        public static function delete($id_base)
        {
                $db = Zend_Registry::get('db');

                $db->delete("avaliacao_base", "id_base = " . $id_base);
        }


        public static function lista()
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_base a order by a.nome";

                $retorno = $db->fetchAll($select);

                return $retorno;
        }
}
