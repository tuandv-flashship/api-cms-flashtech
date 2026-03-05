<?php

namespace App\Containers\AppSection\Translation\Commands;

use App\Containers\AppSection\Translation\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'translations:import', description: 'Import translations from lang files or botble/translations repo into database')]
final class ImportTranslationsCommand extends Command
{
    protected $signature = 'translations:import
        {--from-repo : Import from botble/translations GitHub repo}
        {--locale=* : Only import specific locale(s)}
        {--fresh : Truncate translations table before importing}
        {--dry-run : Show what would be imported without writing to DB}';

    protected $description = 'Import translations from lang files or botble/translations repo into database';

    private int $imported = 0;

    private int $skipped = 0;

    public function handle(): int
    {
        if ($this->option('fresh') && ! $this->option('dry-run')) {
            $locales = $this->option('locale');
            if ($locales) {
                Translation::query()->whereIn('locale', $locales)->delete();
                $this->info('Cleared translations for locales: ' . implode(', ', $locales));
            } else {
                Translation::query()->truncate();
                $this->info('Cleared all translations.');
            }
        }

        $sourcePath = $this->option('from-repo')
            ? $this->downloadRepo()
            : lang_path();

        if (! $sourcePath || ! File::isDirectory($sourcePath)) {
            $this->error("Source path not found: {$sourcePath}");

            return self::FAILURE;
        }

        $this->importFromPath($sourcePath);

        if ($this->option('from-repo') && $sourcePath !== lang_path()) {
            File::deleteDirectory($sourcePath);
            $this->info('Cleaned up temp directory.');
        }

        $this->newLine();
        $this->info("✅ Done. Imported: {$this->imported}, Skipped: {$this->skipped}");

        return self::SUCCESS;
    }

    private function downloadRepo(): ?string
    {
        $config = config('appSection-translation');
        $repo = $config['repository'] ?? 'botble/translations';
        $branch = $config['branch'] ?? 'develop';

        $tmpDir = sys_get_temp_dir() . '/translations-import-' . uniqid();

        $this->info("Cloning {$repo} ({$branch}) → {$tmpDir}...");

        $result = Process::timeout(60)->run(
            "git clone --depth 1 --branch {$branch} https://github.com/{$repo}.git {$tmpDir}",
        );

        if (! $result->successful()) {
            $this->error('Git clone failed: ' . $result->errorOutput());

            return null;
        }

        $this->info('Clone complete.');

        return $tmpDir;
    }

    private function importFromPath(string $basePath): void
    {
        $localeFilter = $this->option('locale') ?: [];

        // Detect locales from directory structure
        $localeDirs = collect(File::directories($basePath))
            ->map(fn (string $dir): string => basename($dir))
            ->filter(fn (string $name): bool => $name !== 'vendor' && $name !== '.git')
            ->when(
                count($localeFilter) > 0,
                fn ($c) => $c->filter(fn ($l) => in_array($l, $localeFilter)),
            )
            ->values();

        $this->info("Found {$localeDirs->count()} locale(s): " . $localeDirs->implode(', '));

        $bar = $this->output->createProgressBar($localeDirs->count());
        $bar->start();

        foreach ($localeDirs as $locale) {
            $this->importLocale($basePath, $locale);
            $bar->advance();
        }

        $bar->finish();
    }

    private function importLocale(string $basePath, string $locale): void
    {
        $localePath = $basePath . '/' . $locale;
        $rows = [];

        // 1. Import PHP group files
        // Structure can be: {locale}/*.php (local) or {locale}/{locale}/*.php (repo)
        $phpDirs = [$localePath];
        $subLocaleDir = $localePath . '/' . $locale;
        if (File::isDirectory($subLocaleDir)) {
            $phpDirs[] = $subLocaleDir;
        }

        foreach ($phpDirs as $dir) {
            foreach (File::glob($dir . '/*.php') as $file) {
                $group = pathinfo($file, PATHINFO_FILENAME);
                $translations = require $file;

                if (! is_array($translations)) {
                    continue;
                }

                $flattened = Arr::dot($translations);
                foreach ($flattened as $key => $value) {
                    if (! is_string($value)) {
                        continue;
                    }

                    $rows[] = [
                        'locale' => $locale,
                        'group_key' => $group,
                        'item_key' => $key,
                        'value' => $value,
                    ];
                }
            }
        }

        // 2. Import JSON files
        $jsonFiles = [
            $localePath . '/' . $locale . '.json',  // repo structure
            $basePath . '/' . $locale . '.json',     // local structure
        ];

        foreach ($jsonFiles as $jsonFile) {
            if (! File::exists($jsonFile)) {
                continue;
            }

            $json = json_decode(File::get($jsonFile), true);
            if (! is_array($json)) {
                continue;
            }

            foreach ($json as $key => $value) {
                if (! is_string($value)) {
                    continue;
                }

                $rows[] = [
                    'locale' => $locale,
                    'group_key' => '*',
                    'item_key' => $key,
                    'value' => $value,
                ];
            }
        }

        if ($this->option('dry-run')) {
            $this->skipped += count($rows);

            return;
        }

        // Bulk upsert
        $chunks = array_chunk($rows, 500);
        foreach ($chunks as $chunk) {
            DB::table('translations')->upsert(
                array_map(fn ($row) => [...$row, 'created_at' => now(), 'updated_at' => now()], $chunk),
                ['locale', 'group_key', 'item_key'],
                ['value', 'updated_at'],
            );
        }

        $this->imported += count($rows);

        // Flush cache for this locale
        Translation::flushLocaleCache($locale);
    }
}
