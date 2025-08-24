<?php

namespace App\Filament\App\Resources\Notes\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Notes\NoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNote extends EditRecord
{
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
