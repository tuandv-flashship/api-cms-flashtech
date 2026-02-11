<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindMenuByLocationTask extends ParentTask
{
    /**
     * @param array<int, string> $with
     */
    public function run(string $location, array $with = [], bool $publishedOnly = true): Menu
    {
        $query = Menu::query()
            ->with($with)
            ->whereHas('locations', static fn ($locationQuery) => $locationQuery->where('location', $location));

        if ($publishedOnly) {
            $query->published();
        }

        return $query->firstOrFail();
    }
}
