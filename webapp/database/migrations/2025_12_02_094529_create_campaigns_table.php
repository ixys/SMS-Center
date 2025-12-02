<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->unique();

            // Plateforme / compte utilisé pour la campagne (SIM, OF, X, etc.)
            $table->foreignId('platform_account_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Nom interne de la campagne (affiché dans Filament)
            $table->string('name');

            // Description optionnelle
            $table->text('description')->nullable();

            // Contenu du message (template simple pour V1)
            $table->text('content');

            // Statut global de la campagne
            // draft / scheduled / running / completed / cancelled
            $table->string('status')->default('draft');

            // Date/heure planifiée d’envoi (optionnel)
            $table->timestamp('scheduled_at')->nullable();

            // Stats simples
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
