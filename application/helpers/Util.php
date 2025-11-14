<?php
class Util
{

	public static $erro;

	public static function gotoIndex()
	{
		echo "<script>window.location = '../../index'</script>";
		exit();
	}

	public static $mes = array('', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');

	public static $mask = array('fisica' => '###.###.###-##', 'juridica' => '##.###.###/####-##');

	public static function  h($s)
	{
		return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
	}
	public static function ext($path)
	{
		return strtolower(pathinfo($path, PATHINFO_EXTENSION));
	}

	static $enclist = array(
		'UTF-8',
		'ASCII',
		'ISO-8859-1',
		'ISO-8859-2',
		'ISO-8859-3',
		'ISO-8859-4',
		'ISO-8859-5',
		'ISO-8859-6',
		'ISO-8859-7',
		'ISO-8859-8',
		'ISO-8859-9',
		'ISO-8859-10',
		'ISO-8859-13',
		'ISO-8859-14',
		'ISO-8859-15',
		'ISO-8859-16',
		'Windows-1251',
		'Windows-1252',
		'Windows-1254',
	);

	/**
	 * Retorna a origem da requisição: https://exemplo.com[:porta]
	 * $trustProxy: confie em X-Forwarded-* (use true se estiver atrás de proxy/CDN)
	 */
	public static function request_origin(bool $trustProxy = false): string
	{
		// Scheme
		$scheme = 'http';
		if ($trustProxy && !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			$scheme = strtolower(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]); // pega o 1º
		} elseif (
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
			(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
		) {
			$scheme = 'https';
		}

		// Host (com possível porta)
		if ($trustProxy && !empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			// pode vir "host1, host2"
			$host = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0]);
		} elseif (!empty($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		} else {
			$host = $_SERVER['SERVER_NAME'] ?? 'localhost';
			if (!empty($_SERVER['SERVER_PORT'])) {
				$host .= ':' . $_SERVER['SERVER_PORT'];
			}
		}

		// Porta (caso não esteja no host e não seja padrão)
		$port = null;
		if ($trustProxy && !empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
			$port = (int)$_SERVER['HTTP_X_FORWARDED_PORT'];
		} elseif (!empty($_SERVER['SERVER_PORT'])) {
			$port = (int)$_SERVER['SERVER_PORT'];
		}

		// Se host já tem porta, não mexe
		if (strpos($host, ':') === false && $port) {
			$isDefault = ($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80);
			if (!$isDefault) {
				$host .= ':' . $port;
			}
		}

		return $scheme . '://' . $host;
	}

