<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Contacts\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Contacts\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContacts extends ListRecords
{
    #[\Override]
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
