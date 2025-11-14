<?php

namespace App\Jobs;

use App\Models\SimCard;
use App\Models\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsViaGoip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $smsMessageId
    ) {
    }

    public function handle(): void
    {
        $message = SmsMessage::find($this->smsMessageId);

        if (! $message) {
            return;
        }

        if ($message->direction !== 'outbound' || $message->status !== 'queued') {
            return;
        }

        $simCard = $message->simCard; // ou logique de choix de SIM

        $queueId = \DB::table('goip_outgoing_queue')->insertGetId([
            'sms_message_id' => $message->id,
            'sim_card_id'    => optional($simCard)->id,
            'phone_number'   => $message->phone_number,
            'body'           => $message->body,
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $message->update([
            'status'   => 'sending',
            'metadata' => array_merge($message->metadata ?? [], [
                'goip_queue_id' => $queueId,
            ]),
        ]);
    }
}
