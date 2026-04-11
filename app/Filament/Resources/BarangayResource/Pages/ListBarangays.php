<?php

namespace App\Filament\Resources\BarangayResource\Pages;

use App\Filament\Resources\BarangayResource;
use Filament\Resources\Pages\ListRecords;

class ListBarangays extends ListRecords
{
    protected static string $resource = BarangayResource::class;

    protected string $view = 'filament.resources.barangays.pages.list-barangays';

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
        return $this->getResource()::getEloquentQuery()->with('municipality')->orderBy('municipality_id')->get();
    }
}
