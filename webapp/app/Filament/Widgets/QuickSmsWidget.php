<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\PlatformAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class QuickSmsWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static ?string $heading = 'SMS rapide';

    protected static string $view = 'filament.widgets.quick-sms-widget';

    // Largeur sur le dashboard
    protected int|string|array $columnSpan = 'full';

    /**
     * State du formulaire (lié à statePath('data')).
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('platform_account_id')
                                       ->label('Compte / SIM')
                                       ->options(fn () => PlatformAccount::query()
                                                                         ->whereHas('platform', fn ($q) => $q->where('code', 'sms'))
                                                                         ->where('is_active', true)
                                                                         ->pluck('name', 'id'))
                                       ->searchable()
                                       ->required()
                                       ->helperText('Compte SMS / SIM utilisé pour ce message.'),

                Forms\Components\TextInput::make('phone')
                                          ->label('Numéro destinataire')
                                          ->placeholder('+33612345678 ou 06XXXXXXXX')
                                          ->required(),

                Forms\Components\Textarea::make('content')
                                         ->label('Message')
                                         ->rows(3)
                                         ->required()
                                         ->autosize()
                                         ->helperText('Le message sera envoyé immédiatement via SMPP.'),
            ])
            ->statePath('data');
    }

    /**
     * Action déclenchée par le submit du formulaire.
     */
    public function send(): void
    {
        $data = $this->form->getState();

        // 1) Récupérer le compte SMS
        /** @var PlatformAccount|null $account */
        $account = PlatformAccount::query()
                                  ->where('id', $data['platform_account_id'] ?? null)
                                  ->whereHas('platform', fn ($q) => $q->where('code', 'sms'))
                                  ->where('is_active', true)
                                  ->first();

        if (! $account) {
            Notification::make()
                        ->title('Compte SMS introuvable')
                        ->body('Le compte / SIM sélectionné n’existe plus ou n’est pas actif.')
                        ->danger()
                        ->send();

            return;
        }

        // 2) Normaliser le numéro pour SMS
        $rawPhone = (string) ($data['phone'] ?? '');
        $remoteId = $this->normalizePhone($rawPhone);

        if (! $remoteId) {
            Notification::make()
                        ->title('Numéro invalide')
                        ->body('Merci de saisir un numéro de téléphone valide.')
                        ->danger()
                        ->send();

            return;
        }

        $content = trim((string) ($data['content'] ?? ''));

        if ($content === '') {
            Notification::make()
                        ->title('Message vide')
                        ->body('Le contenu du message ne peut pas être vide.')
                        ->danger()
                        ->send();

            return;
        }

        // 3) Essayer de rattacher à un contact existant
        $contact = Contact::where('phone_number', $remoteId)->first();

        // 4) Trouver ou créer la conversation
        /** @var Conversation $conversation */
        $conversation = Conversation::firstOrCreate(
            [
                'platform_account_id' => $account->id,
                'contact_id'          => $contact?->id,
            ],
            [
                'external_conversation_id' => $remoteId,
                'remote_user_id'           => $remoteId,
                'remote_username'          => $contact?->first_name
                    ? $contact->first_name . ' ' . $contact->last_name
                    : $remoteId,
                'title'                    => $contact?->first_name
                    ? 'SMS - ' . $contact->first_name . ' ' . $contact->last_name
                    : 'SMS - ' . $remoteId,
                'status'                   => 'open',
            ]
        );

        // 5) Envoyer le message via SmsDriver
        try {
            $driver = $account->driver(); // résolu via PlatformAccount → SmsDriver
            $message = $driver->sendMessage($account, $conversation, $content);

            Notification::make()
                        ->title('SMS envoyé')
                        ->body("Message envoyé à {$remoteId}.")
                        ->success()
                        ->send();

            // Reset du formulaire
            $this->form->fill([
                'platform_account_id' => $data['platform_account_id'],
                'phone'               => null,
                'content'             => null,
            ]);
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                        ->title('Erreur lors de l’envoi')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
        }
    }

    /**
     * Normalise un numéro de téléphone pour SMS (FR → E.164).
     */
    protected function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        // Supprime tout sauf + et chiffres
        $normalized = preg_replace('/[^\d+]/', '', $phone);

        if (! $normalized) {
            return null;
        }

        // Si "06..." → +33...
        if (str_starts_with($normalized, '0') && ! str_starts_with($normalized, '+')) {
            $normalized = '+33' . substr($normalized, 1);
        }

        return $normalized;
    }
}
