<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Table "phones" : état des modems / téléphones gérés par Gammu (statut, batterie, réseau...).
     */
    public function up(): void
    {
        Schema::create('phones', function (Blueprint $table) {
            // $table->engine = 'InnoDB';

            // ID symbolique du téléphone (souvent PhoneID de la conf)
            $table->text('ID');

            // Date de dernière mise à jour
            $table->timestamp('UpdatedInDB')->useCurrent()->useCurrentOnUpdate();

            // Date d'insertion de l'enregistrement
            $table->timestamp('InsertIntoDB')->useCurrent();

            // Timeout de communication avec le téléphone
            $table->timestamp('TimeOut')->useCurrent();

            // Autorisation d'envoi ("yes" / "no")
            $table->enum('Send', ['yes', 'no'])->default('no');

            // Autorisation de réception ("yes" / "no")
            $table->enum('Receive', ['yes', 'no'])->default('no');

            // IMEI du téléphone / modem (clé primaire)
            $table->string('IMEI', 35);

            // IMSI de la SIM
            $table->string('IMSI', 35);

            // Code réseau (MCC/MNC)
            $table->string('NetCode', 10)->default('ERROR');

            // Nom du réseau
            $table->string('NetName', 35)->default('ERROR');

            // Client Gammu (ex : "Gammu 1.42.0")
            $table->text('Client');

            // Niveau de batterie (-1 = inconnu)
            $table->integer('Battery')->default(-1);

            // Niveau de signal (-1 = inconnu)
            $table->integer('Signal')->default(-1);

            // Nombre de SMS envoyés
            $table->integer('Sent')->default(0);

            // Nombre de SMS reçus
            $table->integer('Received')->default(0);

            // Clé primaire sur IMEI (un enregistrement par modem)
            $table->primary('IMEI');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
