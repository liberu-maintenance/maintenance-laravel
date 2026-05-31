<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Equipment\Pages;

use App\Filament\App\Resources\Equipment\EquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipment extends CreateRecord
{
    #[\Override]
    protected static string $resource = EquipmentResource::class;
}
