<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Table "gammu" utilisée par Gammu pour stocker la version du schéma.
     */
    public function up(): void
    {
        Schema::create('gammu', function (Blueprint $table) {
            // Version du schéma Gammu (clé primaire)
            $table->integer('Version')->default(0)->primary();
        });

        // On initialise la version à 17 comme dans le script officiel Gammu
        DB::table('gammu')->insert([
            'Version' => 17,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gammu');
    }
};
