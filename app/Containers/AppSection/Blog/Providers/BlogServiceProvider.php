<?php

namespace App\Containers\AppSection\Blog\Providers;

use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;

/**
 * Blog Service Provider.
 *
 * Events are dispatched from Actions for extensibility.
 * To listen to blog events, register your listeners here:
 *
 * @example
 * Event::listen(PostCreated::class, [YourListener::class, 'handle']);
 * Event::listen(PostPublished::class, [SendNotificationListener::class, 'handle']);
 */
final class BlogServiceProvider extends ParentServiceProvider
{
    public function boot(): void
    {
        // Register your event listeners here
        // Example:
        // Event::listen(PostCreated::class, [YourListener::class, 'handle']);
    }
}
