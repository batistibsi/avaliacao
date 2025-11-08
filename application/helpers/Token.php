<?php
class Token
{

	public static $erro;

	const TOKEN_SECRET = '2025##BEMFEITOSEMPRE'; // .env idealmente

	private static function b64url_enc(string $s): string
	{
		return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
	}
	private static function b64url_dec(string $s): string
	{
		$p = strlen($s) % 4;
		if ($p) $s .= str_repeat('=', 4 - $p);
		return base64_decode(strtr($s, '-_', '+/'));
	}

	public static function gerarToken($id, $ttlSeg = 0)
	{
		$agora = time();
		$ts = $ttlSeg ? ($agora + $ttlSeg) : 0; // 0 = sem expiração
		$payload = $id . ':' . $ts;
		$pay64 = self::b64url_enc($payload);
		$sig = hash_hmac('sha256', $payload, self::TOKEN_SECRET, true);
		$sig64 = self::b64url_enc($sig);
		return $pay64 . '.' . $sig64;
	}

	public static function recuperarId($token)
	{
		if (strpos($token, '.') === false) return null;
		[$pay64, $sig64] = explode('.', $token, 2);
		$payload = self::b64url_dec($pay64);
		$sigBin  = self::b64url_dec($sig64);

		if ($payload === false || $sigBin === false) return null;

		// confere assinatura
		$sigCheck = hash_hmac('sha256', $payload, self::TOKEN_SECRET, true);
		if (!hash_equals($sigCheck, $sigBin)) return null;

		// payload: "id:ts"
		$parts = explode(':', $payload, 2);
		if (count($parts) !== 2) return null;
		[$idStr, $tsStr] = $parts;

		if (!ctype_digit($idStr)) return null;
		$id = (int)$idStr;

		// expiração (0 = sem expiração)
		$ts = ctype_digit($tsStr) ? (int)$tsStr : 0;
		if ($ts > 0 && time() > $ts) return null;

		return $id;
	}

	// Helper para salvar arrays de arquivos
	public static function getToken($nome)
	{
		$db = Zend_Registry::get('db');

		$select = "select a.*
			from gprc_token a
			where a.nome = '" . $nome . "'";

		$registros = $db->fetchAll($select);

		if (count($registros) == 0) {
			self::$erro = "Registro não encontrado.";
			return false;
		} else {
			return $registros[0];
		}
	}


	public static function salvar($nome, $access_token, $expires_in)
	{

		$db = Zend_Registry::get('db');

		$db->beginTransaction();

		$token = self::getToken($nome);

		$data = array(
			"nome" => $nome,
			"access_token" => $access_token,
			"expires_in" => $expires_in
		);

		if ($token)
			$db->update("gprc_token", $data, "nome = '" . $nome . "'");
		else
			$db->insert("gprc_token", $data);

		$db->commit();

		return true;
	}
}
