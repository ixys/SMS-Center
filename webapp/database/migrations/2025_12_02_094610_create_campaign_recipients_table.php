<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('campaign_uuid')
                  ->constrained('campaigns', 'uuid')
                  ->cascadeOnDelete();

            $table->foreignUuid('contact_uuid')
                  ->constrained('contacts', 'uuid')
                  ->cascadeOnDelete();

            // Lien vers le message généré pour ce destinataire (optionnel tant que non envoyé)
            $table->foreignUuid('message_uuid')
                  ->nullable()
                  ->constrained('messages', 'uuid')
                  ->nullOnDelete();

            // Statut d’envoi pour ce destinataire
            // pending / sending / sent / failed
            $table->string('status')->default('pending');

            $table->text('last_error')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->unique(['campaign_uuid', 'contact_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
    }
};
