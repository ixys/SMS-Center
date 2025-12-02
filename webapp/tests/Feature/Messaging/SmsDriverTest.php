<?php

use App\Messaging\Drivers\SmppDriver;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Platform;
use App\Models\PlatformAccount;
use App\Services\Sms\FakeSmsGateway;
use App\Services\Sms\SmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // On bind le SmsGateway sur le Fake pour éviter d’appeler un vrai SMPP
    $this->app->bind(SmsGateway::class, fn () => new FakeSmsGateway());
});

it('creates a conversation and inbound message via SmsDriver handleIncoming', function () {
    // 1) Plateforme + compte SMS
    $platform = Platform::create([
        'name' => 'SMS',
        'code' => 'sms',
        'driver_class' => \App\Messaging\Drivers\SmppDriver::class,
        'default_settings' => [],
        'is_active' => true,
    ]);

    $account = PlatformAccount::create([
        'platform_id' => $platform->id,
        'name' => 'SMS Prod',
        'external_username' => null,
        'external_id' => null,
        'credentials' => [],
        'settings' => [
            'default_number' => '+33600000001',
        ],
        'is_active' => true,
    ]);

    // 2) Payload d’un SMS entrant (par exemple depuis ton worker SMPP)
    $payload = [
        'from' => '0612345678', // numéro de l’expéditeur (contact)
        'to' => '+33600000001', // ton numéro
        'content' => 'Hello depuis le test',
        'message_id' => 'smpp_123',
        'sent_at' => now()->toIso8601String(),
    ];

    // 3) Driver
    /** @var SmppDriver $driver */
    $driver = app(SmppDriver::class);

    $message = $driver->handleIncoming($account, $payload);

    // 4) Assertions
    expect($message)->toBeInstanceOf(Message::class);

    $this->assertDatabaseHas('conversations', [
        'platform_account_id' => $account->id,
        'external_conversation_id' => '+33612345678', // normalisé par normalizePhone
        'remote_user_id' => '+33612345678',
        'status' => 'open',
    ]);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $message->conversation_id,
        'platform_account_id' => $account->id,
        'external_message_id' => 'smpp_123',
        'direction' => 'inbound',
        'from' => '+33612345678',
        'to' => '+33600000001',
        'content' => 'Hello depuis le test',
        'status' => 'delivered',
    ]);

    // Conversation liée correctement
    $conversation = $message->conversation;
    expect($conversation)->not->toBeNull();
    expect($conversation->last_message_at)->not->toBeNull();
    expect($conversation->last_inbound_at)->not->toBeNull();
});

it('sends an outbound sms and stores the message via SmsDriver sendMessage', function () {
    // 1) Plateforme + compte SMS
    $platform = Platform::create([
        'name' => 'SMS',
        'code' => 'sms',
        'driver_class' => \App\Messaging\Drivers\SmppDriver::class,
        'default_settings' => [],
        'is_active' => true,
    ]);

    $account = PlatformAccount::create([
        'platform_id' => $platform->id,
        'name' => 'SMS Prod',
        'external_username' => null,
        'external_id' => null,
        'credentials' => [
            // si tu utilises credentials['from_number'] au lieu de settings['default_number'],
            // adapte ce que tu veux tester
        ],
        'settings' => [
            'default_number' => '+33600000001',
        ],
        'is_active' => true,
    ]);

    // 2) Conversation existante avec un contact
    $conversation = Conversation::create([
        'platform_account_id' => $account->id,
        'external_conversation_id' => '+33612345678',
        'remote_user_id' => '+33612345678',
        'remote_username' => '+33612345678',
        'title' => 'SMS - +33612345678',
        'status' => 'open',
        'last_message_at' => null,
        'last_inbound_at' => null,
        'last_outbound_at' => null,
    ]);

    /** @var SmppDriver $driver */
    $driver = app(SmppDriver::class);

    // 3) Envoi d’un message
    $content = 'Réponse de test';

    $message = $driver->sendMessage($account, $conversation, $content);

    // 4) Assertions
    expect($message)->toBeInstanceOf(Message::class);
    expect($message->direction)->toBe('outbound');
    expect($message->from)->toBe('+33600000001');
    expect($message->to)->toBe('+33612345678');
    expect($message->content)->toBe($content);
    expect($message->status)->toBe('sent');

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'direction' => 'outbound',
        'content' => 'Réponse de test',
    ]);

    $conversation->refresh();
    expect($conversation->last_message_at)->not->toBeNull();
    expect($conversation->last_outbound_at)->not->toBeNull();
});
