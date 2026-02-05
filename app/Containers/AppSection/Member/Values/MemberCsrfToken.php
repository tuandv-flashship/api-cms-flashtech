<?php

namespace App\Containers\AppSection\Member\Values;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

final class MemberCsrfToken
{
    private const DEFAULT_COOKIE_NAME = 'memberCsrfToken';
    private const DEFAULT_HEADER_NAME = 'x-csrf-token';

    public static function enabled(): bool
    {
        return (bool) config('member.csrf.enabled', true);
    }

    public static function cookieName(): string
    {
        return (string) config('member.csrf.cookie_name', self::DEFAULT_COOKIE_NAME);
    }

    public static function headerName(): string
    {
        return (string) config('member.csrf.header_name', self::DEFAULT_HEADER_NAME);
    }

    public static function generate(): string
    {
        return Str::random(40);
    }

    public static function createCookie(string $token): Cookie
    {
        return Cookie::create(
            self::cookieName(),
            $token,
            config('appSection-authentication.refresh-tokens-expire-in'),
            config('session.path'),
            config('session.domain'),
            config('session.secure'),
            false,
            false,
            config('session.same_site', 'lax'),
        );
    }

    public static function shouldCheck(Request $request): bool
    {
        return self::enabled()
            && !MemberClientType::isMobile(MemberClientType::fromRequest($request));
    }

    public static function headerValue(Request $request): string|null
    {
        $value = $request->header(self::headerName());

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function cookieValue(Request $request): string|null
    {
        $value = $request->cookie(self::cookieName());

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function isValid(Request $request): bool
    {
        $header = self::headerValue($request);
        $cookie = self::cookieValue($request);

        if (!$header || !$cookie) {
            return false;
        }

        return hash_equals($cookie, $header);
    }
}
