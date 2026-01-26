<?php

namespace App\Containers\AppSection\Slug\Supports;

use App\Containers\AppSection\Setting\Models\Setting;
use App\Containers\AppSection\LanguageAdvanced\Models\SlugTranslation;
use App\Containers\AppSection\Slug\Models\Slug;
use App\Containers\AppSection\Slug\Services\SlugService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class SlugHelper
{
    private array $canEmptyPrefixes = [];

    private array $registering = [];

    private array $supportedModels = [];

    private array $prefixes = [];

    public function __construct(private readonly SlugCompiler $translator)
    {
        $this->supportedModels = (array) config('slug.general.supported', []);
        $this->prefixes = (array) config('slug.general.prefixes', []);
    }

    public function registerModule(string|array $model, string|null|Closure $name = null): self
    {
        foreach ((array) $model as $item) {
            $this->supportedModels[$item] = $name ?: $item;
        }

        return $this;
    }

    public function registering(Closure $callback): self
    {
        $this->registering[] = $callback;

        return $this;
    }

    public function removeModule(string|array $model): self
    {
        foreach ((array) $model as $item) {
            unset($this->supportedModels[$item]);
        }

        return $this;
    }

    public function supportedModels(): array
    {
        $this->dispatchRegistering();

        return array_map(static function ($name) {
            return is_callable($name) ? $name() : $name;
        }, $this->supportedModels);
    }

    public function setPrefix(string $model, ?string $prefix, bool $canEmptyPrefix = false): self
    {
        $this->prefixes[$model] = $prefix;

        if ($canEmptyPrefix) {
            $this->canEmptyPrefixes[] = $model;
        }

        return $this;
    }

    public function setColumnUsedForSlugGenerator(string $model, string $column): self
    {
        $columns = (array) config('slug.general.slug_generated_columns', []);
        $columns[$model] = $column;

        config(['slug.general.slug_generated_columns' => $columns]);

        return $this;
    }

    public function isSupportedModel(string $model): bool
    {
        return in_array($model, array_keys($this->supportedModels()), true);
    }

    public function disablePreview(array|string $model): self
    {
        $disabled = (array) config('slug.general.disable_preview', []);

        foreach ((array) $model as $item) {
            $disabled[] = $item;
        }

        config(['slug.general.disable_preview' => array_values(array_unique($disabled))]);

        return $this;
    }

    public function canPreview(string $model): bool
    {
        return ! in_array($model, (array) config('slug.general.disable_preview', []), true);
    }

    public function createSlug(Model $model, ?string $name = null): Slug
    {
        $prefix = $this->getPrefix($model::class);

        $slug = Slug::query()->firstOrNew([
            'reference_type' => $model::class,
            'reference_id' => $model->getKey(),
            'prefix' => $prefix ?? '',
        ]);

        $value = $name ?: $model->{$this->getColumnNameToGenerateSlug($model::class)};
        $service = new SlugService();
        $attempts = 0;
        $maxAttempts = 5;

        while (true) {
            $slug->key = $service->create(
                (string) $value,
                $slug->getKey(),
                $prefix,
                ! $this->turnOffAutomaticUrlTranslationIntoLatin(),
            );

            try {
                $slug->save();
                break;
            } catch (QueryException $exception) {
                if (! $this->isDuplicateKeyException($exception) || $attempts >= $maxAttempts) {
                    throw $exception;
                }

                $attempts++;
            }
        }

        return $slug;
    }

    public function getSlug(
        ?string $key,
        ?string $prefix = null,
        ?string $model = null,
        int|string|null $referenceId = null,
        ?string $langCode = null
    ): ?Slug {
        $condition = [];

        $extension = $this->getPublicSingleEndingURL();

        if ($key !== null) {
            $condition = ['key' => $key];

            if (! empty($extension)) {
                $condition = ['key' => Str::replaceLast($extension, '', $key)];
            }
        }

        if ($model !== null) {
            $condition['reference_type'] = $model;
        }

        if ($referenceId !== null) {
            $condition['reference_id'] = $referenceId;
        }

        if ($prefix !== null) {
            $condition['prefix'] = $prefix;
        }

        $query = Slug::query()->where($condition);

        if ($langCode && isset($condition['key'])) {
            $translation = SlugTranslation::query()
                ->where('lang_code', $langCode)
                ->where('key', $condition['key'])
                ->when(array_key_exists('prefix', $condition), function ($query) use ($condition) {
                    return $query->where('prefix', $condition['prefix']);
                })
                ->first();

            if ($translation) {
                return Slug::query()->whereKey($translation->slugs_id)->first();
            }
        }

        return $query->first();
    }

    public function getPrefix(string $model, string $default = '', bool $translate = true): ?string
    {
        $prefix = $this->getSetting($this->getPermalinkSettingKey($model));

        if ($prefix === null) {
            $this->dispatchRegistering();

            $prefix = Arr::get($this->prefixes, $model);
        }

        if ($prefix !== null && $translate) {
            $prefix = $this->translator->compile($prefix);
        }

        return $prefix ?? $default;
    }

    public function getHelperTextForPrefix(string $model, string $default = '/', bool $translate = true): string
    {
        return $this->getHelperText(
            $this->getPrefix($model, $default, $translate) ?: '',
            Str::slug('your-url-here'),
            '/'
        );
    }

    public function getHelperText(string $prefix, ?string $postfix = '', ?string $separation = ''): string
    {
        $url = ($prefix ? $prefix . $separation : '') . $postfix;

        return ltrim(str_replace('//', '/', $url), '/');
    }

    public function getColumnNameToGenerateSlug(array|string|object|null $model): ?string
    {
        if (!$model) {
            return null;
        }

        if (is_object($model)) {
            $model = $model::class;
        }

        $column = Arr::get((array) config('slug.general.slug_generated_columns', []), $model);

        return $column !== null ? (string) $column : 'name';
    }

    public function getPermalinkSettingKey(string $model): string
    {
        return $this->getSettingKey('permalink-' . Str::slug(str_replace('\\', '_', $model)));
    }

    public function turnOffAutomaticUrlTranslationIntoLatin(): bool
    {
        return (string) $this->getSetting($this->getSettingKey('slug_turn_off_automatic_url_translation_into_latin'), '0') === '1';
    }

    public function getPublicSingleEndingURL(): ?string
    {
        $endingUrl = $this->getSetting(
            $this->getSettingKey('public_single_ending_url'),
            config('slug.general.public_single_ending_url', '')
        );

        return ! empty($endingUrl) ? '.' . ltrim((string) $endingUrl, '.') : null;
    }

    public function getSettingKey(string $key): string
    {
        return $key;
    }

    private function isDuplicateKeyException(QueryException $exception): bool
    {
        $errorInfo = $exception->errorInfo;

        if (is_array($errorInfo) && isset($errorInfo[1]) && (int) $errorInfo[1] === 1062) {
            return true;
        }

        return $exception->getCode() === '23000';
    }

    public function getCanEmptyPrefixes(): array
    {
        return $this->canEmptyPrefixes;
    }

    public function getTranslator(): SlugCompiler
    {
        return $this->translator;
    }

    public function getSlugPrefixes(): array
    {
        $prefixes = [];

        foreach ($this->supportedModels() as $class => $model) {
            $prefixes[] = addslashes((string) $this->getPrefix($class, '', false));
        }

        return array_values(array_filter($prefixes));
    }

    public function getAllPrefixes(): array
    {
        $settingsPrefixes = Setting::query()
            ->where('key', 'like', 'permalink-%')
            ->pluck('value', 'key')
            ->all();

        $prefixes = [];

        foreach ($this->supportedModels() as $class => $model) {
            $normalizedModel = Str::slug(str_replace('\\', '_', $class));

            foreach ($settingsPrefixes as $key => $value) {
                if (! Str::startsWith($key, 'permalink-' . $normalizedModel)) {
                    continue;
                }

                $prefixes[] = $value;
                unset($settingsPrefixes[$key]);
            }

            $prefixes[] = Arr::get($this->prefixes, $class);
        }

        return array_values(array_unique(array_filter($prefixes)));
    }

    private function dispatchRegistering(): void
    {
        if ($this->registering === []) {
            return;
        }

        foreach ($this->registering as $callback) {
            call_user_func($callback, $this);
        }
    }

    private function getSetting(string $key, mixed $default = null): mixed
    {
        $value = Setting::query()
            ->where('key', $key)
            ->value('value');

        return $value === null ? $default : $value;
    }
}
