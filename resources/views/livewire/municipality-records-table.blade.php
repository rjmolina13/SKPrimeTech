<div>
    <x-filament::modal id="municipality-records" width="7xl">
        <x-slot name="heading">
            {{ $chartTitle ?? 'Records' }} - {{ $municipalityName ?? 'Municipality' }}
        </x-slot>

        <div class="mt-4">
            {{ $this->table }}
        </div>
    </x-filament::modal>
</div>
