<?php

if (isset($_SERVER['REMOTE_ADDR'])) {
	http_response_code(403);
	exit(0);
}

$arrConf = parse_ini_file("/var/www/config.ini", true);

$host = $arrConf['avaliacao_db']['db.config.host'];
$dbname = $arrConf['avaliacao_db']['db.config.dbname'];
$user = $arrConf['avaliacao_db']['db.config.username'];
$password = $arrConf['avaliacao_db']['db.config.password'];
$port = $arrConf['avaliacao_db']['db.config.port'];
$stringConnection = "host=" . $host . " port=" . $port . " dbname=" . $dbname . " user=" . $user . " password=" . $password;
$con = pg_connect($stringConnection);

// Diretório dos scripts
$migrationDir = '/var/www/avaliacao/migrations';
$migrationFiles = scandir($migrationDir);

$query = "CREATE TABLE IF NOT EXISTS public.migrations
(
    id serial NOT NULL PRIMARY KEY,
    script_name character varying(255) NOT NULL,
    applied_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);";

$result = pg_query($con, $query);

// Obter scripts já aplicados

$query = "SELECT script_name FROM migrations";
$result = pg_query($con, $query);
$linhas = pg_fetch_all($result);
$appliedScripts = [];
if ($linhas && count($linhas)) {
	foreach ($linhas as $value) {
		$appliedScripts[] = $value['script_name'];
	}
}

foreach ($migrationFiles as $file) {
	// Ignorar diretórios especiais
	if ($file === '.' || $file === '..') {
		continue;
	}

	// Aplicar apenas scripts pendentes
	if (!in_array($file, $appliedScripts)) {
		$filePath = $migrationDir . '/' . $file;
		$sql = file_get_contents($filePath);

		try {
			// Executar script
			$result = pg_query($con, $sql);
			if (!$result) {
				throw new Exception(pg_last_error($con));
			}

			// Registrar o script como aplicado
			$insertQuery = "INSERT INTO migrations (script_name) VALUES ($1)";
			$insertResult = pg_query_params($con, $insertQuery, [$file]);
			if (!$insertResult) {
				throw new Exception(pg_last_error($con));
			}

			echo "Aplicado: $file\n";
		} catch (Exception $e) {
			echo "Erro ao aplicar $file: " . $e->getMessage() . "\n";
			break; // Interromper em caso de erro
		}
	}
}

echo "Todas as migrações pendentes foram aplicadas.\n";
