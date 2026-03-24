<?php

namespace App\Containers\AppSection\Blog\Enums;

enum ContentStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case PENDING = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::PUBLISHED => 'Đã xuất bản',
            self::DRAFT     => 'Nháp',
            self::PENDING   => 'Chờ duyệt',
        };
    }

    /**
     * Options for select fields: ['value' => 'label', ...]
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}
