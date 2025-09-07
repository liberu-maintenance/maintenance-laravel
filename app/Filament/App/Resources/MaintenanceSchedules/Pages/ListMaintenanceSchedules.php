<?php

namespace App\Filament\App\Resources\MaintenanceSchedules\Pages;

use App\Filament\App\Resources\MaintenanceSchedules\MaintenanceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceSchedules extends ListRecords
{
    protected static string $resource = MaintenanceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}