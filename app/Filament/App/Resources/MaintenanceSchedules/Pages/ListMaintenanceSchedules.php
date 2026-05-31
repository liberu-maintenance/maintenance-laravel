<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\MaintenanceSchedules\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\MaintenanceSchedules\MaintenanceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceSchedules extends ListRecords
{
    #[\Override]
    protected static string $resource = MaintenanceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
