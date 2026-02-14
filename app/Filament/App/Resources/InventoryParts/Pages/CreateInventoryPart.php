<?php

namespace App\Filament\App\Resources\InventoryParts\Pages;

use App\Filament\App\Resources\InventoryParts\InventoryPartResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryPart extends CreateRecord
{
    protected static string $resource = InventoryPartResource::class;
}
