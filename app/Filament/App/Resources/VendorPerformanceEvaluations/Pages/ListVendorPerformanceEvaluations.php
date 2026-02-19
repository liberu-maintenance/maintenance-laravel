<?php

namespace App\Filament\App\Resources\VendorPerformanceEvaluations\Pages;

use App\Filament\App\Resources\VendorPerformanceEvaluations\VendorPerformanceEvaluationResource;
use Filament\Resources\Pages\ListRecords;

class ListVendorPerformanceEvaluations extends ListRecords
{
    protected static string $resource = VendorPerformanceEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
