<?php

namespace App\Models;

use App\Models\Base\User as BaseUser;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;
    use HasRoles, HasFactory;

    /**
     * Permet de savoir si l’utilisateur peut accéder à un panel Filament.
     * Ici on accepte tous les utilisateurs, mais tu peux affiner (rôle, email spécifique, etc.)
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // N’autoriser que les admins
        return $this->hasRole('admin');
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
