<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Notes\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Notes\NoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNote extends EditRecord
{
    #[\Override]
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
