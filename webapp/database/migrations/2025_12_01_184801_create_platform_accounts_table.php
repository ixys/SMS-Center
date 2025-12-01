<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // Nom interne : "Compte OF principal"
            $table->string('external_username')->nullable(); // Handle / pseudo sur la plateforme
            $table->string('external_id')->nullable(); // ID compte sur la plateforme (si dispo)

            // Ex: API keys, tokens, secrets, etc. (idéalement chiffrés via cast)
            $table->json('credentials')->nullable();
            $table->json('settings')->nullable(); // Paramètres spécifiques (webhook URL, options…)

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_accounts');
    }
};
