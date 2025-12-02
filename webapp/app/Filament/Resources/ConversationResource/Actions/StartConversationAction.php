<?php

namespace App\Filament\Resources\ConversationResource\Actions;

use App\Models\Conversation;
use App\Models\PlatformAccount;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms;

class StartConversationAction
{
    public static function make(): Action
    {
        return Action::make('startConversation')
                     ->label('Nouvelle conversation')
                     ->icon('heroicon-o-chat-bubble-left-right')
                     ->modalHeading('Initier une conversation')
                     ->modalSubmitActionLabel('Envoyer')
                     ->form([
                         Forms\Components\Select::make('platform_account_id')
                                                ->label('Compte / SIM')
                                                ->relationship('platformAccount', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->helperText('Choisis la fonte (plateforme / SIM) pour initier la conversation.'),

                         Forms\Components\TextInput::make('recipient')
                                                   ->label('Destinataire')
                                                   ->placeholder('+33612345678 ou handle')
                                                   ->required(),

                         Forms\Components\TextInput::make('title')
                                                   ->label('Titre interne')
                                                   ->nullable(),

                         Forms\Components\Textarea::make('content')
                                                  ->label('Premier message')
                                                  ->rows(4)
                                                  ->required(),
                     ])
                     ->action(function (array $data): void {

                         /** @var PlatformAccount $account */
                         $account = PlatformAccount::findOrFail($data['platform_account_id']);

                         $rawRecipient = $data['recipient'];
                         $content      = $data['content'];
                         $title        = $data['title'] ?: null;

                         // Normalisation SMS si besoin
                         $remoteId = $rawRecipient;
                         $remoteUsername = $rawRecipient;

                         if ($account->platform?->code === 'sms') {
                             $remoteId = preg_replace('/[^\d+]/', '', $rawRecipient);

                             if (str_starts_with($remoteId, '0') && ! str_starts_with($remoteId, '+')) {
                                 $remoteId = '+33' . substr($remoteId, 1);
                             }

                             $remoteUsername = $remoteId;
                         }

                         // Création conversation
                         $conversation = Conversation::create([
                             'platform_account_id'      => $account->id,
                             'external_conversation_id' => $remoteId,
                             'remote_user_id'           => $remoteId,
                             'remote_username'          => $remoteUsername,
                             'title'                    => $title ?: sprintf('%s - %s',
                                 strtoupper($account->platform?->code ?? 'MSG'),
                                 $remoteUsername
                             ),
                             'status'                   => 'open',
                         ]);

                         // Envoi du premier message
                         try {
                             $driver = $account->driver();
                             $driver->sendMessage($account, $conversation, $content);

                             Notification::make()
                                         ->title('Conversation créée')
                                         ->body('Le premier message a bien été envoyé.')
                                         ->success()
                                         ->send();

                         } catch (\Throwable $e) {
                             report($e);

                             Notification::make()
                                         ->title('Erreur')
                                         ->danger()
                                         ->body($e->getMessage())
                                         ->send();
                         }
                     });
    }
}
