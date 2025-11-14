<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Tables "sim_cards", "sms_conversations", "sms_messages"
     * pour structurer fonctionnellement le trafic SMS.
     */
    public function up(): void
    {
        Schema::create('sim_cards', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Nom lisible (ex : "SIM1 OTP", "SIM2 Marketing")
            $table->string('name');

            // SenderID utilisé par Gammu (doit matcher la conf Gammu)
            $table->string('sender_id', 255)->unique();

            // Numéro de la SIM (MSISDN)
            $table->string('phone_number', 32)->nullable();

            // IMEI / IMSI si tu veux remonter l'info des modems
            $table->string('imei', 35)->nullable();
            $table->string('imsi', 35)->nullable();

            // SIM activable/désactivable fonctionnellement
            $table->boolean('is_active')->default(true);

            // Priorité en cas de routage automatique (0 = normal, + haut = prioritaire)
            $table->unsignedTinyInteger('priority')->default(0);

            // Limites (ex : pour éviter le spam par opérateur)
            $table->unsignedInteger('daily_quota')->nullable();
            $table->unsignedInteger('monthly_quota')->nullable();

            // Stratégie d'utilisation
            $table->enum('strategy', ['manual', 'round_robin', 'load_balancing'])
                ->default('manual');

            // Infos additionnelles
            $table->json('metadata')->nullable();

            $table->timestamps();
        });

        Schema::create('sms_conversations', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Contact principal de la conversation (peut être null si numéro inconnu)
            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            // Numéro externe associé à cette conversation (fallback si pas de contact)
            $table->string('phone_number', 32);

            // SIM principale de cette conversation (ex : tu fixes une SIM par contact)
            $table->foreignId('sim_card_id')
                ->nullable()
                ->constrained('sim_cards')
                ->nullOnDelete();

            // Dernier message (pour affichage)
            $table->text('last_message_preview')->nullable();

            // Direction du dernier message (inbound/outbound)
            $table->enum('last_direction', ['inbound', 'outbound'])->nullable();

            // Date du dernier message
            $table->timestamp('last_message_at')->nullable();

            // Nombre de messages entrants non lus
            $table->unsignedInteger('unread_inbound_count')->default(0);

            // Conversation archivée / mutée
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_muted')->default(false);

            // Infos additionnelles (ex : canal, tags, préférences)
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('phone_number');
            $table->index('last_message_at');
        });

        Schema::create('sms_messages', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Lien vers la conversation métier
            $table->foreignId('sms_conversation_id')
                ->nullable()
                ->constrained('sms_conversations')
                ->nullOnDelete();

            // Lien direct au contact (pour requêtes rapides)
            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            // Lien à la SIM qui a envoyé/reçu le message
            $table->foreignId('sim_card_id')
                ->nullable()
                ->constrained('sim_cards')
                ->nullOnDelete();

            // Numéro distant (expéditeur ou destinataire externe)
            $table->string('phone_number', 32);

            // Direction du message
            $table->enum('direction', ['inbound', 'outbound', 'system'])
                ->default('outbound');

            // Statut métier du message
            $table->enum('status', [
                'queued',     // en file d'attente
                'sending',    // en cours d'envoi
                'sent',       // envoyé côté modem
                'delivered',  // accusé de réception OK
                'failed',     // échec
                'received',   // reçu (pour les inbound)
            ])->default('queued');

            // Contenu texte du message
            $table->text('body');

            // Charset / encodage éventuel
            $table->string('charset', 16)->nullable();

            // Données additionnelles (MMS, pièces jointes futures, etc.)
            $table->json('media_urls')->nullable();

            // Référence provider (si un jour tu ajoutes Twilio / autre gateway)
            $table->string('provider_message_id', 191)->nullable();

            // Lien vers les tables Gammu (optionnel mais pratique)

            // ID de "inbox" (SMS entrant récupéré par Gammu)
            $table->unsignedInteger('gammu_inbox_id')->nullable();

            // ID de "outbox" (SMS en file Gammu)
            $table->unsignedInteger('gammu_outbox_id')->nullable();

            // ID de "sentitems" (SMS historisé Gammu)
            $table->unsignedInteger('gammu_sentitems_id')->nullable();

            // Dates métier (programmation / envoi / livraison / échec / lecture)
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('read_at')->nullable();

            // Infos d'erreur détaillées
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();

            // Métadonnées diverses
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Index pour les recherches classiques
            $table->index(['phone_number', 'direction']);
            $table->index('status');
            $table->index('scheduled_at');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
        Schema::dropIfExists('sms_conversations');
        Schema::dropIfExists('sim_cards');
    }
};
