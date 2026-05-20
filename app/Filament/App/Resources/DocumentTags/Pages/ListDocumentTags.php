<?php

namespace App\Filament\App\Resources\DocumentTags\Pages;

use App\Filament\App\Resources\DocumentTags\DocumentTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentTags extends ListRecords
{
    protected static string $resource = DocumentTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
