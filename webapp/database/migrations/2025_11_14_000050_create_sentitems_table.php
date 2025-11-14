<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Table "sentitems" : SMS effectivement envoyés (historique).
     * Gammu y déplace les messages après envoi et mise à jour des statuts.
     */
    public function up(): void
    {
        Schema::create('sentitems', function (Blueprint $table) {
            // $table->engine = 'InnoDB';

            // Date de dernière mise à jour
            $table->timestamp('UpdatedInDB')->useCurrent()->useCurrentOnUpdate();

            // Date d'insertion dans la base (quand le SMS a été mis en file)
            $table->timestamp('InsertIntoDB')->useCurrent();

            // Date d'envoi effectif
            $table->timestamp('SendingDateTime')->useCurrent();

            // Date de livraison (si rapport reçu)
            $table->timestamp('DeliveryDateTime')->nullable();

            // Texte brut encodé
            $table->text('Text');

            // Numéro destinataire
            $table->string('DestinationNumber', 20)->default('');

            // Type de codage du SMS
            $table->enum('Coding', [
                'Default_No_Compression',
                'Unicode_No_Compression',
                '8bit',
                'Default_Compression',
                'Unicode_Compression',
            ])->default('Default_No_Compression');

            // User Data Header
            $table->text('UDH');

            // Numéro du SMSC
            $table->string('SMSCNumber', 20)->default('');

            // Classe du SMS
            $table->integer('Class')->default(-1);

            // Texte décodé lisible
            $table->text('TextDecoded');

            // Identifiant du message (même ID que dans outbox)
            $table->unsignedInteger('ID')->default(0);

            // Identifiant de la SIM / modem
            $table->string('SenderID', 255);

            // Position de la partie si SMS concaténé
            $table->integer('SequencePosition')->default(1);

            // Statut global de cette partie / message
            $table->enum('Status', [
                'SendingOK',
                'SendingOKNoReport',
                'SendingError',
                'DeliveryOK',
                'DeliveryFailed',
                'DeliveryPending',
                'DeliveryUnknown',
                'Error',
            ])->default('SendingOK');

            // Code d'erreur spécifique
            $table->integer('StatusError')->default(-1);

            // TPMR (message reference) fourni par le réseau
            $table->integer('TPMR')->default(-1);

            // Validité relative
            $table->integer('RelativeValidity')->default(-1);

            // Identifiant du créateur (ex : "laravel", "filament", "gammu-smsd")
            $table->text('CreatorID');

            // Code de statut interne
            $table->integer('StatusCode')->default(-1);

            // Clé primaire composite
            $table->primary(['ID', 'SequencePosition']);

            // Index pour requêtes sur la date de livraison
            $table->index('DeliveryDateTime', 'sentitems_date');

            // Index sur TPMR
            $table->index('TPMR', 'sentitems_tpmr');

            // Index sur le numéro destinataire
            $table->index('DestinationNumber', 'sentitems_dest');

            // Index sur SenderID (modem/SIM)
            $table->index('SenderID', 'sentitems_sender');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentitems');
    }
};
