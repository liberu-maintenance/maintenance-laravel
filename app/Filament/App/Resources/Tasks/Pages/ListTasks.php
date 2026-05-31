<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Tasks\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Tasks\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    #[\Override]
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
