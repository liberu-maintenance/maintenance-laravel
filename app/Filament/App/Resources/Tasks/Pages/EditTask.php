<?php

namespace App\Filament\App\Resources\TaskResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
