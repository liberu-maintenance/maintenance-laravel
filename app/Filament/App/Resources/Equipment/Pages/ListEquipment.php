<?php

namespace App\Filament\App\Resources\Equipment\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Equipment\EquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipment extends ListRecords
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}