<?php
class Hash11
{
    // Configurações
    private const HASH_LEN  = 11; // tamanho fixo do hash
    private const ALPH      = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; // base62
    private const BASE      = 62;
    // id2 será armazenado com padding decimal (ex.: 9 => até 999.999.999)
    private const SPLIT_PAD = 9;

    // Chave secreta (use setSecret())
    private static string $secret = 'troque-esta-chave-secreta';

    // ---------- API PÚBLICA ----------

    /** Defina uma chave secreta (use algo longo/aleatório e guarde fora do código) */
    public static function setSecret(string $key): void
    {
        if ($key === '') {
            throw new InvalidArgumentException('Secret não pode ser vazio.');
        }
        self::$secret = $key;
    }

    /** (id1, id2) -> hash base62 de 11 chars (reversível) */
    public static function encodeIds(int $id1, int $id2): string
    {
        if ($id1 < 0 || $id2 < 0) {
            throw new InvalidArgumentException('IDs devem ser >= 0.');
        }
        if ($id2 >= 10 ** self::SPLIT_PAD) {
            throw new InvalidArgumentException('id2 excede '.self::SPLIT_PAD.' dígitos.');
        }

        // 1) concatena decimal: id1 || id2(9 dígitos)
        $dec = (string)$id1 . str_pad((string)$id2, self::SPLIT_PAD, '0', STR_PAD_LEFT);

        // 2) converte para base62 e pad à esquerda para 11
        $b62 = self::decToBase62($dec);
        if (strlen($b62) > self::HASH_LEN) {
            throw new RuntimeException('Número combinado excede o espaço de 11 chars base62.');
        }
        $b62 = str_pad($b62, self::HASH_LEN, self::ALPH[0], STR_PAD_LEFT);

        // 3) aplica deslocamentos por posição conforme a chave
        $shifts = self::deriveShifts(self::$secret);
        return self::shiftEncode($b62, $shifts);
    }

    /** hash base62 (11) -> [id1, id2] */
    public static function decodeHash(string $hash11): array
    {
        if (strlen($hash11) !== self::HASH_LEN) {
            throw new InvalidArgumentException('Hash deve ter exatamente '.self::HASH_LEN.' caracteres.');
        }
        if (preg_match('/[^'.preg_quote(self::ALPH,'/').']/', $hash11)) {
            throw new InvalidArgumentException('Hash contém caractere inválido.');
        }

        // 1) desfaz deslocamentos
        $shifts   = self::deriveShifts(self::$secret);
        $b62plain = self::shiftDecode($hash11, $shifts);

        // 2) remove padding à esquerda (ALPH[0] = '0')
        $b62plain = ltrim($b62plain, self::ALPH[0]);
        if ($b62plain === '') $b62plain = '0';

        // 3) base62 -> decimal
        $dec = self::base62ToDec($b62plain);

        // 4) separa id1 e id2
        $len = strlen($dec);
        if ($len <= self::SPLIT_PAD) {
            $id1 = 0;
            $id2 = (int)$dec;
        } else {
            $id1 = (int) substr($dec, 0, $len - self::SPLIT_PAD);
            $id2 = (int) substr($dec, -self::SPLIT_PAD);
        }
        return [$id1, $id2];
    }

    // ---------- PRIVADO: utilitários ----------

    /** Deriva deslocamentos (0..61) por posição a partir da chave */
    private static function deriveShifts(string $key): array
    {
        $mac = hash_hmac('sha1', 'base62|pad='.self::HASH_LEN, $key, true); // 20 bytes
        $out = [];
        for ($i = 0; $i < self::HASH_LEN; $i++) {
            $out[$i] = ord($mac[$i % strlen($mac)]) % self::BASE;
        }
        return $out;
    }

