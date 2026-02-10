<?php

namespace App\Containers\AppSection\Page\Providers;

use App\Ship\Parents\Providers\MainServiceProvider as ParentMainServiceProvider;

class MainServiceProvider extends ParentMainServiceProvider
{
    public array $serviceProviders = [
        EventServiceProvider::class,
    ];
}
