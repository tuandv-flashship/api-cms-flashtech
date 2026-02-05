<?php

namespace App\Containers\AppSection\Member\Providers;

use App\Containers\AppSection\Member\Models\MemberActivityLog;
use App\Ship\Parents\Providers\MainServiceProvider as ParentMainServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Console\PruneCommand;

class MainServiceProvider extends ParentMainServiceProvider
{
    public array $serviceProviders = [
        EventServiceProvider::class,
    ];

    public array $aliases = [
        //
    ];

    public function boot(): void
    {
        $this->registerScheduler();
    }

    private function registerScheduler(): void
    {
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule
                ->command(PruneCommand::class, ['--model' => MemberActivityLog::class])
                ->dailyAt('00:40');
        });
    }
}
