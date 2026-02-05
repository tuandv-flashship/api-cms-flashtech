<?php

namespace App\Containers\AppSection\Member\Values;

final class MemberPhoneNormalizer
{
    public static function normalize(string|null $phone): string|null
    {
        if ($phone === null) {
            return null;
        }

        $clean = preg_replace('/[\\s\\-\\(\\)\\.]/', '', $phone);

        if ($clean === '' || $clean === null) {
            return $clean;
        }

        if (str_starts_with($clean, '00')) {
            return '+' . substr($clean, 2);
        }

        if (str_starts_with($clean, '+')) {
            return $clean;
        }

        $defaultCountryCode = (string) config('member.phone.default_country_code', '');
        if ($defaultCountryCode !== '' && preg_match('/^0\\d+$/', $clean)) {
            return '+' . $defaultCountryCode . substr($clean, 1);
        }

        return $clean;
    }
}
