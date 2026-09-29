<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'event.name', 'value' => ['text' => 'Campus Tour UniNorte 2026'], 'group' => 'event', 'is_public' => true],
            ['key' => 'event.courses', 'value' => ['SI', 'ADS'], 'group' => 'event', 'is_public' => true],
            ['key' => 'privacy.access_logs', 'value' => ['enabled' => true], 'group' => 'privacy', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
