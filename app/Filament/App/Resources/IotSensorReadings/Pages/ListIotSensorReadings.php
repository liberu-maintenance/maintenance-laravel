<?php

namespace App\Filament\App\Resources\IotSensorReadings\Pages;

use App\Filament\App\Resources\IotSensorReadings\IotSensorReadingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListIotSensorReadings extends ListRecords
{
    protected static string $resource = IotSensorReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->dispatch('refreshData');
                }),
        ];
    }
}
