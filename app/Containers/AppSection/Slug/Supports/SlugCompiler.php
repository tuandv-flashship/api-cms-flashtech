<?php

namespace App\Containers\AppSection\Slug\Supports;

use Carbon\CarbonImmutable;

final class SlugCompiler
{
    /**
     * @return array<string, array{label: string, value: string|int}>
     */
    public function getVariables(): array
    {
        $now = CarbonImmutable::now();

        return [
            '%%year%%' => [
                'label' => 'Current year',
                'value' => $now->year,
            ],
            '%%month%%' => [
                'label' => 'Current month',
                'value' => $now->month,
            ],
            '%%day%%' => [
                'label' => 'Current day',
                'value' => $now->day,
            ],
        ];
    }

    public function compile(?string $prefix): string
    {
        if (!$prefix) {
            return '';
        }

        foreach ($this->getVariables() as $key => $value) {
            $prefix = str_replace($key, (string) $value['value'], $prefix);
        }

        return $prefix;
    }
}
