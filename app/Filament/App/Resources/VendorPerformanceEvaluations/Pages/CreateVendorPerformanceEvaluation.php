<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\VendorPerformanceEvaluations\Pages;

use App\Filament\App\Resources\VendorPerformanceEvaluations\VendorPerformanceEvaluationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVendorPerformanceEvaluation extends CreateRecord
{
    #[\Override]
    protected static string $resource = VendorPerformanceEvaluationResource::class;
}
