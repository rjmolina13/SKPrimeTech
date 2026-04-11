<?php

namespace App\Filament\Resources\MunicipalityResource\Pages;

use App\Filament\Resources\MunicipalityResource;
use Filament\Resources\Pages\ListRecords;

class ListMunicipalities extends ListRecords
{
    protected static string $resource = MunicipalityResource::class;

    protected string $view = 'filament.resources.municipalities.pages.list-municipalities';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('print')
                ->label('Print List')
                ->icon('heroicon-o-printer')
                ->action(fn () => $this->dispatch('open-modal', id: 'print-modal')),
            \Filament\Actions\CreateAction::make(),
        ];
    }

    public function getPrintData()
    {
        return $this->getResource()::getEloquentQuery()->withCount('barangays')->orderBy('name')->get();
    }
}
