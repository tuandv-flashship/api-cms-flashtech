<?php

namespace App\Containers\AppSection\Member\Values;

use App\Ship\Parents\Requests\Request;
use App\Ship\Parents\Values\Value as ParentValue;
use Symfony\Component\HttpFoundation\Cookie;
use Webmozart\Assert\Assert;

final readonly class MemberRefreshToken extends ParentValue
{
    private const COOKIE_NAME = 'memberRefreshToken';

    private function __construct(
        private string $refreshToken,
    ) {
    }

    public static function createFrom(Request $request): self
    {
        return self::create(
            $request->input(
                'refresh_token',
                $request->cookie(self::cookieName()),
            ),
        );
    }

    public static function create(string $refreshToken): self
    {
        Assert::stringNotEmpty($refreshToken);

        return new self($refreshToken);
    }

    public static function cookieName(): string
    {
        return self::COOKIE_NAME;
    }

    public function value(): string
    {
        return $this->refreshToken;
    }

    public function asCookie(): Cookie
    {
        return Cookie::create(
            self::cookieName(),
            $this->refreshToken,
            config('appSection-authentication.refresh-tokens-expire-in'),
            config('session.path'),
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax'),
        );
    }
}
