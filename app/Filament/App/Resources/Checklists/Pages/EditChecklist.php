<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Checklists\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Checklists\ChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChecklist extends EditRecord
{
    #[\Override]
    protected static string $resource = ChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
