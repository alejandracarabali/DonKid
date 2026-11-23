<?php

namespace App\Support;

/**
 * Códigos de un solo uso basados en el tiempo (TOTP, estándar RFC 6238):
 * el mismo algoritmo de Google Authenticator, Microsoft Authenticator, Authy, etc.
 *
 * El servidor y el celular comparten una clave secreta. Cada 30 segundos ambos
 * calculan, por separado, un código de 6 dígitos a partir de esa clave y la hora.
 * Si coinciden, la persona tiene el celular en la mano.
 */
class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // base32
    private const PERIOD = 30; // segundos por código
    private const DIGITS = 6;

    // Clave nueva: 20 bytes aleatorios, en base32 (lo que se escribe en la app).
    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    // Número de "ventana" de 30 segundos para un momento dado.
    public static function timeStep(?int $timestamp = null): int
    {
        return intdiv($timestamp ?? time(), self::PERIOD);
    }

    public static function codeAt(string $secret, int $step): string
    {
        // El contador va en 8 bytes big-endian (RFC 4226).
        $counter = pack('N2', ($step >> 32) & 0xFFFFFFFF, $step & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $counter, self::base32Decode($secret), true);

        // "Truncamiento dinámico": el último medio byte dice de dónde leer 4 bytes.
        $offset = ord($hash[19]) & 0x0F;
        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | (ord($hash[$offset + 1]) << 16)
            | (ord($hash[$offset + 2]) << 8)
            | ord($hash[$offset + 3]);

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Devuelve la ventana en la que el código es válido, o null si no lo es.
     * Acepta la ventana anterior y la siguiente (±30 s) por si el reloj del celular va un poco corrido.
     * $afterStep evita reusar un código: solo valen ventanas posteriores a la última usada.
     */
    public static function verify(string $secret, string $code, ?int $afterStep = null, ?int $timestamp = null): ?int
    {
        $code = preg_replace('/\s+/', '', $code);
        if (! preg_match('/^\d{6}$/', $code)) {
            return null;
        }

        $now = self::timeStep($timestamp);
        foreach ([$now - 1, $now, $now + 1] as $step) {
            if ($afterStep !== null && $step <= $afterStep) {
                continue;
            }
            // hash_equals: compara sin filtrar información por el tiempo que tarda.
            if (hash_equals(self::codeAt($secret, $step), $code)) {
                return $step;
            }
        }

        return null;
    }

    // Enlace que, tocado en el celular, abre la app autenticadora con la cuenta ya configurada.
    public static function uri(string $secret, string $account, string $issuer = 'DonKid'): string
    {
        return 'otpauth://totp/'.rawurlencode($issuer.':'.$account)
            .'?secret='.$secret.'&issuer='.rawurlencode($issuer).'&period='.self::PERIOD.'&digits='.self::DIGITS;
    }

    // Clave en grupos de 4 para escribirla más fácil a mano: ABCD EFGH ...
    public static function format(string $secret): string
    {
        return trim(chunk_split($secret, 4, ' '));
    }

    public static function base32Encode(string $bytes): string
    {
        $bits = '';
        foreach (str_split($bytes) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $out .= self::ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $out;
    }

    public static function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[\s=]/', '', $secret));

        $bits = '';
        foreach (str_split($secret) as $char) {
            $bits .= str_pad(decbin(strpos(self::ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $out .= chr(bindec($byte));
            }
        }

        return $out;
    }
}
