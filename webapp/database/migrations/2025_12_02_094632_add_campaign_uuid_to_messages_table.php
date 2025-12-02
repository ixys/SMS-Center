<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignUuid('campaign_uuid')
                  ->nullable()
                  ->after('conversation_uuid')
                  ->constrained('campaigns', 'uuid')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campaign_uuid');
        });
    }
};
