<?php

namespace App\Support;

/**
 * Genera claves temporales seguras y fáciles de dictar o copiar.
 * Formato: XXXX-XXXX-XXXX (sin caracteres ambiguos como 0/O, 1/l/I).
 * Siempre incluye mayúsculas, minúsculas y números.
 */
class ClaveTemporal
{
    private const MAYUS = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    private const MINUS = 'abcdefghijkmnpqrstuvwxyz';
    private const DIGITOS = '23456789';

    public static function generar(): string
    {
        $todos = self::MAYUS . self::MINUS . self::DIGITOS;

        $chars = [
            self::MAYUS[random_int(0, strlen(self::MAYUS) - 1)],
            self::MINUS[random_int(0, strlen(self::MINUS) - 1)],
            self::DIGITOS[random_int(0, strlen(self::DIGITOS) - 1)],
        ];

        while (count($chars) < 12) {
            $chars[] = $todos[random_int(0, strlen($todos) - 1)];
        }

        // Mezcla criptográficamente segura (Fisher-Yates)
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        return implode('-', str_split(implode('', $chars), 4));
    }
}
