<?php

namespace App\Filament\App\Resources\MaintenanceSchedules\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\MaintenanceSchedules\MaintenanceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceSchedule extends EditRecord
{
    protected static string $resource = MaintenanceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}