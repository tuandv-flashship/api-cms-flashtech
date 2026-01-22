<?php

namespace App\Containers\AppSection\Translation\Supports;

use App\Ship\Supports\Language;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

final class TranslationFilesystem
{
    public function langPath(string $path = ''): string
    {
        $base = base_path('lang');

        if ($path === '') {
            return $base;
        }

        return $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    public function ensureLangPath(): void
    {
        File::ensureDirectoryExists($this->langPath());
    }

    public function normalizeLocale(string $locale): string
    {
        return str_replace('-', '_', $locale);
    }

    /**
     * @return array<int, string>
     */
    public function listInstalledLocales(): array
    {
        $langPath = $this->langPath();

        if (! File::exists($langPath)) {
            return [];
        }

        $locales = [];

        foreach (File::allFiles($langPath) as $file) {
            $locale = $this->extractLocaleFromPath($file->getPathname());
            if (! $locale) {
                continue;
            }

            $locales[] = $locale;
        }

        $locales = array_values(array_unique($locales));
        sort($locales, SORT_STRING);

        return $locales;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAvailableLocales(array $installedLocales = []): array
    {
        $installedSet = $this->buildLocaleSet($installedLocales);
        $available = Language::getAvailableLocales(true);
        $locales = [];

        foreach ($available as $language) {
            if (! is_array($language)) {
                continue;
            }

            $locale = (string) ($language['locale'] ?? '');
            if ($locale === '') {
                continue;
            }

            $locales[] = [
                'locale' => $locale,
                'code' => $language['code'] ?? $locale,
                'name' => $language['name'] ?? $locale,
                'flag' => $language['flag'] ?? $locale,
                'is_rtl' => (bool) ($language['is_rtl'] ?? false),
                'installed' => isset($installedSet[$this->normalizeLocale($locale)]),
            ];
        }

        usort($locales, static fn (array $left, array $right): int => strcmp((string) ($left['locale'] ?? ''), (string) ($right['locale'] ?? '')));

        return $locales;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapInstalledLocales(array $installedLocales): array
    {
        $available = Language::getAvailableLocales(true);
        $mapped = [];

        foreach ($installedLocales as $locale) {
            $mapped[] = $this->resolveLocaleInfo($locale, $available);
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveLocaleInfo(string $locale, array $availableLocales): array
    {
        $normalized = $this->normalizeLocale($locale);

        foreach ($availableLocales as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            $candidateLocale = (string) ($candidate['locale'] ?? '');
            $candidateCode = (string) ($candidate['code'] ?? '');

            if ($candidateLocale === '' && $candidateCode === '') {
                continue;
            }

            if ($candidateLocale === $locale || $candidateLocale === $normalized || $candidateCode === $locale || $candidateCode === $normalized) {
                return [
                    'locale' => $candidateLocale ?: $locale,
                    'code' => $candidateCode ?: $locale,
                    'name' => $candidate['name'] ?? $locale,
                    'flag' => $candidate['flag'] ?? ($candidateLocale ?: $locale),
                    'is_rtl' => (bool) ($candidate['is_rtl'] ?? false),
                    'installed' => true,
                ];
            }
        }

        return [
            'locale' => $locale,
            'code' => $locale,
            'name' => $locale,
            'flag' => $locale,
            'is_rtl' => false,
            'installed' => true,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function listGroups(string $locale): array
    {
        $langPath = $this->langPath();

        if (! File::exists($langPath)) {
            return [];
        }

        $groups = [];
        $normalized = $this->normalizeLocale($locale);

        foreach (File::allFiles($langPath) as $file) {
            $group = $this->extractGroupFromPath($file->getPathname(), $locale, $normalized);
            if (! $group) {
                continue;
            }

            $groups[] = $group;
        }

        $groups = array_values(array_unique($groups));
        sort($groups, SORT_STRING);

        return $groups;
    }

    /**
     * @return array{group: string, path: string, type: string}
     */
    public function resolveGroup(string $locale, string $group): array
    {
        $group = trim($group);
        if ($group === '') {
            throw new RuntimeException('Group is required.');
        }

        if (Str::contains($group, ['..', '\\'])) {
            throw new RuntimeException('Invalid group path.');
        }

        $normalizedGroup = $group;

        if ($group === 'json') {
            return [
                'group' => $normalizedGroup,
                'path' => $this->langPath($this->normalizeLocale($locale) . '.json'),
                'type' => 'json',
            ];
        }

        if (Str::startsWith($group, 'theme/')) {
            $parts = explode('/', $group);
            if (count($parts) < 2 || trim((string) $parts[1]) === '') {
                throw new RuntimeException('Invalid theme group.');
            }

            $theme = $parts[1];
            $normalizedGroup = 'theme/' . $theme;

            return [
                'group' => $normalizedGroup,
                'path' => $this->langPath('vendor/themes/' . $theme . '/' . $this->normalizeLocale($locale) . '.json'),
                'type' => 'json',
            ];
        }

        if (Str::startsWith($group, 'app/')) {
            $group = Str::after($group, 'app/');
        }

        if (Str::startsWith($group, 'vendor/')) {
            $parts = explode('/', $group);
            if (count($parts) < 3) {
                throw new RuntimeException('Invalid vendor group.');
            }

            $file = array_pop($parts);
            $namespace = implode('/', array_slice($parts, 1));
            if ($file === 'json') {
                return [
                    'group' => 'vendor/' . $namespace . '/json',
                    'path' => $this->langPath('vendor/' . $namespace . '/' . $this->normalizeLocale($locale) . '.json'),
                    'type' => 'json',
                ];
            }

            return [
                'group' => 'vendor/' . $namespace . '/' . $file,
                'path' => $this->langPath('vendor/' . $namespace . '/' . $this->normalizeLocale($locale) . '/' . $file . '.php'),
                'type' => 'php',
            ];
        }

        if (Str::contains($group, '/')) {
            $normalizedGroup = 'app/' . $group;
        } else {
            $normalizedGroup = 'app/' . $group;
        }

        return [
            'group' => $normalizedGroup,
            'path' => $this->langPath($this->normalizeLocale($locale) . '/' . $group . '.php'),
            'type' => 'php',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function readTranslations(string $locale, string $group): array
    {
        $resolved = $this->resolveGroup($locale, $group);

        if ($resolved['type'] === 'json') {
            return $this->readJsonTranslations($resolved['path']);
        }

        $translations = $this->readPhpTranslations($resolved['path']);

        return Arr::dot($translations);
    }

    /**
     * @param array<string, mixed> $translations
     * @return array<string, string>
     */
    public function writeTranslations(string $locale, string $group, array $translations): array
    {
        $resolved = $this->resolveGroup($locale, $group);

        if ($resolved['type'] === 'json') {
            $updated = $this->updateJsonTranslations($resolved['path'], $translations);

            return $updated;
        }

        $updated = $this->updatePhpTranslations($resolved['path'], $translations);

        return Arr::dot($updated);
    }

    public function copyLocaleFromDefault(string $locale): bool
    {
        $this->ensureLangPath();
        $normalized = $this->normalizeLocale($locale);
        $copied = false;

        $defaultPath = $this->langPath('en');
        if (File::isDirectory($defaultPath) && ! File::isDirectory($this->langPath($normalized))) {
            File::copyDirectory($defaultPath, $this->langPath($normalized));
            $copied = true;
        }

        $defaultJson = $this->langPath('en.json');
        if (File::exists($defaultJson) && ! File::exists($this->langPath($normalized . '.json'))) {
            File::copy($defaultJson, $this->langPath($normalized . '.json'));
            $copied = true;
        }

        $vendorPath = $this->langPath('vendor');
        if (File::isDirectory($vendorPath)) {
            foreach (File::allDirectories($vendorPath) as $dir) {
                if (! $this->matchesLocale(basename($dir), 'en')) {
                    continue;
                }

                $destination = dirname($dir) . DIRECTORY_SEPARATOR . $normalized;
                if (! File::isDirectory($destination)) {
                    File::copyDirectory($dir, $destination);
                    $copied = true;
                }
            }

            foreach (File::allFiles($vendorPath) as $file) {
                $basename = $file->getBasename();
                if ($basename !== 'en.json') {
                    continue;
                }

                $destination = dirname($file->getPathname()) . DIRECTORY_SEPARATOR . $normalized . '.json';
                if (! File::exists($destination)) {
                    File::ensureDirectoryExists(dirname($destination));
                    File::copy($file->getPathname(), $destination);
                    $copied = true;
                }
            }
        }

        return $copied;
    }

    /**
     * @return array<int, string>
     */
    public function collectLocaleFiles(string $locale): array
    {
        $langPath = $this->langPath();

        if (! File::exists($langPath)) {
            return [];
        }

        $normalized = $this->normalizeLocale($locale);
        $files = [];

        foreach (File::allFiles($langPath) as $file) {
            $path = $file->getPathname();
            $relative = $this->relativeLangPath($path);
            if ($relative === null) {
                continue;
            }

            $segments = explode('/', $relative);
            $filename = end($segments);

            if ($segments[0] !== 'vendor') {
                if ($filename === $normalized . '.json') {
                    $files[] = $path;
                }

                if (isset($segments[0]) && $this->matchesLocale($segments[0], $normalized)) {
                    $files[] = $path;
                }

                continue;
            }

            if (Str::endsWith($filename, '.json') && $this->matchesLocale(Str::beforeLast($filename, '.json'), $normalized)) {
                $files[] = $path;
                continue;
            }

            if (Str::endsWith($filename, '.php')) {
                foreach ($segments as $segment) {
                    if ($this->matchesLocale($segment, $normalized)) {
                        $files[] = $path;
                        break;
                    }
                }
            }
        }

        $files = array_values(array_unique($files));
        sort($files, SORT_STRING);

        return $files;
    }

    public function deleteLocale(string $locale): void
    {
        $normalized = $this->normalizeLocale($locale);

        if ($normalized === 'en') {
            return;
        }

        $paths = [];

        $baseDir = $this->langPath($normalized);
        if (File::isDirectory($baseDir)) {
            $paths[] = $baseDir;
        }

        $baseJson = $this->langPath($normalized . '.json');
        if (File::exists($baseJson)) {
            $paths[] = $baseJson;
        }

        $vendorPath = $this->langPath('vendor');
        if (File::isDirectory($vendorPath)) {
            foreach (File::allDirectories($vendorPath) as $dir) {
                if ($this->matchesLocale(basename($dir), $normalized)) {
                    $paths[] = $dir;
                }
            }

            foreach (File::allFiles($vendorPath) as $file) {
                $basename = $file->getBasename();
                if ($basename === $normalized . '.json') {
                    $paths[] = $file->getPathname();
                }
            }
        }

        foreach (array_unique($paths) as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
                continue;
            }

            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    /**
     * @param array<int, string> $installedLocales
     * @return array<string, bool>
     */
    private function buildLocaleSet(array $installedLocales): array
    {
        $set = [];

        foreach ($installedLocales as $locale) {
            $normalized = $this->normalizeLocale($locale);
            $set[$normalized] = true;
            $set[$locale] = true;
        }

        return $set;
    }

    private function extractLocaleFromPath(string $path): string|null
    {
        $relative = $this->relativeLangPath($path);
        if ($relative === null) {
            return null;
        }

        $segments = explode('/', $relative);
        $filename = end($segments);

        if ($segments[0] !== 'vendor') {
            if ($filename && Str::endsWith($filename, '.json') && count($segments) === 1) {
                return $this->normalizeLocale(Str::beforeLast($filename, '.json'));
            }

            if ($filename && Str::endsWith($filename, '.php') && count($segments) >= 2) {
                return $this->normalizeLocale($segments[0]);
            }

            return null;
        }

        if ($filename && Str::endsWith($filename, '.json')) {
            return $this->normalizeLocale(Str::beforeLast($filename, '.json'));
        }

        if ($filename && Str::endsWith($filename, '.php')) {
            $availableLocales = Language::getLocaleKeys();
            $availableSet = array_fill_keys($availableLocales, true);

            foreach ($segments as $segment) {
                $normalized = $this->normalizeLocale($segment);
                if (isset($availableSet[$normalized])) {
                    return $normalized;
                }
            }
        }

        return null;
    }

    private function extractGroupFromPath(string $path, string $locale, string $normalized): string|null
    {
        $relative = $this->relativeLangPath($path);
        if ($relative === null) {
            return null;
        }

        $segments = explode('/', $relative);
        $filename = end($segments);

        if ($segments[0] !== 'vendor') {
            if ($filename && Str::endsWith($filename, '.json') && count($segments) === 1) {
                $candidate = Str::beforeLast($filename, '.json');
                return $this->matchesLocale($candidate, $normalized) ? 'json' : null;
            }

            if (! $this->matchesLocale($segments[0] ?? '', $normalized)) {
                return null;
            }

            if ($filename && Str::endsWith($filename, '.php')) {
                $relativeFile = implode('/', array_slice($segments, 1));
                $relativeFile = Str::beforeLast($relativeFile, '.php');
                return 'app/' . $relativeFile;
            }

            return null;
        }

        if ($filename && Str::endsWith($filename, '.json')) {
            $candidate = Str::beforeLast($filename, '.json');
            if (! $this->matchesLocale($candidate, $normalized)) {
                return null;
            }

            $namespace = array_slice($segments, 1, -1);
            if (($namespace[0] ?? '') === 'themes' && isset($namespace[1])) {
                return 'theme/' . $namespace[1];
            }

            return 'vendor/' . implode('/', $namespace) . '/json';
        }

        if ($filename && Str::endsWith($filename, '.php')) {
            $localeIndex = $this->findLocaleIndex($segments, $locale, $normalized);
            if ($localeIndex === null) {
                return null;
            }

            $namespace = array_slice($segments, 1, $localeIndex - 1);
            $groupParts = array_slice($segments, $localeIndex + 1);
            if ($groupParts === []) {
                return null;
            }

            $groupParts[count($groupParts) - 1] = Str::beforeLast($groupParts[count($groupParts) - 1], '.php');

            return 'vendor/' . implode('/', array_merge($namespace, $groupParts));
        }

        return null;
    }

    private function relativeLangPath(string $path): string|null
    {
        $langPath = $this->langPath();
        $langPath = rtrim($langPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (! Str::startsWith($path, $langPath)) {
            return null;
        }

        $relative = Str::after($path, $langPath);

        return str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    }

    /**
     * @return array<string, string>
     */
    private function readJsonTranslations(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $contents = File::get($path);
        $decoded = json_decode($contents, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function readPhpTranslations(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $data = File::getRequire($path);
        if (! is_array($data)) {
            return [];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $incoming
     * @return array<string, string>
     */
    private function updateJsonTranslations(string $path, array $incoming): array
    {
        $existing = $this->readJsonTranslations($path);

        foreach ($incoming as $key => $value) {
            $key = (string) $key;
            if ($value === null) {
                unset($existing[$key]);
                continue;
            }

            $existing[$key] = $value;
        }

        ksort($existing, SORT_STRING);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $existing;
    }

    /**
     * @param array<string, mixed> $incoming
     * @return array<string, mixed>
     */
    private function updatePhpTranslations(string $path, array $incoming): array
    {
        $existing = $this->readPhpTranslations($path);

        foreach ($incoming as $key => $value) {
            $key = (string) $key;
            if ($value === null) {
                Arr::forget($existing, $key);
                continue;
            }

            Arr::set($existing, $key, $value);
        }

        $existing = $this->sortTranslations($existing);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $this->exportPhpArray($existing));

        return $existing;
    }

    /**
     * @param array<string, mixed> $translations
     * @return array<string, mixed>
     */
    private function sortTranslations(array $translations): array
    {
        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                $translations[$key] = $this->sortTranslations($value);
            }
        }

        ksort($translations, SORT_STRING);

        return $translations;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function exportPhpArray(array $data): string
    {
        $exported = var_export($data, true);

        return "<?php\n\nreturn " . $exported . ";\n";
    }

    private function matchesLocale(string $candidate, string $locale): bool
    {
        $normalized = $this->normalizeLocale($locale);
        $candidateNormalized = $this->normalizeLocale($candidate);

        return $candidate === $locale || $candidate === $normalized || $candidateNormalized === $normalized;
    }

    private function findLocaleIndex(array $segments, string $locale, string $normalized): int|null
    {
        foreach ($segments as $index => $segment) {
            if ($this->matchesLocale($segment, $locale) || $this->matchesLocale($segment, $normalized)) {
                return $index;
            }
        }

        return null;
    }

}
