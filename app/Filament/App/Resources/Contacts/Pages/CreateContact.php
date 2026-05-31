<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Contacts\Pages;

use App\Filament\App\Resources\Contacts\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    #[\Override]
    protected static string $resource = ContactResource::class;
}
