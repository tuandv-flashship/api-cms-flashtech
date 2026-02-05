<?php

namespace App\Containers\AppSection\Member\Values;

use Illuminate\Http\Request;

final class MemberClientType
{
    public const WEB = 'web';
    public const MOBILE = 'mobile';
    private const HEADER_NAME = 'x-client';
    private const QUERY_NAME = 'client';

    public static function fromRequest(Request $request): string
    {
        $header = strtolower((string) $request->header(self::HEADER_NAME, ''));
        $query = strtolower((string) $request->query(self::QUERY_NAME, ''));

        $value = $header !== '' ? $header : ($query !== '' ? $query : self::WEB);

        return in_array($value, [self::WEB, self::MOBILE], true)
            ? $value
            : self::WEB;
    }

    public static function isMobile(string $clientType): bool
    {
        return $clientType === self::MOBILE;
    }
}
