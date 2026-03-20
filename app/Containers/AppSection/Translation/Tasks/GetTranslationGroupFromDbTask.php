<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

final class GetTranslationGroupFromDbTask extends ParentTask
{
    /**
     * @return array{items: list<array{group: string, key: string, en: string|null, value: string|null}>, total: int, page: int, per_page: int, last_page: int}
     */
    public function run(string $locale, ?string $group = null, ?string $search = null, int $page = 1, int $perPage = 20): array
    {
        $query = DB::table('translations as t')
            ->leftJoin('translations as en', function ($join) {
                $join->on('en.group_key', '=', 't.group_key')
                    ->on('en.item_key', '=', 't.item_key')
                    ->where('en.locale', '=', 'en');
            })
            ->where('t.locale', $locale)
            ->select([
                't.group_key as group',
                't.item_key as key',
                'en.value as en',
                't.value as value',
            ]);

        if ($group !== null && $group !== '') {
            $query->where('t.group_key', $group);
        }

        if ($search !== null && $search !== '') {
            $escapedSearch = '%' . addcslashes($search, '%_') . '%';
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('t.item_key', 'LIKE', $escapedSearch)
                    ->orWhere('t.value', 'LIKE', $escapedSearch)
                    ->orWhere('en.value', 'LIKE', $escapedSearch);
            });
        }

        $query->orderBy('t.group_key')->orderBy('t.item_key');

        $total = $query->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $items = $query->offset($offset)->limit($perPage)->get();

        return [
            'items' => $items->map(fn ($row) => [
                'group' => $row->group,
                'key' => $row->key,
                'en' => $row->en,
                'value' => $row->value,
            ])->all(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => $lastPage,
        ];
    }
}