    /** Converte decimal (string) -> base62 (string) sem BCMath */
    private static function decToBase62(string $dec): string
    {
        $dec = self::strTrimLeadingZeros($dec);
        if (!preg_match('/^\d+$/', $dec)) {
            throw new InvalidArgumentException('Decimal inválido.');
        }
        if ($dec === '0') return '0';

        $res = '';
        while ($dec !== '0') {
            [$dec, $rem] = self::strDivModSmall($dec, self::BASE);
            $res = self::ALPH[$rem] . $res;
        }
        return $res;
    }

    /** Converte base62 (string) -> decimal (string) sem BCMath */
    private static function base62ToDec(string $s): string
    {
        if ($s === '' || preg_match('/[^'.preg_quote(self::ALPH,'/').']/', $s)) {
            throw new InvalidArgumentException('Base62 inválido.');
        }
        $dec = '0';
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $val = strpos(self::ALPH, $s[$i]);  // 0..61
            $dec = self::strMulSmall($dec, self::BASE); // dec *= 62
            $dec = self::strAddSmall($dec, $val);       // dec += val
        }
        return self::strTrimLeadingZeros($dec);
    }

    /** Aplica deslocamentos por posição (encode) */
    private static function shiftEncode(string $plain, array $shifts): string
    {
        $chars = str_split($plain);
        for ($i = 0; $i < count($chars); $i++) {
            $v = strpos(self::ALPH, $chars[$i]);
            $v = ($v + $shifts[$i]) % self::BASE;
            $chars[$i] = self::ALPH[$v];
        }
        return implode('', $chars);
    }

    /** Reverte deslocamentos por posição (decode) */
    private static function shiftDecode(string $cipher, array $shifts): string
    {
        $chars = str_split($cipher);
        for ($i = 0; $i < count($chars); $i++) {
            $v = strpos(self::ALPH, $chars[$i]);
            $v = ($v - $shifts[$i]) % self::BASE;
            if ($v < 0) $v += self::BASE;
            $chars[$i] = self::ALPH[$v];
        }
        return implode('', $chars);
    }

    // ---- Aritmética decimal em string (sem extensões) ----

    private static function strTrimLeadingZeros(string $s): string
    {
        $s = ltrim($s, '0');
        return $s === '' ? '0' : $s;
    }

    private static function strAddSmall(string $dec, int $add): string
    {
        $carry = $add;
        $i = strlen($dec) - 1;
        $out = '';

        while ($i >= 0 || $carry > 0) {
            $d = $i >= 0 ? (ord($dec[$i]) - 48) : 0;
            $sum = $d + ($carry % 10);
            $carry = intdiv($carry, 10);
            if ($sum >= 10) { $sum -= 10; $carry++; }
            $out = chr(48 + $sum) . $out;
            $i--;
        }
        if ($i >= 0) $out = substr($dec, 0, $i + 1) . $out; // (normalmente i<0 aqui)
        return self::strTrimLeadingZeros($out);
    }

    private static function strMulSmall(string $dec, int $mul): string
    {
        if ($mul === 0 || $dec === '0') return '0';
        $carry = 0;
        $out = '';
        for ($i = strlen($dec) - 1; $i >= 0; $i--) {
            $d = ord($dec[$i]) - 48;
            $p = $d * $mul + $carry;
            $out = chr(48 + ($p % 10)) . $out;
            $carry = intdiv($p, 10);
        }
        while ($carry > 0) {
            $out = chr(48 + ($carry % 10)) . $out;
            $carry = intdiv($carry, 10);
        }
        return self::strTrimLeadingZeros($out);
    }

    /** Divide decimal-string por inteiro pequeno; retorna [quociente, resto] */
    private static function strDivModSmall(string $dec, int $div): array
    {
        $quot = '';
        $rem  = 0;
        $len  = strlen($dec);
        for ($i = 0; $i < $len; $i++) {
            $rem = $rem * 10 + (ord($dec[$i]) - 48);
            $q = intdiv($rem, $div);
            $rem = $rem % $div;
            if (!($q === 0 && $quot === '')) $quot .= chr(48 + $q);
        }
        if ($quot === '') $quot = '0';
        return [$quot, $rem];
    }
}
