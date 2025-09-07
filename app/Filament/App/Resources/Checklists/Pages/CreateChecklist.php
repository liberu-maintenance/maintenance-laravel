<?php

namespace App\Filament\App\Resources\Checklists\Pages;

use App\Filament\App\Resources\Checklists\ChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChecklist extends CreateRecord
{
    protected static string $resource = ChecklistResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}