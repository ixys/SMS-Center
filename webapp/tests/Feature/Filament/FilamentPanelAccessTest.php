<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('allows an admin user to access the Filament panel', function () {
    // Création du rôle admin
    $adminRole = Role::firstOrCreate(['name' => 'admin']);

    // User avec rôle admin
    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole($adminRole);

    // Connexion
    actingAs($user);

    // Accès à la page dashboard du panel admin
    $response = get(route('filament.admin.pages.dashboard'));

    $response->assertOk();
});

it('forbids a non-admin user from accessing the Filament panel', function () {
    // User sans rôle admin
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $response = get(route('filament.admin.pages.dashboard'));

    // Si tu abort(403) dans canAccessPanel()
    $response->assertForbidden();

    // À adapter si tu fais une redirection plutôt :
    // $response->assertRedirect();
});
