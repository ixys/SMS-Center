<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [

            // ---------------------------------------------------------
            // SMS / SMPP — plateforme "maître" pour tes 4 cartes SIM
            // ---------------------------------------------------------
            [
                'code' => 'sms',
                'name' => 'SMS',
                'driver_class' => \App\Messaging\Drivers\SmppDriver::class,
                'default_settings' => [
                    'encoding' => 'gsm7',
                    'max_length' => 160,
                ],
                'is_active' => true,
            ],

            // ---------------------------------------------------------
            // OnlyFans — API non officielle (à implémenter plus tard)
            // ---------------------------------------------------------
            [
                'code' => 'onlyfans',
                'name' => 'OnlyFans',
                'driver_class' => \App\Messaging\Drivers\OnlyFansDriver::class,
                'default_settings' => [
                    'api_base' => 'https://onlyfans.com/api',
                ],
                'is_active' => false,
            ],

            // ---------------------------------------------------------
            // X (Twitter DM)
            // ---------------------------------------------------------
            [
                'code' => 'x',
                'name' => 'X (Twitter)',
                'driver_class' => \App\Messaging\Drivers\XDriver::class,
                'default_settings' => [
                    'api_base' => 'https://api.twitter.com/2',
                ],
                'is_active' => false,
            ],

            // ---------------------------------------------------------
            // Instagram DM
            // ---------------------------------------------------------
            [
                'code' => 'instagram',
                'name' => 'Instagram',
                'driver_class' => \App\Messaging\Drivers\InstagramDriver::class,
                'default_settings' => [
                    'api_base' => 'https://graph.instagram.com',
                ],
                'is_active' => false,
            ],

            // ---------------------------------------------------------
            // WhatsApp (Cloud API)
            // ---------------------------------------------------------
            [
                'code' => 'whatsapp',
                'name' => 'WhatsApp',
                'driver_class' => \App\Messaging\Drivers\WhatsAppDriver::class,
                'default_settings' => [
                    'api_base' => 'https://graph.facebook.com/v19.0',
                ],
                'is_active' => false,
            ],

            // ---------------------------------------------------------
            // Telegram Bot
            // ---------------------------------------------------------
            [
                'code' => 'telegram',
                'name' => 'Telegram',
                'driver_class' => \App\Messaging\Drivers\TelegramDriver::class,
                'default_settings' => [
                    'api_base' => 'https://api.telegram.org/bot',
                ],
                'is_active' => false,
            ],
        ];

        foreach ($platforms as $data) {
            Platform::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'driver_class' => $data['driver_class'],
                    'default_settings' => $data['default_settings'],
                    'is_active' => $data['is_active'],
                ]
            );
        }

        $this->command?->info('✔ Platforms seed completed.');
    }
}
