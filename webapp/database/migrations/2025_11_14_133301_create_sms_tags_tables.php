<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Système générique de tags réutilisables sur différentes entités (conversations, campagnes, contacts...).
     */
    public function up(): void
    {
        Schema::create('sms_tags', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Nom lisible du tag (ex : "VIP", "OTP", "Support", "Marketing")
            $table->string('name');

            // Slug unique pour usage technique
            $table->string('slug')->unique();

            // Couleur ou code visuel
            $table->string('color', 16)->nullable();

            // Tag système
            $table->boolean('is_system')->default(false);

            $table->timestamps();
        });

        Schema::create('sms_taggables', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ID du tag
            $table->foreignId('sms_tag_id')
                ->constrained('sms_tags')
                ->onDelete('cascade');

            // Morphs pour cibler diverses entités
            $table->morphs('taggable'); // taggable_id, taggable_type

            $table->timestamps();

            $table->unique(
                ['sms_tag_id', 'taggable_id', 'taggable_type'],
                'sms_taggables_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_taggables');
        Schema::dropIfExists('sms_tags');
    }
};
