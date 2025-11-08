<?php
class Protocolo
{

	public static $erro;

	/**
	 * Cria protocolo numérico a partir de dois IDs.
	 * $widthB = quantos dígitos reservar para $idB (ex.: 9 → até 999.999.999)
	 * Retorna string numérica (pode ficar grande).
	 */
	public static function protocolo_make(int $id1, int $id2): string
	{
		Hash11::setSecret('bemfeito@@##2025');
		return Hash11::encodeIds($id1, $id2);
	}


	/**
	 * Lê/valida protocolo e retorna [idA, idB].
	 * Lança exceção se DV inválido ou formato inconsistente.
	 */
	public static function protocolo_parse(string $protocolo): array
	{
		Hash11::setSecret('bemfeito@@##2025');
		return Hash11::decodeHash($protocolo);
	}

}
