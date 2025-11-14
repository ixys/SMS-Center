<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Commentaire FR :
     * Tables pour sécuriser l'accès API et journaliser les webhooks.
     */
    public function up(): void
    {
        Schema::create('sms_api_keys', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Nom interne (ex : "Backoffice", "Script Cron OTP", etc.)
            $table->string('name');

            // Clé API (à stocker hashée en prod idéalement)
            $table->string('api_key', 128)->unique();

            // Clé active ou non
            $table->boolean('is_active')->default(true);

            // Filtrage IP (liste blanche éventuelle)
            $table->json('allowed_ips')->nullable();

            // Limitation du taux de requêtes (req/min)
            $table->unsignedInteger('rate_limit_per_minute')->nullable();

            // Dernière utilisation
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();
        });

        Schema::create('sms_webhook_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Sens du webhook (incoming = reçu, outgoing = envoyé vers un système tiers)
            $table->enum('direction', ['incoming', 'outgoing']);

            // Event ou type (ex : "delivery_report", "incoming_message", "custom")
            $table->string('event')->nullable();

            // URL cible (pour outgoing) ou URL appelée (pour incoming)
            $table->string('url')->nullable();

            // Charge utile JSON
            $table->json('payload')->nullable();

            // Headers HTTP
            $table->json('headers')->nullable();

            // Code HTTP (si applicable)
            $table->integer('status_code')->nullable();

            // Indicateur de traitement applicatif (OK / KO)
            $table->boolean('is_processed')->default(false);

            // Date de traitement
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['direction', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_webhook_logs');
        Schema::dropIfExists('sms_api_keys');
    }
};
