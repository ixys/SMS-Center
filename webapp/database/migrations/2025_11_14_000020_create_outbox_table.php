<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Table "outbox" : SMS en file d'attente d'envoi.
     * Gammu lit cette table, envoie les messages et les déplace ensuite dans "sentitems".
     */
    public function up(): void
    {
        Schema::create('outbox', function (Blueprint $table) {
            // $table->engine = 'InnoDB';

            // Date de dernière mise à jour
            $table->timestamp('UpdatedInDB')->useCurrent()->useCurrentOnUpdate();

            // Date d'insertion du SMS dans la file
            $table->timestamp('InsertIntoDB')->useCurrent();

            // Date prévue d'envoi (par défaut maintenant)
            $table->timestamp('SendingDateTime')->useCurrent();

            // Fenêtre horaire d'envoi : ne pas envoyer après cette heure
            $table->time('SendBefore')->default('23:59:59');

            // Fenêtre horaire d'envoi : ne pas envoyer avant cette heure
            $table->time('SendAfter')->default('00:00:00');

            // Texte brut (encodé) du SMS
            $table->text('Text')->nullable();

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
            $table->text('UDH')->nullable();

            // Classe du SMS
            $table->integer('Class')->default(-1);

            // Texte décodé lisible
            $table->text('TextDecoded');

            // Identifiant du message dans la file
            $table->increments('ID');

            // Indique si le SMS fait partie d'un message multipart
            $table->enum('MultiPart', ['false', 'true'])->default('false');

            // Validité relative (durée pendant laquelle l'opérateur tente de livrer)
            $table->integer('RelativeValidity')->default(-1);

            // Identifiant de la SIM / modem (SenderID dans ta conf Gammu)
            $table->string('SenderID', 255)->nullable();

            // Date limite d'envoi avant timeout
            $table->timestamp('SendingTimeOut')->nullable()->useCurrent();

            // Demande de rapport de livraison
            $table->enum('DeliveryReport', ['default', 'yes', 'no'])->default('default');

            // Identifiant du créateur (ex : "laravel", "filament", etc.)
            $table->text('CreatorID');

            // Nombre de tentatives déjà effectuées
            $table->integer('Retries')->default(0);

            // Priorité d'envoi (0 = normal, plus haut = prioritaire)
            $table->integer('Priority')->default(0);

            // Statut du SMS dans la file
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

            // Code de statut interne
            $table->integer('StatusCode')->default(-1);

            // Index pour optimiser les recherches par date
            $table->index(['SendingDateTime', 'SendingTimeOut'], 'outbox_date');

            // Index pour filtrer par SenderID (modem/SIM)
            $table->index('SenderID', 'outbox_sender');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox');
    }
};
