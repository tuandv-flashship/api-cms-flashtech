<?php

namespace App\Containers\AppSection\Slug\Supports;

final class SlugRuntimeServices
{
    private static SlugHelper|null $helper = null;

    public static function helper(): SlugHelper
    {
        if (self::$helper === null) {
            self::$helper = new SlugHelper(new SlugCompiler());
        }

        return self::$helper;
    }

    public static function reset(): void
    {
        self::$helper = null;
    }
}
