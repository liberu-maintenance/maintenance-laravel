<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Notes\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Notes\NoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotes extends ListRecords
{
    #[\Override]
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
