<?php

namespace App\Filament\Resources\SharedAccessLinks\Pages;

use App\Filament\Resources\SharedAccessLinks\SharedAccessLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSharedAccessLinks extends ManageRecords
{
    protected static string $resource = SharedAccessLinkResource::class;

    protected static ?string $title = 'Shared Access Management';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
