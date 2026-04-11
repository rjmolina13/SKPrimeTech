<?php

namespace App\Filament\Resources\FormDefinitionResource\Pages;

use App\Filament\Resources\FormDefinitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFormDefinition extends CreateRecord
{
    protected static string $resource = FormDefinitionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['schema'] = FormDefinitionResource::normalizeSchemaForStorage($data['schema'] ?? []);

        return $data;
    }
}