	public static function mascaraTelefone($numero)
	{
		// remove tudo que não é número
		$numero = preg_replace('/\D/', '', $numero);

		if (strlen($numero) == 11) {
			// celular (9 dígitos)
			return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $numero);
		} elseif (strlen($numero) == 10) {
			// fixo (8 dígitos)
			return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $numero);
		} else {
			return $numero; // formato desconhecido
		}
	}

	public static function gerarIniciais($nome)
	{
		// Remove múltiplos espaços e trim
		$nome = trim(preg_replace('/\s+/', ' ', $nome));

		if ($nome === '') {
			return '';
		}

		// Separa pelo espaço
		$partes = explode(' ', $nome);

		// 1ª letra do primeiro nome
		$ini1 = mb_substr($partes[0], 0, 1, 'UTF-8');

		// 2ª letra: do último nome (se existir)
		if (count($partes) > 1) {
			$ini2 = mb_substr(end($partes), 0, 1, 'UTF-8');
		} else {
			// Se tiver só um nome, usa a segunda letra do mesmo nome
			$ini2 = mb_substr($partes[0], 1, 1, 'UTF-8') ?: '';
		}

		return mb_strtoupper($ini1 . $ini2, 'UTF-8');
	}

	public function __construct() {}

	public static function getModule() {}

	public static function encodeValue($valor)
	{
		$valor = trim($valor);
		if ($valor) {
			if ($encoding = mb_detect_encoding($valor, Util::$enclist, false)) {
				$valor = mb_convert_encoding($valor, 'UTF-8', $encoding);
			}
		}
		return !empty($valor) ? $valor : null;
	}

	public static function defaultData()
	{
		$hoje = new DateTime();
		return $hoje->format('Y-m-d');
	}

	public static function tirarAcentos($string)
	{
		$acentos = array("'");
		$string = str_replace($acentos, " ", $string);
		return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $string);
	}

	public static function formatHora($strData = null, $segundo = false)
	{
		$data = $strData ? new DateTime($strData) : new DateTime();
		return $segundo ? $data->format('H:i:s') : $data->format('H:i');
	}


	public static function formatData($strData = null)
	{
		$data = $strData ? new DateTime($strData) : new DateTime();
		return $data->format('d/m/Y');
	}

	public static function cleanSqlCommands($string)
	{
		$string = preg_replace("/(from|select|insert|update|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $string);
		$string = strip_tags($string);
		$string = (get_magic_quotes_gpc()) ? $string : addslashes($string);

		return $string;
	}

	public static function getDiaDaSemana($timestamp)
	{
		$timestamp = strtotime($timestamp);
		$date = getdate($timestamp);
		$diaSemana = $date['weekday'];
		if (preg_match('/(sunday|domingo)/mi', $diaSemana)) $diaSemana = 'Domingo';
		else if (preg_match('/(monday|segunda)/mi', $diaSemana)) $diaSemana = 'Segunda';
		else if (preg_match('/(tuesday|terça)/mi', $diaSemana)) $diaSemana = 'Terça';
		else if (preg_match('/(wednesday|quarta)/mi', $diaSemana)) $diaSemana = 'Quarta';
		else if (preg_match('/(thursday|quinta)/mi', $diaSemana)) $diaSemana = 'Quinta';
		else if (preg_match('/(friday|sexta)/mi', $diaSemana)) $diaSemana = 'Sexta';
		else if (preg_match('/(saturday|sábado)/mi', $diaSemana)) $diaSemana = 'Sábado';
		return $diaSemana;
	}

	public static function vencimento($data, $DIAS_PGTO = 0)
	{
		$data = date('Y-m-d', strtotime($data . '+ ' . $DIAS_PGTO . ' days'));
		if (self::getDiaDaSemana($data) == ("Sábado")) {
			$data = date('Y-m-d', strtotime($data . ' + 2 days'));
		} else if (self::getDiaDaSemana($data) == ("Domingo")) {
			$data = date('Y-m-d', strtotime($data . '+ 1 days'));
		}
		return $data;
	}

	public static function Dia_Util($data, $DIAS_PGTO = 0)
	{
		$feriados = self::Feriados(date('Y', strtotime($data)));
		$dutil = FALSE;
		while (!$dutil) {
			$data = self::vencimento($data, $DIAS_PGTO);
			if (self::array_value_recursive($data, $feriados) <> NULL) {
				$data = date('Y-m-d', strtotime($data . '+ 1 days'));
				$data = self::vencimento($data, $DIAS_PGTO);
			} else {
				$dutil = TRUE;
			}
		}
		return $data;
	}


	public static function DataPascoa($Ano)
	{
		$Rest = ($Ano % 19) + 1;
		switch ($Rest) {
			case 1:
				$Dia = mktime(0, 0, 0, 4, 14, $Ano);
				break;
			case 2:
				$Dia = mktime(0, 0, 0, 4, 3, $Ano);
				break;
			case 3:
				$Dia = mktime(0, 0, 0, 3, 23, $Ano);
				break;
			case 4:
				$Dia = mktime(0, 0, 0, 4, 11, $Ano);
				break;
			case 5:
				$Dia = mktime(0, 0, 0, 3, 31, $Ano);
				break;
			case 6:
				$Dia = mktime(0, 0, 0, 4, 18, $Ano);
				break;
			case 7:
				$Dia = mktime(0, 0, 0, 4, 8, $Ano);
				break;
			case 8:
				$Dia = mktime(0, 0, 0, 3, 28, $Ano);
				break;
			case 9:
				$Dia = mktime(0, 0, 0, 4, 16, $Ano);
				break;
			case 10:
				$Dia = mktime(0, 0, 0, 4, 5, $Ano);
				break;
			case 11:
				$Dia = mktime(0, 0, 0, 3, 25, $Ano);
				break;
			case 12:
				$Dia = mktime(0, 0, 0, 4, 13, $Ano);
				break;
			case 13:
				$Dia = mktime(0, 0, 0, 4, 2, $Ano);
				break;
			case 14:
				$Dia = mktime(0, 0, 0, 3, 22, $Ano);
				break;
			case 15:
				$Dia = mktime(0, 0, 0, 4, 10, $Ano);
				break;
			case 16:
				$Dia = mktime(0, 0, 0, 3, 30, $Ano);
				break;
			case 17:
				$Dia = mktime(0, 0, 0, 4, 17, $Ano);
				break;
			case 18:
				$Dia = mktime(0, 0, 0, 4, 7, $Ano);
				break;
			case 19:
				$Dia = mktime(0, 0, 0, 3, 27, $Ano);
				break;
		}
		$Ret = "";
		for ($n = 1; $n <= 13; $n++) {
			$Dia += 86400;

			if (date('l', $Dia) == "Sunday") {
				$dd = date('d', $Dia);
				$mm = date('m', $Dia);
				return date('Y-m-d', $Dia);
			}
		}
		return "";
	}

	public static function Feriados($ano)
	{
		$feriados[$ano . '-01-01'] = 'Confraternização Universal';
		$feriados[$ano . '-04-21'] = 'Tiradentes';
		$feriados[$ano . '-05-01'] = 'Dia do Trabalho';
		$feriados[$ano . '-09-07'] = 'Proclamação da Independência';
		$feriados[$ano . '-10-12'] = 'Nossa Srª Aparecida';
		$feriados[$ano . '-11-02'] = 'Finados';
		$feriados[$ano . '-11-15'] = 'Proclamação da República';
		$feriados[$ano . '-12-25'] = 'Natal';

		$pascoa = self::DataPascoa($ano);

		$feriados[date('Y-m-d', strtotime($pascoa . ' - 48 days'))] = 'Segunda de Carnaval';
		$feriados[date('Y-m-d', strtotime($pascoa . ' - 47 days'))] = 'Terça de Carnaval';
		$feriados[date('Y-m-d', strtotime($pascoa . ' - 2 days'))] = 'Sexta-Feira da Paixão';
		$feriados[$pascoa] = 'Páscoa';
		$feriados[date('Y-m-d', strtotime($pascoa . ' + 60 days'))] = 'Corpus Christi';

		return $feriados;
	}

	public static function array_value_recursive($key, array $arr)
	{
		$val = array();
		array_walk_recursive($arr, function ($v, $k) use ($key, &$val) {
			if ($k == $key) array_push($val, $v);
		});
		return count($val) > 1 ? $val : array_pop($val);
	}

	public static function conta_dias_uteis($di, $df)
	{
		$dias_uteis = 0;
		$dc = $di;
		while ($df > $dc) {
			if ($dc == Dia_Util($dc)) $dias_uteis++;
			$do = new DateTime($dc);
			$do->add(new DateInterval("P1D"));
			$dc = $do->format("Y-m-d");
		}
		return $dias_uteis;
	}
	//Aqui aplicando Clean Code
	public static function  calcDataDiaUteis($dataInicial, $diasUteis)
	{
		$dataCorrente = $dataInicial;
		$i = 0;
		while ($i <= $diasUteis) {
			if ($dataCorrente == Dia_Util($dataCorrente)) $i++;
			$dataObjeto = new DateTime($dataCorrente);
			$dataObjeto->add(new DateInterval("P1D"));
			$dataCorrente = $i <= $diasUteis ? $dataObjeto->format("Y-m-d") :  $dataCorrente;
		}
		return $dataCorrente;
	}


	public static function dias_periodo($inicio, $fim, $hoje = null, $descartar_hoje = false)
	{

		$objInicio = new DateTime($inicio);
		$objFim = new DateTime($fim);
		$objHoje = new DateTime();

		$anoInicio = $objInicio->format('Y');
		$anoFim = $objFim->format('Y');
		$feriados = self::dias_feriados($anoInicio);

		while ($anoInicio != $anoFim) {
			$anoInicio++;
			$feriados2 = self::dias_feriados($anoInicio);
			foreach ($feriados2 as $k => $v) {
				$feriados[$k] = $v;
			}
		}

		$uteis = 0;

		$intervalo = new DateInterval('P1D');

		while ($objInicio <= $objFim) {
			$soma = 1;
			$timestamp = $objInicio->getTimestamp();
			$semana = $objInicio->format('N');

			if ($hoje) {
				if ($descartar_hoje) {
					if ($objInicio <= $objHoje) $soma = 0;
				} else {
					if ($objInicio < $objHoje) $soma = 0;
				}
			}

			if (isset($feriados[$timestamp])) $soma = 0; //echo date("d-m-Y",$timestamp);

			if ($semana >= 6) $soma = 0;

			$uteis += $soma;

			$objInicio->add($intervalo);
		}

		return $uteis;
	}


	public static function dias_uteis($mes, $ano, $dia_inicio = null, $descartar_hoje = false)
	{

		$feriados =  self::dias_feriados($ano);
		$uteis = 0;
		// Obtém o número de dias no mês 
		// (http://php.net/manual/en/function.cal-days-in-month.php)
		$dias_no_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

		for ($dia = 1; $dia <= $dias_no_mes; $dia++) {

			if ($dia_inicio) {
				if ($descartar_hoje) {
					if ($dia_inicio >= $dia) continue;
				} else {
					if ($dia_inicio > $dia) continue;
				}
			}

			// Aqui você pode verifica se tem feriado
			// ----------------------------------------
			// Obtém o timestamp
			// (http://php.net/manual/pt_BR/function.mktime.php)
			$timestamp = mktime(0, 0, 0, $mes, $dia, $ano);

			if (isset($feriados[$timestamp])) continue; //echo date("d-m-Y",$timestamp);

			$semana    = date("N", $timestamp);

			if ($semana < 6) $uteis++;
		}

		return $uteis;
	}


	public static function dias_feriados($ano = null)
	{
		if ($ano === null) {
			$ano = intval(date('Y'));
		}

		$pascoa     = easter_date($ano); // Limite entre 1970 a 2037 conforme 

		$dia_pascoa = date('j', $pascoa);
		$mes_pascoa = date('n', $pascoa);
		$ano_pascoa = date('Y', $pascoa);

		$feriados = array(
			// Datas Fixas dos feriados brasileiros
			mktime(0, 0, 0, 1,  1,   $ano) => 'Ano Novo', // Confraternização Universal - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 4,  21,  $ano) => 'Tiradentes', // Tiradentes - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 5,  1,   $ano) => 'Dia do Trabalhador', // Dia do Trabalhador - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 9,  7,   $ano) => 'Independência do Brasil', // Dia da Independência - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 10,  12, $ano) => 'Nossa Senhora Aparecida', // N. S. Aparecida - Lei nº 6802, de 30/06/80
			mktime(0, 0, 0, 11,  2,  $ano) => 'Finados', // Todos os santos - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 11, 15,  $ano) => 'Proclamação da República', // Proclamação da republica - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 12, 25,  $ano) => 'Natal', // Natal - Lei nº 662, de 06/04/49

			// Essas datas dependem da páscoa
			mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48,  $ano_pascoa) => 'Segunda de Carnaval', //2ºferia Carnaval
			mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47,  $ano_pascoa) => 'Terça de Carnaval', //3ºferia Carnaval	
			mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2,  $ano_pascoa) => 'Sexta-feira da Paixão', //6ºfeira Santa  
			mktime(0, 0, 0, $mes_pascoa, $dia_pascoa,  $ano_pascoa) => 'Páscoa', //Pascoa
			mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60,  $ano_pascoa) => 'Corpus Christi', //Corpus Cirist
		);

		ksort($feriados);

		return $feriados;
	}

	public static function trataNumeric($numero)
	{
		$numero = preg_replace("/[^0-9,]/", "", $numero);
		return str_replace(",", ".", $numero);
	}

	public static $estados = array(
		"AC" => "Acre",
		"AL" => "Alagoas",
		"AP" => "Amapá",
		"AM" => "Amazonas",
		"BA" => "Bahia",
		"CE" => "Ceará",
		"DF" => "Distrito Federal",
		"ES" => "Espírito Santo",
		"GO" => "Goiás",
		"MA" => "Maranhão",
		"MT" => "Mato Grosso",
		"MS" => "Mato Grosso do Sul",
		"MG" => "Minas Gerais",
		"PA" => "Pará",
		"PB" => "Paraíba",
		"PR" => "Paraná",
		"PE" => "Pernambuco",
		"PI" => "Piauí",
		"RJ" => "Rio de Janeiro",
		"RN" => "Rio Grande do Norte",
		"RS" => "Rio Grande do Sul",
		"RO" => "Rondônia",
		"RR" => "Roraima",
		"SC" => "Santa Catarina",
		"SP" => "São Paulo",
		"SE" => "Sergipe",
		"TO" => "Tocantins"
	);

	public static function valor2Float($valor)
	{
		$valor = str_replace('.', '', $valor);
		$valor = str_replace(',', '.', $valor);
		return (float) $valor;
	}

	public static function limparNumero($numero)
	{
		return preg_replace('/[^0-9]/', '', $numero);
	}

	public static function mask($val, $mask)
	{
		$maskared = '';
		$k = 0;
		for ($i = 0; $i <= strlen($mask) - 1; $i++) {
			if ($mask[$i] == '#') {
				if (isset($val[$k]))
					$maskared .= $val[$k++];
			} else {
				if (isset($mask[$i]))
					$maskared .= $mask[$i];
			}
		}
		return $maskared;
	}


	public static function validaCNPJ($cnpj)
	{

		if (empty($cnpj)) {
			return false;
		}

		$cnpj = self::limparNumero($cnpj);

		if (strlen($cnpj) != 14)
			return false;

		// Lista de CNPJs inválidos
		$invalidos = [
			'00000000000000',
			'11111111111111',
			'22222222222222',
			'33333333333333',
			'44444444444444',
			'55555555555555',
			'66666666666666',
			'77777777777777',
			'88888888888888',
			'99999999999999'
		];

		// Verifica se o CNPJ está na lista de inválidos
		if (in_array($cnpj, $invalidos))
			return false;

		// Valida primeiro dígito verificador
		for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
			$soma += $cnpj{
				$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		$resto = $soma % 11;
		if ($cnpj{
			12} != ($resto < 2 ? 0 : 11 - $resto))
			return false;
		// Valida segundo dígito verificador
		for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
			$soma += $cnpj{
				$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		$resto = $soma % 11;
		return $cnpj{
			13} == ($resto < 2 ? 0 : 11 - $resto);
	}

	public static function validaCPF($cpf = null)
	{

		if (empty($cpf)) {
			return false;
		}

		$cpf = self::limparNumero($cpf);


		if (strlen($cpf) != 11) {
			return false;
		}
		// Verifica se nenhuma das sequências invalidas abaixo 
		// foi digitada. Caso afirmativo, retorna falso
		else if (
			$cpf == '00000000000' ||
			$cpf == '11111111111' ||
			$cpf == '22222222222' ||
			$cpf == '33333333333' ||
			$cpf == '44444444444' ||
			$cpf == '55555555555' ||
			$cpf == '66666666666' ||
			$cpf == '77777777777' ||
			$cpf == '88888888888' ||
			$cpf == '99999999999'
		) {
			return false;
			// Calcula os digitos verificadores para verificar se o
			// CPF é válido
		} else {

			for ($t = 9; $t < 11; $t++) {

				for ($d = 0, $c = 0; $c < $t; $c++) {
					$d += $cpf{
						$c} * (($t + 1) - $c);
				}
				$d = ((10 * $d) % 11) % 10;
				if ($cpf{
					$c} != $d) {
					return false;
				}
			}

			return true;
		}
	}

	public static function identificaTipoPessoa($cpf_cnpj)
	{

		$cpf_cnpj = Util::limparNumero($cpf_cnpj);

		if (strlen($cpf_cnpj) < 11) {
			self::$erro = 'CPF/CNPJ informado!';
			return false;
		}

		if (strlen($cpf_cnpj) > 18) {
			self::$erro = 'CPF/CNPJ informado!';
			return false;
		}

		if (strlen($cpf_cnpj) > 11) {
			if (!Util::validaCNPJ($cpf_cnpj)) {
				self::$erro = 'CPF/CNPJ informado!';
				return false;
			} else {
				$tipo = 'juridica';
			}
		} else {
			if (!Util::validaCPF($cpf_cnpj)) {
				self::$erro = 'CPF/CNPJ informado!';
				return false;
			} else {
				$tipo = 'fisica';
			}
		}

		return $tipo;
	}

	public static function getController()
	{
		return Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
	}
}
