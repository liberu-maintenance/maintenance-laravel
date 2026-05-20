<?php

namespace App\Filament\App\Resources\InventoryParts\Pages;

use App\Filament\App\Resources\InventoryParts\InventoryPartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryParts extends ListRecords
{
    protected static string $resource = InventoryPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
