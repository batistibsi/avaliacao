<?

session_start();

set_include_path(get_include_path() . PATH_SEPARATOR . '../application/models/' . PATH_SEPARATOR . '../application/helpers/' . PATH_SEPARATOR . '../application/layouts/' . PATH_SEPARATOR . '/var/www/library/');

require_once "Zend/Loader/Autoloader.php";


date_default_timezone_set('America/Sao_Paulo');

// Set up autoload.
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);


// Definições do sistema
$config = new Zend_Config_Ini('/var/www/config.ini', 'interface');

// Definindo nível de erros
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

// Definindo o log de erros
ini_set('log_errors', 'on');
ini_set('error_log', $config->errorlog);

// produção ou teste?
/*if ($config->producao) ini_set('display_errors', 0);
else ini_set('display_errors', 'on');*/

ini_set('display_errors', 'on');

Zend_Registry::set('producao', $config->producao);

// URL principal do sistema
Zend_Registry::set('url', '../..');

// Diretório base do sistema
Zend_Registry::set('basedir', $config->basedir);

// Vericação de login
if (!isset($_SESSION['usuario_avaliacao']) || !isset($_SESSION['permissao_avaliacao'])) {

	if (!$_POST) {
		echo '<script>parent.parent.location = "' . Zend_Registry::get("url") . '/logon.php";</script>';
	} else {
		echo 'Sessão encerrada, atualize a página e realize o login novamente!';
	}
	exit();
}

Zend_Registry::set('usuario', $_SESSION['usuario_avaliacao']);
Zend_Registry::set('permissao', $_SESSION['permissao_avaliacao']);
Zend_Registry::set('id_usuario', $_SESSION['id_usuario_avaliacao']);
Zend_Registry::set('id_empresa', $_SESSION['id_empresa_avaliacao']);

// setting the view
$view = new Zend_View;
$view->setEncoding('UTF-8');
$view->setEscape('htmlentities');
$view->setBasePath('../application/views');
Zend_Registry::set('view', $view);


// Conecta no banco de dados com config
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


//verificando se a chamada é ajax ou normal
$request = new Zend_Controller_Request_Http();
Zend_Registry::set('ajax', $request->isXmlHttpRequest());



setlocale(LC_MONETARY, 'ptb');

$controller = Zend_Controller_Front::getInstance();
$controller->setControllerDirectory('../application/controllers');
$controller->throwExceptions(true);


Zend_Locale::setDefault('pt_BR');

// Dispatch the request using the front controller. 
$controller->dispatch();
