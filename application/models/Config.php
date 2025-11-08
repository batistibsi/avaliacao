<?php
class Config
{
        public static $erro;

        public static function busca()
        {
                $db = Zend_Registry::get('db');

                $select = "select * from avaliacao_config a
                                where id_config = 1";

                $registros = $db->fetchAll($select);

                return $registros[0];
        }
}
