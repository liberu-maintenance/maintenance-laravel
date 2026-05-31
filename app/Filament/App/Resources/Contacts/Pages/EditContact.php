<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Contacts\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Contacts\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContact extends EditRecord
{
    #[\Override]
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
