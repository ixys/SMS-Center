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
            // UUID fonctionnel, pratique pour exposer en API
            $table->uuid('uuid')->primary()->unique();

            // Détail du nom (au cas où)
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('name')->virtualAs('concat(first_name, \' \', last_name)');

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
            $table->uuid('uuid')->primary()->unique();

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

            $table->foreignUuid('contact_uuid')
                ->constrained('contacts', 'uuid')
                ->onDelete('cascade');

            $table->foreignUuid('contact_group_uuid')
                ->constrained('contact_groups', 'uuid')
                ->onDelete('cascade');

            $table->timestamps();

            // Un contact ne doit pas être dupliqué dans le même groupe
            $table->unique(['contact_uuid', 'contact_group_uuid'], 'contact_group_contact_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_group_contact');
        Schema::dropIfExists('contact_groups');
        Schema::dropIfExists('contacts');
    }
};
