<?php

namespace App\Containers\AppSection\Translation\Providers;

use App\Containers\AppSection\Translation\Commands\ImportTranslationsCommand;
use App\Containers\AppSection\Translation\Supports\TranslationLoaderManager;
use Illuminate\Translation\Translator;
use Illuminate\Support\ServiceProvider;

/**
 * Replaces Laravel's default FileLoader with our TranslationLoaderManager
 * and registers the translation import command.
 */
final class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Extend the translator to use our DB-aware loader.
        // This runs when the translator is first resolved (deferred),
        // replacing the FileLoader-based translator with ours.
        $this->app->extend('translator', function (Translator $translator, $app) {
            $loader = new TranslationLoaderManager(
                $app['files'],
                $app['path.lang'],
            );

            $newTranslator = new Translator($loader, $translator->getLocale());
            $newTranslator->setFallback($translator->getFallback());

            return $newTranslator;
        });

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportTranslationsCommand::class,
            ]);
        }
    }
}
