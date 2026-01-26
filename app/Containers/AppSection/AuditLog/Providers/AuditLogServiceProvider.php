<?php

namespace App\Containers\AppSection\AuditLog\Providers;

use App\Containers\AppSection\AuditLog\Events\AuditHandlerEvent;
use App\Containers\AppSection\AuditLog\Listeners\CustomerLoginListener;
use App\Containers\AppSection\AuditLog\Listeners\CustomerLogoutListener;
use App\Containers\AppSection\AuditLog\Listeners\CustomerRegistrationListener;
use App\Containers\AppSection\AuditLog\Listeners\LoginListener;
use App\Containers\AppSection\AuditLog\Models\AuditHistory;
use App\Containers\AppSection\AuditLog\Supports\AuditLog;
use App\Containers\AppSection\RequestLog\Models\RequestLog;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

final class AuditLogServiceProvider extends ParentServiceProvider
{
    public function boot(): void
    {
        $this->registerAuthListeners();
        $this->registerModelListeners();
        $this->registerScheduler();
    }

    private function registerAuthListeners(): void
    {
        Event::listen(Login::class, [LoginListener::class, 'handle']);
        Event::listen(Login::class, [CustomerLoginListener::class, 'handle']);
        Event::listen(Logout::class, [CustomerLogoutListener::class, 'handle']);
        Event::listen(Registered::class, [CustomerRegistrationListener::class, 'handle']);
    }

    private function registerModelListeners(): void
    {
        Event::listen('eloquent.created: *', function (string $event, array $data): void {
            $this->handleModelEvent('created', $data[0] ?? null);
        });

        Event::listen('eloquent.updated: *', function (string $event, array $data): void {
            $this->handleModelEvent('updated', $data[0] ?? null);
        });

        Event::listen('eloquent.deleted: *', function (string $event, array $data): void {
            $this->handleModelEvent('deleted', $data[0] ?? null);
        });
    }

    private function registerScheduler(): void
    {
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule
                ->command(PruneCommand::class, ['--model' => AuditHistory::class])
                ->dailyAt('00:30');
        });
    }

    private function handleModelEvent(string $action, mixed $model): void
    {
        if (! $model instanceof Model) {
            return;
        }

        if (! $this->shouldLogModel($model)) {
            return;
        }

        $module = $model::class;
        $screen = Str::lower(class_basename($model));
        $referenceName = AuditLog::getReferenceName($screen, $model);

        if ($action === 'updated' && $model instanceof User) {
            $actorId = Auth::guard()->id() ?: Auth::guard('api')->id();
            if ($actorId && (int) $actorId === (int) $model->getKey()) {
                $action = 'has updated his profile';
            }
        }

        $type = match ($action) {
            'created' => 'info',
            'updated', 'has updated his profile' => 'primary',
            'deleted' => 'danger',
            default => 'info',
        };

        event(new AuditHandlerEvent(
            $module,
            $action,
            (string) $model->getKey(),
            $referenceName,
            $type,
        ));
    }

    private function shouldLogModel(Model $model): bool
    {
        $class = $model::class;

        if (Str::startsWith($class, 'App\\Containers\\AppSection\\AuditLog')) {
            return false;
        }

        if ($model instanceof RequestLog || $model instanceof AuditHistory) {
            return false;
        }

        return Str::startsWith($class, 'App\\Containers\\');
    }
}
