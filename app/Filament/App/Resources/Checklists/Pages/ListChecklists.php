<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Checklists\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Checklists\ChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChecklists extends ListRecords
{
    #[\Override]
    protected static string $resource = ChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
