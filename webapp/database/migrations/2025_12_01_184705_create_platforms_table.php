<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom lisible : "OnlyFans", "X", "SMS"...
            $table->string('code')->unique(); // Code technique : "onlyfans", "x", "sms"
            $table->string('driver_class'); // Classe PHP du driver (ex: App\\Messaging\\Drivers\\OnlyfansDriver)
            $table->json('default_settings')->nullable(); // Paramètres par défaut (config générique)
            $table->boolean('is_active')->default(true); // Activation globale de la plateforme
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
