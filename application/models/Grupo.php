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


        public static function insert($nome, $campos, $membros)
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
                        "id_usuario" => $campos['id_usuario'],
                        "id_formulario" => $campos['id_formulario']
                );

                $db->insert("avaliacao_grupo", $data);

                $select = "select a.id_grupo from avaliacao_grupo a where a.nome = '" . $nome . "'";

                $retorno = $db->fetchAll($select);

                $id_grupo = $retorno[0]['id_grupo'];

                if (count($membros)) {
                        foreach ($membros as $value) {
                                $db->delete('avaliacao_grupo_usuario', 'id_usuario=' . $value);
                                $data = array(
                                        "id_grupo" => $id_grupo,
                                        "id_usuario" => $value
                                );
                                $db->insert("avaliacao_grupo_usuario", $data);
                        }
                }

                return true;
        }

        public static function update($id_grupo, $nome, $campos, $membros)
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
                        "id_usuario" => $campos['id_usuario'],
                        "id_formulario" => $campos['id_formulario']
                );

                $db->update("avaliacao_grupo", $data, "id_grupo = " . $id_grupo);

                $db->delete('avaliacao_grupo_usuario', 'id_grupo=' . $id_grupo);

                if (count($membros)) {
                        foreach ($membros as $value) {
                                $db->delete('avaliacao_grupo_usuario', 'id_usuario=' . $value);
                                $data = array(
                                        "id_grupo" => $id_grupo,
                                        "id_usuario" => $value
                                );
                                $db->insert("avaliacao_grupo_usuario", $data);
                        }
                }

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

        public static function membros($id_grupo)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.*, b.nome
                                from avaliacao_grupo_usuario a
                                inner join avaliacao_usuario b on a.id_usuario = b.id_usuario 
                                where a.id_grupo = " . $id_grupo . "
                                order by b.nome";

                $retorno = $db->fetchAll($select);

                $arrAux = [];

                if(count($retorno)){
                        foreach($retorno as $value){
                                $arrAux[$value['id_usuario']] = $value;
                        }
                }

                return $arrAux;
        }
}
