<?php

namespace App\Containers\AppSection\LanguageAdvanced\Traits;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasLanguageTranslations
{
    public static function bootHasLanguageTranslations(): void
    {
        // Cascade delete translations when model is deleted
        static::deleted(function (Model $model): void {
            if (method_exists($model, 'translations')) {
                $model->translations()->delete();
            }
        });
    }

    /**
     * Auto-resolve the translations relationship using naming convention:
     * Translation model class = {ModelClass}Translation (same namespace)
     * Foreign key = {table_name}_id
     *
     * Override getTranslationModelClass() in the model if naming differs.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(
            $this->getTranslationModelClass(),
            LanguageAdvancedManager::getTranslationForeignKey($this),
        );
    }

    /**
     * Auto-translate attributes listed in language-advanced.supported config.
     *
     * When a translatable column is accessed and translations are not yet loaded,
     * this method will automatically lazy-load them (once per model instance).
     *
     * If the model defines an explicit accessor (get{Key}Attribute), it will be
     * called by parent::getAttribute() and this method will NOT auto-translate,
     * avoiding double-translation. Models with custom post-translation logic
     * (e.g. GalleryMeta.getImagesAttribute) should keep their explicit accessor.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Skip auto-translation if this model has an explicit accessor for this key.
        if ($this->hasGetMutator($key)) {
            return $value;
        }

        // Only translate columns present in this model instance AND registered in config.
        if (! array_key_exists($key, $this->attributes)) {
            return $value;
        }

        $columns = LanguageAdvancedManager::getTranslatableColumns($this);
        if (! in_array($key, $columns, true)) {
            return $value;
        }

        // Auto lazy-load translations when needed (once per model instance).
        // Skip for models without a primary key (e.g. aggregate query results).
        if (! $this->relationLoaded('translations') && $this->getKey()) {
            $langCode = LanguageAdvancedManager::getTranslationLocale();

            if ($langCode && ! LanguageAdvancedManager::isDefaultLocale($langCode)) {
                $this->load(['translations' => fn ($q) => $q->where('lang_code', $langCode)]);
            }
        }

        return LanguageAdvancedManager::translateAttribute($this, $key, $value);
    }

    /**
     * Override this method if the translation model class does not follow
     * the {Model}Translation naming convention.
     */
    protected function getTranslationModelClass(): string
    {
        return static::class . 'Translation';
    }

    public function getTranslatedAttribute(string $key, mixed $value): mixed
    {
        return LanguageAdvancedManager::translateAttribute($this, $key, $value);
    }
}

