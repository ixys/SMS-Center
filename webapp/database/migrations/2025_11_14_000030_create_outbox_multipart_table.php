<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Table "outbox_multipart" : parties supplémentaires d'un SMS long (concaténé).
     * Gammu assemble ces parties avec l'enregistrement principal dans "outbox".
     */
    public function up(): void
    {
        Schema::create('outbox_multipart', function (Blueprint $table) {
            // $table->engine = 'InnoDB';

            // Texte brut (encodé) de cette partie
            $table->text('Text')->nullable();

            // Type de codage du SMS
            $table->enum('Coding', [
                'Default_No_Compression',
                'Unicode_No_Compression',
                '8bit',
                'Default_Compression',
                'Unicode_Compression',
            ])->default('Default_No_Compression');

            // User Data Header
            $table->text('UDH')->nullable();

            // Classe du SMS
            $table->integer('Class')->default(-1);

            // Texte décodé de cette partie
            $table->text('TextDecoded')->nullable();

            // ID du SMS principal (référence à outbox.ID, mais sans contrainte FK)
            $table->unsignedInteger('ID')->default(0);

            // Position de cette partie dans le message concaténé
            $table->integer('SequencePosition')->default(1);

            // Statut de cette partie
            $table->enum('Status', [
                'SendingOK',
                'SendingOKNoReport',
                'SendingError',
                'DeliveryOK',
                'DeliveryFailed',
                'DeliveryPending',
                'DeliveryUnknown',
                'Error',
                'Reserved',
            ])->default('Reserved');

            // Code interne d'erreur / statut
            $table->integer('StatusCode')->default(-1);

            // Clé primaire composite (ID du message + position de la partie)
            $table->primary(['ID', 'SequencePosition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_multipart');
    }
};
