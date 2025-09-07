<?php

namespace App\Filament\App\Resources\CustomForms\Pages;

use App\Filament\App\Resources\CustomForms\CustomFormResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomForm extends CreateRecord
{
    protected static string $resource = CustomFormResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}