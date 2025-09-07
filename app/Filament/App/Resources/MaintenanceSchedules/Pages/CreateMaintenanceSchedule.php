<?php

namespace App\Filament\App\Resources\MaintenanceSchedules\Pages;

use App\Filament\App\Resources\MaintenanceSchedules\MaintenanceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenanceSchedule extends CreateRecord
{
    protected static string $resource = MaintenanceScheduleResource::class;
}