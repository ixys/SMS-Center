<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Tables "contacts" / "contact_groups" / "contact_group_contact"
     * pour gérer les destinataires et leur segmentation.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            // Identifiant interne
            $table->bigIncrements('id');

            // UUID fonctionnel, pratique pour exposer en API
            $table->uuid('uuid')->unique();

            // Nom complet ou alias
            $table->string('name')->nullable();

            // Détail du nom (au cas où)
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            // Numéro principal (format tel brut)
            $table->string('phone_number', 32);

            // Numéro international normalisé (+336...)
            $table->string('international_phone_number', 32)->nullable();

            // Code pays (FR, BE, etc.)
            $table->string('country_code', 4)->nullable();

            // Adresse email éventuelle (utile pour cross-channel)
            $table->string('email')->nullable();

            // Indicateur d'activation du contact
            $table->boolean('is_active')->default(true);

            // Infos additionnelles (tags, préférences, etc.)
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Index fréquemment utilisés
            $table->index('phone_number');
            $table->index('international_phone_number');
            $table->index('country_code');
        });

        Schema::create('contact_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();

            // Nom du groupe (ex: "Clients VIP", "OTP", "Test interne")
            $table->string('name');

            // Description fonctionnelle
            $table->text('description')->nullable();

            // Couleur ou code visuel (pour l'UI)
            $table->string('color', 16)->nullable();

            // Groupe système protégé (non supprimable dans l'UI)
            $table->boolean('is_system')->default(false);

            $table->timestamps();
        });

        Schema::create('contact_group_contact', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->onDelete('cascade');

            $table->foreignId('contact_group_id')
                ->constrained('contact_groups')
                ->onDelete('cascade');

            $table->timestamps();

            // Un contact ne doit pas être dupliqué dans le même groupe
            $table->unique(['contact_id', 'contact_group_id'], 'contact_group_contact_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_group_contact');
        Schema::dropIfExists('contact_groups');
        Schema::dropIfExists('contacts');
    }
};
