<?php

namespace App\Containers\AppSection\Revision\Supports;

final class FieldFormatter
{
    public static function format(string $key, ?string $value, array $formats): ?string
    {
        foreach ($formats as $formatKey => $format) {
            $parts = explode(':', $format);
            if (count($parts) === 1) {
                continue;
            }

            if ($formatKey === $key) {
                $method = array_shift($parts);

                if (method_exists(self::class, $method)) {
                    return self::$method($value, implode(':', $parts));
                }

                break;
            }
        }

        return $value;
    }
}
