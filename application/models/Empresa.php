<?php
class Empresa
{
        public static $erro;

        public static function buscaId($id_empresa)
        {
                $db = Zend_Registry::get('db');

                $select = "select * from avaliacao_empresa a
                                where id_empresa =" . $id_empresa;

                $registros = $db->fetchAll($select);

                return $registros[0];
        }

        public static function urlSite($id_empresa,$sub_pasta='index.php')
        {
                $url = Util::request_origin(false);

                return $url . '/site/'.$sub_pasta.'?token=' . Token::gerarToken($id_empresa);
        }

        public static function criar($campos)
        {
                $db = Zend_Registry::get('db');

                $db->beginTransaction();

                $db->insert("avaliacao_empresa", $campos);

                $select = "select max(id_empresa) as id_empresa from avaliacao_empresa;";

                $registros = $db->fetchAll($select);

                $db->commit();

                return $registros[0]['id_empresa'];
        }

        public static function salvar($id_empresa, $campos)
        {
                $db = Zend_Registry::get('db');

                $db->update("avaliacao_empresa", $campos, 'id_empresa = ' . $id_empresa);

                return true;
        }

        public static function lista()
        {
                $db = Zend_Registry::get('db');

                $select = "select * 
                           from avaliacao_empresa a
                           inner join avaliacao_usuario b on a.id_empresa = b.id_empresa
                           where b.ativo";

                $registros = $db->fetchAll($select);

                $arrAux = [];

                if (count($registros)) {
                        foreach ($registros as $value) {
                                $arrAux[$value['id_empresa']] = $value;
                        }
                }

                return $arrAux;
        }
}
