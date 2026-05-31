<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Equipment\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Equipment\EquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEquipment extends EditRecord
{
    #[\Override]
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
