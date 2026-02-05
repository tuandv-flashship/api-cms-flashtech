<?php

namespace App\Containers\AppSection\Member\Middleware;

use App\Containers\AppSection\Member\Values\MemberCsrfToken;
use App\Containers\AppSection\Member\Values\MemberRefreshToken;
use App\Ship\Parents\Middleware\Middleware as ParentMiddleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class VerifyMemberCsrfToken extends ParentMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!MemberCsrfToken::shouldCheck($request)) {
            return $next($request);
        }

        $refreshCookie = MemberRefreshToken::cookieName();
        $hasRefreshToken = (string) $request->cookie($refreshCookie) !== ''
            || (string) $request->input('refresh_token') !== '';

        if (!$hasRefreshToken) {
            return $next($request);
        }

        if (!MemberCsrfToken::isValid($request)) {
            throw ValidationException::withMessages([
                'csrf_token' => 'Invalid CSRF token.',
            ]);
        }

        return $next($request);
    }
}
