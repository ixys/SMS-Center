<?php

namespace App\Filament\Resources\SmsMessageResource\Pages;

use App\Filament\Resources\SmsMessageResource;
use App\Models\Contact;
use App\Models\SimCard;
use App\Services\SmsOutboundService;
use Filament\Resources\Pages\CreateRecord;

class CreateSmsMessage extends CreateRecord
{
    protected static string $resource = SmsMessageResource::class;

    /**
     * Commentaire FR :
     * On override la création pour passer par SmsOutboundService
     * au lieu d'un simple SmsMessage::create($data).
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        /** @var SmsOutboundService $service */
        $service = app(SmsOutboundService::class);

        $contact = null;
        $simCard = null;

        if (! empty($data['contact_id'])) {
            $contact = Contact::find($data['contact_id']);
        }

        if (! empty($data['sim_card_id'])) {
            $simCard = SimCard::find($data['sim_card_id']);
        }

        $phoneNumber = $data['phone_number'];
        $body        = $data['body'];

        // On délègue toute la logique au service
        $message = $service->sendOutboundMessage(
            phoneNumber: $phoneNumber,
            body: $body,
            simCard: $simCard,
            contact: $contact,
        );

        return $message;
    }

    /**
     * Optionnel : message de succès plus explicite.
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'SMS mis en file d’envoi via GoIP';
    }
}
