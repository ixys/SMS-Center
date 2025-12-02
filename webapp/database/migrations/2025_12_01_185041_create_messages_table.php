<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->unique();

            $table->foreignUuid('conversation_uuid')
                  ->constrained('conversations', 'uuid')
                  ->cascadeOnDelete();

            $table->string('external_message_id')->nullable()->index(); // ID message sur la plateforme

            $table->enum('direction', ['inbound', 'outbound']); // Sens du message
            $table->string('from')->nullable(); // handle / numéro émetteur
            $table->string('to')->nullable();   // handle / numéro destinataire

            $table->text('content')->nullable(); // Corps texte
            $table->json('attachments')->nullable(); // Médias, liens, etc.

            $table->string('status')->default('delivered');
            // delivered / pending / failed / read / etc.

            $table->timestamp('sent_at')->nullable(); // Timestamp de la plateforme
            $table->timestamp('received_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_uuid', 'external_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
