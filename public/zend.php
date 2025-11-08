<?php
/*Inclui Zend*/
set_include_path(PATH_SEPARATOR . '/var/www/avaliacao/application/models/' . PATH_SEPARATOR . '/var/www/avaliacao/application/helpers/' . PATH_SEPARATOR . '/var/www/library/');
require_once "Zend/Loader/Autoloader.php";
// Set up autoload.
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);
$options = array(
	Zend_Db::AUTO_QUOTE_IDENTIFIERS => false
);
$config = new Zend_Config_Ini('/var/www/config.ini', 'avaliacao_db');
Zend_Registry::set('config', $config);
$arr = $config->db->config->toArray();
$arr['options'] = $options;
try {
	$db = Zend_Db::factory($config->db->adapter, $arr);
	$db->getConnection();
} catch (Zend_Db_Adapter_Exception $e) {
	//trigger_error($e->getMessage());
}
Zend_Db_Table_Abstract::setDefaultAdapter($db);
Zend_Registry::set('db', $db);
Zend_Registry::set('leandro', "ES_da22bae2c8324073aab760744d867824");
/*Inclui Zend*/

