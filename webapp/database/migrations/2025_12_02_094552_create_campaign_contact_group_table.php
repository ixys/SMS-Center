<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_contact_group', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('campaign_uuid')
                  ->constrained('campaigns', 'uuid')
                  ->cascadeOnDelete();

            $table->foreignUuid('contact_group_uuid')
                  ->constrained('contact_groups', 'uuid')
                  ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['campaign_uuid', 'contact_group_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_contact_group');
    }
};
