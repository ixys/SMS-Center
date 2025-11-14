<?php

namespace App\Models;

use App\Models\Base\User as BaseUser;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    /**
     * Permet de savoir si l’utilisateur peut accéder à un panel Filament.
     * Ici on accepte tous les utilisateurs, mais tu peux affiner (rôle, email spécifique, etc.)
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token'
	];
}
