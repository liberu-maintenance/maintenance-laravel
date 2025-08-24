<?php

namespace App\Filament\App\Resources\Notes\Pages;

use App\Filament\App\Resources\Notes\NoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNote extends CreateRecord
{
    protected static string $resource = NoteResource::class;
}
