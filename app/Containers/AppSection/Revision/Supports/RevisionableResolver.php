<?php

namespace App\Containers\AppSection\Revision\Supports;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class RevisionableResolver
{
    public function resolve(string $type): ?string
    {
        $supported = config('revision.supported', []);
        if (! is_array($supported)) {
            return null;
        }

        if (Arr::isAssoc($supported)) {
            if (isset($supported[$type])) {
                return $supported[$type];
            }

            $normalized = Str::lower($type);
            foreach ($supported as $key => $class) {
                if (Str::lower((string) $key) === $normalized) {
                    return $class;
                }
            }
        } else {
            if (in_array($type, $supported, true)) {
                return $type;
            }

            $normalized = Str::lower($type);
            foreach ($supported as $class) {
                if (Str::lower(class_basename($class)) === $normalized) {
                    return $class;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function supportedTypes(): array
    {
        $supported = config('revision.supported', []);
        if (! is_array($supported)) {
            return [];
        }

        if (Arr::isAssoc($supported)) {
            return array_values(array_map('strval', array_keys($supported)));
        }

        return array_values(array_map(static function ($class): string {
            return Str::lower(class_basename($class));
        }, $supported));
    }
}
