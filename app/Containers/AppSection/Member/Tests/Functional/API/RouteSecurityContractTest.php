<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Routing\Route;

final class RouteSecurityContractTest extends ApiTestCase
{
    public function testMemberRoutesKeepSecurityMiddlewareContract(): void
    {
        $this->assertRouteHasMiddleware('api_member_register_member', [
            'throttle:' . config('member.throttle.register', '6,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_login_member', [
            'throttle:' . config('member.throttle.login', '6,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_forgot_password', [
            'throttle:' . config('member.throttle.password_reset', '6,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_reset_password', [
            'throttle:' . config('member.throttle.password_reset', '6,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_refresh_token', [
            'member.csrf',
            'throttle:' . config('member.throttle.refresh', '12,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_logout', [
            'auth:member',
            'throttle:' . config('member.throttle.logout', '20,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_get_profile', [
            'auth:member',
        ]);

        $this->assertRouteHasMiddleware('api_member_update_profile', [
            'auth:member',
            'throttle:' . config('member.throttle.profile_update', '20,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_change_password', [
            'auth:member',
            'throttle:' . config('member.throttle.change_password', '10,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_verify_email', [
            'throttle:' . config('member.throttle.verify_email', '20,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_social_login_redirect', [
            'throttle:' . config('member.throttle.social_redirect', '20,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_social_login_callback', [
            'throttle:' . config('member.throttle.social_callback', '20,1'),
        ]);

        $this->assertRouteHasMiddleware('api_member_get_all_members', [
            'auth:api',
        ]);

        $this->assertRouteHasMiddleware('api_member_find_member_by_id', [
            'auth:api',
        ]);

        $this->assertRouteHasMiddleware('api_member_create_member', [
            'auth:api',
        ]);

        $this->assertRouteHasMiddleware('api_member_update_member', [
            'auth:api',
        ]);

        $this->assertRouteHasMiddleware('api_member_delete_member', [
            'auth:api',
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
