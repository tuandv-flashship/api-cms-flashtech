<?php

namespace App\Containers\AppSection\Menu\Tests\Functional;

use App\Containers\AppSection\Menu\Tests\FunctionalTestCase;
use Illuminate\Support\Facades\DB;

class ApiTestCase extends FunctionalTestCase
{
    /**
     * @return array<int, array<string, mixed>>
     */
    protected function captureQueries(callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $callback();
        } finally {
            $queries = DB::getQueryLog();
            DB::disableQueryLog();
            DB::flushQueryLog();
        }

        return $queries;
    }

    /**
     * @param array<int, array<string, mixed>> $queries
     * @param array<int, string> $tables
     */
    protected function countQueriesForTables(array $queries, array $tables): int
    {
        $count = 0;

        foreach ($queries as $query) {
            $sql = isset($query['query']) ? strtolower((string) $query['query']) : '';

            foreach ($tables as $table) {
                if (str_contains($sql, strtolower($table))) {
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * @param array<int, array<string, mixed>> $queries
     */
    protected function countMenuQueries(array $queries): int
    {
        return $this->countQueriesForTables($queries, [
            'menus',
            'menu_locations',
            'menu_nodes',
            'menu_nodes_translations',
        ]);
    }
}
