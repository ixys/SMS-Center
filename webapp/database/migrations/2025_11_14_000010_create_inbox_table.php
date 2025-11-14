<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Table "inbox" : SMS reçus par les modems, gérée par Gammu SMSD.
     */
    public function up(): void
    {
        Schema::create('inbox', function (Blueprint $table) {
            // Optionnel : si tu veux expliciter le moteur
            // $table->engine = 'InnoDB';

            // Date de dernière mise à jour de l'enregistrement
            $table->timestamp('UpdatedInDB')->useCurrent()->useCurrentOnUpdate();

            // Date de réception du SMS
            $table->timestamp('ReceivingDateTime')->useCurrent();

            // Texte brut (encodé) du SMS
            $table->text('Text');

            // Numéro de l'expéditeur
            $table->string('SenderNumber', 20)->default('');

            // Type de codage du SMS
            $table->enum('Coding', [
                'Default_No_Compression',
                'Unicode_No_Compression',
                '8bit',
                'Default_Compression',
                'Unicode_Compression',
            ])->default('Default_No_Compression');

            // User Data Header (ex : concaténation de SMS)
            $table->text('UDH');

            // Numéro du SMSC (centrale SMS)
            $table->string('SMSCNumber', 20)->default('');

            // Classe du SMS (pour certains usages spécifiques)
            $table->integer('Class')->default(-1);

            // Texte décodé (UTF-8 lisible)
            $table->text('TextDecoded');

            // Identifiant interne de l'enregistrement
            $table->increments('ID');

            // Identifiant du destinataire (si plusieurs destinataires / routage)
            $table->text('RecipientID');

            // Indique si le message a été traité par ton application
            $table->enum('Processed', ['false', 'true'])->default('false');

            // Code status interne Gammu
            $table->integer('Status')->default(-1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox');
    }
};
