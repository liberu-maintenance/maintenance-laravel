<?php

namespace App\Filament\App\Resources\TaskResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
