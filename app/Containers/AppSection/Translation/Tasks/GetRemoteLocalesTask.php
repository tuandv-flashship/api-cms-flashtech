<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class GetRemoteLocalesTask extends ParentTask
{
    /**
     * @return array<int, string>
     */
    public function run(): array
    {
        $repository = config('appSection-translation.repository', 'botble/translations');
        $branch = config('appSection-translation.branch', 'develop');

        try {
            $response = Http::withoutVerifying()
                ->asJson()
                ->acceptJson()
                ->get(sprintf('https://api.github.com/repos/%s/git/trees/%s', $repository, $branch));

            $tree = $response->json('tree');
            if (! is_array($tree)) {
                return [];
            }

            $locales = [];
            foreach ($tree as $item) {
                if (! is_array($item)) {
                    continue;
                }

                if (($item['type'] ?? '') !== 'tree') {
                    continue;
                }

                $path = (string) ($item['path'] ?? '');
                if ($path === '' || Str::startsWith($path, 'vendor')) {
                    continue;
                }

                $locales[] = $path;
            }

            $locales = array_values(array_unique($locales));
            sort($locales, SORT_STRING);

            return $locales;
        } catch (Throwable) {
            return [];
        }
    }
}
