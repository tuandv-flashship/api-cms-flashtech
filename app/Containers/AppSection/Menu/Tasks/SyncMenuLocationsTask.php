<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Data\Repositories\MenuLocationRepository;
use App\Containers\AppSection\Menu\Models\MenuLocation;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class SyncMenuLocationsTask extends ParentTask
{
    public function __construct(
        private readonly MenuLocationRepository $repository,
    ) {
    }

    /**
     * @param array<int, string> $locations
     */
    public function run(int $menuId, array $locations): void
    {
        $allowedLocations = array_keys((array) config('menu.locations', []));

        $normalized = collect($locations)
            ->filter(fn (mixed $location) => is_string($location) && $location !== '')
            ->map(fn (string $location) => trim($location))
            ->filter(fn (string $location) => in_array($location, $allowedLocations, true))
            ->unique()
            ->values()
            ->all();

        MenuLocation::query()
            ->where('menu_id', $menuId)
            ->whereNotIn('location', $normalized)
            ->delete();

        foreach ($normalized as $location) {
            $this->repository->updateOrCreate(
                ['location' => $location],
                ['menu_id' => $menuId, 'location' => $location],
            );
        }
    }
}
