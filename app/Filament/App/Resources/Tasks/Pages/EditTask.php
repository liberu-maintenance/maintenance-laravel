<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Tasks\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Tasks\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    #[\Override]
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
