<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Vendors\Pages;

use App\Filament\App\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVendor extends CreateRecord
{
    #[\Override]
    protected static string $resource = VendorResource::class;
}
