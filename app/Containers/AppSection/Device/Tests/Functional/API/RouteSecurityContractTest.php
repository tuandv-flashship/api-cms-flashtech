<?php

namespace App\Containers\AppSection\Device\Tests\Functional\API;

use App\Containers\AppSection\Device\Tests\Functional\ApiTestCase;
use Illuminate\Routing\Route;

final class RouteSecurityContractTest extends ApiTestCase
{
    public function testDeviceRoutesKeepSecurityMiddlewareContract(): void
    {
        $this->assertRouteHasMiddleware('api_member_list_devices', [
            'auth:member',
        ]);

        $this->assertRouteHasMiddleware('api_user_list_devices', [
            'auth:api',
        ]);

        $this->assertRouteHasMiddleware('api_member_list_device_keys', [
            'auth:member',
        ]);

        $this->assertRouteHasMiddleware('api_user_list_device_keys', [
            'auth:api',
        ]);

        $this->assertRouteHasMiddleware('api_member_register_device', [
            'auth:member',
            'request.signature',
            'throttle:' . config('device.throttle.register', '20,1'),
        ]);

        $this->assertRouteHasMiddleware('api_user_register_device', [
            'auth:api',
            'request.signature',
        ]);

        $this->assertRouteHasMiddleware('api_member_update_device', [
            'auth:member',
            'request.signature',
        ]);

        $this->assertRouteHasMiddleware('api_user_update_device', [
            'auth:api',
            'request.signature',
        ]);

        $this->assertRouteHasMiddleware('api_member_revoke_device', [
            'auth:member',
            'request.signature',
            'throttle:' . config('device.throttle.revoke_device', '20,1'),
        ]);

        $this->assertRouteHasMiddleware('api_user_revoke_device', [
            'auth:api',
            'request.signature',
        ]);

        $this->assertRouteHasMiddleware('api_member_revoke_device_key', [
            'auth:member',
            'request.signature',
            'throttle:' . config('device.throttle.revoke_key', '20,1'),
        ]);

        $this->assertRouteHasMiddleware('api_user_revoke_device_key', [
            'auth:api',
            'request.signature',
        ]);

        $this->assertRouteHasMiddleware('api_member_rotate_device_key', [
            'auth:member',
            'request.signature',
            'throttle:' . config('device.throttle.rotate_key', '20,1'),
        ]);

        $this->assertRouteHasMiddleware('api_user_rotate_device_key', [
            'auth:api',
            'request.signature',
        ]);
    }

    /**
     * @param array<int, string> $expectedMiddlewares
     */
    private function assertRouteHasMiddleware(string $routeName, array $expectedMiddlewares): void
    {
        $route = app('router')->getRoutes()->getByName($routeName);
        $this->assertInstanceOf(Route::class, $route, sprintf('Route "%s" is not registered.', $routeName));

        $middlewares = $route->gatherMiddleware();

        foreach ($expectedMiddlewares as $expectedMiddleware) {
            $this->assertContains(
                $expectedMiddleware,
                $middlewares,
                sprintf('Route "%s" must include middleware "%s".', $routeName, $expectedMiddleware),
            );
        }
    }
}
