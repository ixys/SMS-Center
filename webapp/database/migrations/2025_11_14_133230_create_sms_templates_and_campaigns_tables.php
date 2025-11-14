<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Tables "sms_templates", "sms_campaigns", "sms_campaign_messages"
     * pour industrialiser les envois de SMS.
     */
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Nom interne du template
            $table->string('name');

            // Slug unique (utile pour API / référence code)
            $table->string('slug')->unique();

            // Catégorie fonctionnelle (OTP, marketing, notification...)
            $table->string('category')->nullable();

            // Corps du SMS (avec placeholders éventuels : {{name}}, {{code}}, etc.)
            $table->text('body');

            // Variables attendues par le template
            $table->json('placeholders')->nullable();

            // Template activé/désactivé
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Nom de la campagne
            $table->string('name');

            // Description métier
            $table->text('description')->nullable();

            // Type de campagne
            $table->enum('type', ['bulk', 'transactional', 'notification'])
                ->default('bulk');

            // Statut de la campagne
            $table->enum('status', [
                'draft',
                'scheduled',
                'running',
                'paused',
                'completed',
                'cancelled',
            ])->default('draft');

            // Template utilisé par défaut pour la campagne
            $table->foreignId('sms_template_id')
                ->nullable()
                ->constrained('sms_templates')
                ->nullOnDelete();

            // SIM par défaut à utiliser (peut être null = stratégie auto)
            $table->foreignId('sim_card_id')
                ->nullable()
                ->constrained('sim_cards')
                ->nullOnDelete();

            // Horaires de la campagne
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // Statistiques globales
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('total_sent')->default(0);
            $table->unsignedInteger('total_delivered')->default(0);
            $table->unsignedInteger('total_failed')->default(0);

            // Métadonnées diverses (config de ciblage, etc.)
            $table->json('metadata')->nullable();

            $table->timestamps();
        });

        Schema::create('sms_campaign_messages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('sms_campaign_id')
                ->constrained('sms_campaigns')
                ->onDelete('cascade');

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            // Lien vers sms_messages quand il est créé
            $table->foreignId('sms_message_id')
                ->nullable()
                ->constrained('sms_messages')
                ->nullOnDelete();

            // Numéro cible (copie figée au moment de la campagne)
            $table->string('phone_number', 32);

            // Statut de ce message dans le contexte de la campagne
            $table->enum('status', [
                'pending',   // prévu mais pas encore injecté
                'queued',    // passé en file sms_messages
                'sent',
                'delivered',
                'failed',
                'skipped',   // ignoré (opt-out, doublon, etc.)
            ])->default('pending');

            // Dates d'exécution
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Infos d'erreur éventuelles
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();

            // Payload final utilisé (corps du message après merge template + données)
            $table->text('rendered_body')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_campaign_messages');
        Schema::dropIfExists('sms_campaigns');
        Schema::dropIfExists('sms_templates');
    }
};
