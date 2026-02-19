<?php

namespace App\Filament\App\Resources\DocumentTags\Pages;

use App\Filament\App\Resources\DocumentTags\DocumentTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentTag extends EditRecord
{
    protected static string $resource = DocumentTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
