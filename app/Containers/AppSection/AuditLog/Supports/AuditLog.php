<?php

namespace App\Containers\AppSection\AuditLog\Supports;

use Illuminate\Database\Eloquent\Model;

final class AuditLog
{
    public static function getReferenceName(string $screen, Model $data): string
    {
        return match ($screen) {
            'user', 'auth' => (string) ($data->name ?? ''),
            default => (string) ($data->name ?? $data->title ?? ($data?->getKey() ? 'ID: ' . $data->id : '')),
        };
    }
}
