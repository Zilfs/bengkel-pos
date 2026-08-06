<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div style="margin-top: 1.5rem">
            <x-filament::actions :actions="$this->getFormActions()" alignment="start" />
        </div>
    </form>
</x-filament-panels::page>
