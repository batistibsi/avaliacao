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

                if (!$respostas || !count($respostas)) {
                        self::$erro = "Não há respostas no envio!";
                        return false;
                }
                if (!$campos['id_formulario']) {
                        self::$erro = "Formulario não informado!";
                        return false;
                }
                $estrutura = Formulario::buscarBlocos($campos['id_formulario']);

                if (!$estrutura) {
                        self::$erro = "Estrutura de formulario não encontrada!";
                        return false;
                }

                if (!$campos['id_grupo']) {
                        self::$erro = "Grupo não informado!";
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
                $db->beginTransaction();

                $query = "INSERT INTO avaliacao_envio(id_grupo, id_formulario, id_usuario, id_avaliador, data_envio)
                        VALUES (" . $campos['id_grupo'] . ",
                                " . $campos['id_formulario'] . ",
                                " . $campos['id_usuario'] . ",
                                " . $campos['id_avaliador'] . ",
                                '" . $campos['data_envio'] . "')
                    	RETURNING id_envio;";

                $registros = $db->fetchAll($query);

                $id_envio = $registros[0]['id_envio'];

                foreach ($estrutura as $bloco) {
                        if (!$bloco['itens'] || !count($bloco['itens'])) continue;

                        foreach ($bloco['itens'] as $pergunta) {

                                if (!isset($respostas[$pergunta['id_pergunta']]['nota'])) {
                                        self::$erro = "Problemas ao resgatar a resposta";
                                        $db->rollback();
                                        return false;
                                }

                                $query = "INSERT INTO avaliacao_resposta(id_envio, pergunta, bloco, resposta, observacao, peso)
                                        VALUES (" . $id_envio . ",
                                                '" . $pergunta['pergunta'] . "',
                                                '" . $bloco['nome'] . "',
                                                " . $respostas[$pergunta['id_pergunta']]['nota'] . ",
                                                '" . $respostas[$pergunta['id_pergunta']]['comentario'] . "',
                                                " . $pergunta['peso'] . ")
                                        RETURNING id_resposta;";

                                $registros = $db->fetchAll($query);

                                //$id_resposta = $registros[0]['id_resposta'];
                        }
                }

                $db->commit();

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
