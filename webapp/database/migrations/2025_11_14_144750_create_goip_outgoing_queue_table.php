<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Table tampon pour les SMS à envoyer via GoIP (consommée par un daemon UDP).
     */
    public function up(): void
    {
        Schema::create('goip_outgoing_queue', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Référence au message métier
            $table->foreignId('sms_message_id')
                ->constrained('sms_messages')
                ->onDelete('cascade');

            // SIM à utiliser (slot GoIP) -> map avec sim_cards.sender_id ou channel
            $table->foreignId('sim_card_id')
                ->nullable()
                ->constrained('sim_cards')
                ->nullOnDelete();

            // Numéro destination + texte (copie figée pour l’envoi)
            $table->string('phone_number', 32);
            $table->text('body');

            // Statut dans la file GoIP
            $table->enum('status', [
                'pending',
                'sending',
                'sent',
                'failed',
            ])->default('pending');

            // Dernier message d’erreur éventuel
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goip_outgoing_queue');
    }
};
