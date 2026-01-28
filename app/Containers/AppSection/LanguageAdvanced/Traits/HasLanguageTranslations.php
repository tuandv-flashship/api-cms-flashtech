<?php

namespace App\Containers\AppSection\LanguageAdvanced\Traits;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;

trait HasLanguageTranslations
{
    public function getTranslatedAttribute(string $key, mixed $value): mixed
    {
        return LanguageAdvancedManager::translateAttribute($this, $key, $value);
    }
}
