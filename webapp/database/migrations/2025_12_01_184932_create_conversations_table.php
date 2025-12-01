<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('platform_account_id')->constrained()->cascadeOnDelete();

            // IDs distants
            $table->string('external_conversation_id')->nullable(); // ID conversation sur la plateforme
            $table->string('remote_user_id')->nullable(); // ID de l’abonné / contact distant
            $table->string('remote_username')->nullable(); // Pseudo / username distant

            $table->string('title')->nullable(); // Libellé interne : "OF - @fanboy69"
            $table->string('status')->default('open'); // open / closed / blocked...

            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('last_inbound_at')->nullable();
            $table->timestamp('last_outbound_at')->nullable();

            $table->timestamps();

            $table->index(['platform_account_id', 'external_conversation_id']);
            $table->index(['platform_account_id', 'remote_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
