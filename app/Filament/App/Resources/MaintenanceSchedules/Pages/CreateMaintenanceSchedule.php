<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\MaintenanceSchedules\Pages;

use App\Filament\App\Resources\MaintenanceSchedules\MaintenanceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenanceSchedule extends CreateRecord
{
    #[\Override]
    protected static string $resource = MaintenanceScheduleResource::class;
}
