<?php

namespace App\Containers\AppSection\Device\Providers;

use App\Containers\AppSection\Device\Contracts\TouchDeviceSignatureActivity;
use App\Containers\AppSection\Device\Tasks\TouchDeviceSignatureActivityTask;
use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;

final class DeviceServiceProvider extends ParentServiceProvider
{
    public function register(): void
    {
        parent::register();

        $this->app->bind(TouchDeviceSignatureActivity::class, TouchDeviceSignatureActivityTask::class);
    }
}
