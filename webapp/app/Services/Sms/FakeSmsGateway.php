<?php

namespace App\Services\Sms;

use Carbon\Carbon;

class FakeSmsGateway implements SmsGateway
{
    public function send(string $from, string $to, string $content, array $options = []): array
    {
        // Ici tu peux logguer, pousser dans une table de debug, etc.
        logger()->info('[FakeSmsGateway] SMS envoyé', [
            'from' => $from,
            'to' => $to,
            'content' => $content,
            'options' => $options,
        ]);

        return [
            'external_message_id' => 'fake_' . uniqid(),
            'status' => 'sent',
            'sent_at' => Carbon::now(),
        ];
    }
}
