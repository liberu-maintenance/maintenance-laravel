<?php

namespace App\Filament\App\Resources\Contacts\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Contacts\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
