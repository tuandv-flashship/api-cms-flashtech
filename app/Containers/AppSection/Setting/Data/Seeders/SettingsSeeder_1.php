<?php

namespace App\Containers\AppSection\Setting\Data\Seeders;

use App\Containers\AppSection\Setting\Tasks\UpsertSettingsTask;
use App\Containers\AppSection\Setting\Models\Setting;
use App\Ship\Parents\Seeders\Seeder as ParentSeeder;

final class SettingsSeeder_1 extends ParentSeeder
{
    public function run(UpsertSettingsTask $task): void
    {
        $force = filter_var(env('FORCE_SETTINGS_SEED', false), FILTER_VALIDATE_BOOLEAN);
        $keys = [
            'admin_email',
            'time_zone',
            'locale_direction',
            'locale',
            'enable_send_error_reporting_via_email',
            'redirect_404_to_homepage',
            'request_log_data_retention_period',
            'audit_log_data_retention_period',
            'phone_number_enable_country_code',
            'phone_number_available_countries',
            'phone_number_min_length',
            'phone_number_max_length',
        ];

        if (!$force && Setting::query()->whereIn('key', $keys)->exists()) {
            return;
        }

        $task->run([
            'admin_email' => json_encode([
                'dvt.soict@gmail.com',
                'dvt.hust@gmail.com',
            ], JSON_THROW_ON_ERROR),
            'time_zone' => 'Asia/Ho_Chi_Minh',
            'locale_direction' => 'ltr',
            'locale' => 'vi',
            'enable_send_error_reporting_via_email' => 0,
            'redirect_404_to_homepage' => 0,
            'request_log_data_retention_period' => 30,
            'audit_log_data_retention_period' => 30,
            'phone_number_enable_country_code' => 1,
            'phone_number_available_countries' => json_encode(['US', 'VN'], JSON_THROW_ON_ERROR),
            'phone_number_min_length' => 8,
            'phone_number_max_length' => 15,
        ]);
    }
}
