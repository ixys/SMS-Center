<x-filament::widget>
    <x-filament::card>
        <form wire:submit.prevent="send" class="space-y-4">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                    Envoyer le SMS
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament::widget>
