<?php
class Envio
{
        public static $erro;

        public static function buscaId($id_envio)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_envio a where a.id_envio = " . $id_envio;

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return $registros[0];
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }


        public static function insert($campos, $respostas)
        {

                if (!$campos['id_grupo']) {
                        self::$erro = "Grupo não informado!";
                        return false;
                }
                if (!$campos['id_formulario']) {
                        self::$erro = "Formulario não informado!";
                        return false;
                }
                if (!$campos['id_usuario']) {
                        self::$erro = "Usuário não informado!";
                        return false;
                }
                if (!$campos['id_avaliador']) {
                        self::$erro = "Avaliador não informado!";
                        return false;
                }
                if (!$campos['data_envio']) {
                        self::$erro = "Data não informada!";
                        return false;
                }

                $db = Zend_Registry::get('db');

                $query = "INSERT INTO avaliacao_envio(id_grupo, id_formulario, id_usuario, id_avaliador, data_envio)
                        VALUES (" . $campos['id_grupo'] . ",
                                " . $campos['id_formulario'] . ",
                                " . $campos['id_usuario'] . ",
                                " . $campos['id_avaliador'] . ",
                                '" . $campos['data_envio'] . "')
                    	RETURNING id_envio;";

                $registros = $db->fetchAll($query);

                $id_envio = $registros[0]['id_envio'];

                return true;
        }

        public static function lista()
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_envio a order by a.nome";

                $retorno = $db->fetchAll($select);

                return $retorno;
        }
}
