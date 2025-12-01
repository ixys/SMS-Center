<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_account_id')->constrained()->cascadeOnDelete();

            $table->string('external_message_id')->nullable(); // ID message sur la plateforme

            $table->enum('direction', ['inbound', 'outbound']); // Sens du message
            $table->string('from')->nullable(); // handle / numéro émetteur
            $table->string('to')->nullable();   // handle / numéro destinataire

            $table->text('content')->nullable(); // Corps texte
            $table->json('attachments')->nullable(); // Médias, liens, etc.

            $table->string('status')->default('delivered');
            // delivered / pending / failed / read / etc.

            $table->timestamp('sent_at')->nullable(); // Timestamp de la plateforme
            $table->timestamps();

            $table->index(['platform_account_id', 'external_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
