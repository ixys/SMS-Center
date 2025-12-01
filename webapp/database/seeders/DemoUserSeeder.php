<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crée le rôle admin s'il n'existe pas
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crée ou met à jour l'utilisateur démo
        $user = User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );

        // Lui attribue le rôle admin
        if (! $user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
