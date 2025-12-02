<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\PlatformAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmsPlatformSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère la plateforme SMS
        $platformSms = Platform::firstOrCreate(
            ['code' => 'sms'],
            [
                'name' => 'SMS',
                'driver_class' => \App\Messaging\Drivers\SmppDriver::class,
                'default_settings' => [],
                'is_active' => true,
            ]
        );

        // Définition des 4 cartes SIM (GoIP4)
        $sims = [
            [
                'name'              => 'SIM 1 – Slot 1',
                'smpp_phone_number' => '+33611111111',
                'smpp_sim_slot'     => 1,
                'smpp_sender_id'    => 'goip01',
            ],
            [
                'name'              => 'SIM 2 – Slot 2',
                'smpp_phone_number' => '+33622222222',
                'smpp_sim_slot'     => 2,
                'smpp_sender_id'    => 'goip02',
            ],
            [
                'name'              => 'SIM 3 – Slot 3',
                'smpp_phone_number' => '+33633333333',
                'smpp_sim_slot'     => 3,
                'smpp_sender_id'    => 'goip03',
            ],
            [
                'name'              => 'SIM 4 – Slot 4',
                'smpp_phone_number' => '+33644444444',
                'smpp_sim_slot'     => 4,
                'smpp_sender_id'    => 'goip04',
            ],
        ];

        foreach ($sims as $sim) {
            PlatformAccount::updateOrCreate(
                [
                    'platform_id'       => $platformSms->id,
                    'smpp_phone_number' => $sim['smpp_phone_number'],
                ],
                [
                    'name'          => $sim['name'],
                    'smpp_sim_slot' => $sim['smpp_sim_slot'],
                    'smpp_sender_id'=> $sim['smpp_sender_id'],

                    // Credentials SMPP ici (optionnel)
                    'credentials'   => [],

                    // Settings généraux SMS
                    'settings' => [
                        'default_number' => $sim['smpp_phone_number'],
                    ],

                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('✔ PlatformAccount (SIM SMPP) seed completed.');
    }
}
