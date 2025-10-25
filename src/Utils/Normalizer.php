
<?php
namespace App\Utils;

final class Normalizer {
    private const MAP = [
        'Š'=>'S','š'=>'s','Đ'=>'Dj','đ'=>'dj','Ž'=>'Z','ž'=>'z','Č'=>'C','č'=>'c','Ć'=>'C','ć'=>'c',
        'À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A','Ā'=>'A','Æ'=>'AE',
        'È'=>'E','É'=>'E','Ê'=>'E','Ë'=>'E','Ě'=>'E','Ē'=>'E','Ę'=>'E','Ė'=>'E',
        'Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','Ī'=>'I','Į'=>'I','İ'=>'I',
        'Ñ'=>'N','Ń'=>'N',
        'Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O','Ō'=>'O','Ø'=>'O','Œ'=>'OE',
        'Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U','Ū'=>'U',
        'Ý'=>'Y','Ÿ'=>'Y',
        'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','ā'=>'a','æ'=>'ae',
        'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ě'=>'e','ē'=>'e','ę'=>'e','ė'=>'e',
        'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ī'=>'i','į'=>'i','ı'=>'i',
        'ñ'=>'n','ń'=>'n',
        'ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ō'=>'o','ø'=>'o','œ'=>'oe',
        'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ū'=>'u',
        'ý'=>'y','ÿ'=>'y'
    ];

    public static function ascii(string $s): string {
        return strtr($s, self::MAP);
    }

    public static function nullIfEmpty($v) {
        if ($v === null) return null;
        if (is_string($v)) {
            $t = trim($v);
            if ($t === '' || strtolower($t) === 'null') return null;
            if (preg_match('/^1900-01-01( |T|$)/', $t)) return null;
            if (preg_match('/^0001-01-01( |T|$)/', $t)) return null;
            return $t;
        }
        if (is_numeric($v)) return $v;
        return $v ?: null;
    }

    public static function dateOrNull(?string $iso): ?string {
        if (!$iso) return null;
        $iso = substr($iso, 0, 10);
        if ($iso === '1900-01-01' || $iso === '0001-01-01') return null;
        return $iso;
    }
}
