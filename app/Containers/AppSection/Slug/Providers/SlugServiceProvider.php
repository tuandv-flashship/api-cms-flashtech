<?php

namespace App\Containers\AppSection\Slug\Providers;

use App\Containers\AppSection\Slug\Supports\SlugCompiler;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;

final class SlugServiceProvider extends ParentServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SlugHelper::class, function () {
            return new SlugHelper(new SlugCompiler());
        });
    }
}
