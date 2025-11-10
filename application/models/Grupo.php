<?php
class Grupo
{
        public static $erro;

        public static function buscaId($id_grupo)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_grupo a where a.id_grupo = " . $id_grupo;

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return $registros[0];
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }

        public static function uniqueNome($id_grupo, $nome)
        {
                $db = Zend_Registry::get('db');

                $select = "select * from avaliacao_grupo where id_grupo <> " . $id_grupo . " and nome = '" . $nome . "'";

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

                $db->insert("avaliacao_grupo", $data);

                return true;
        }

        public static function update($id_grupo, $nome, $campos)
        {

                if (strlen($nome) > 255 || strlen($nome) < 2) {
                        self::$erro = 'Nome inválido!';
                        return false;
                }

                if (!self::uniqueNome($id_grupo, $nome)) {
                        Usuario::$erro = 'Já existe um registro com o nome: ' . $nome . '.';
                        return false;
                }


                $db = Zend_Registry::get('db');

                $data = array(
                        "nome" => $nome,
                        "id_usuario" => $campos['id_usuario']
                );

                $db->update("avaliacao_grupo", $data, "id_grupo = " . $id_grupo);

                return true;
        }

        public static function delete($id_grupo)
        {
                $db = Zend_Registry::get('db');

                $db->delete("avaliacao_grupo", "id_grupo = " . $id_grupo);
        }


        public static function lista()
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_grupo a order by a.nome";

                $retorno = $db->fetchAll($select);

                return $retorno;
        }
}
