<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('platform_accounts', function (Blueprint $table) {
            // Numéro de téléphone SMPP associé à la SIM (format E.164 : +336XXXXXXXX).
            // Sert au routage inbound (deliver_sm) et comme fallback expéditeur.
            $table->string('smpp_phone_number')
                  ->nullable()
                  ->after('external_id')
                  ->index()
                  ->comment('SMPP: numéro E.164 associé à la SIM / ligne SMS');

            // Slot physique SMPP/GoIP utilisé pour cette ligne (GoIP4 = 1..4).
            // Permet d’identifier le canal matériel correspondant.
            $table->unsignedTinyInteger('smpp_sim_slot')
                  ->nullable()
                  ->after('smpp_phone_number')
                  ->comment('SMPP: numéro de slot physique sur la passerelle (GoIP4: 1..4)');

            // Identifiant d’expéditeur SMPP (alphanumérique ou numérique).
            // Si présent → TON alphanumérique ; sinon fallback sur smpp_phone_number.
            $table->string('smpp_sender_id')
                  ->nullable()
                  ->after('smpp_sim_slot')
                  ->comment('SMPP: Sender ID utilisé comme expéditeur (alphanum ou numéro)');
        });
    }

    public function down(): void
    {
        Schema::table('platform_accounts', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'sim_slot']);
        });
    }
};
