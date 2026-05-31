<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\VendorContracts\Pages;

use App\Filament\App\Resources\VendorContracts\VendorContractResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVendorContract extends CreateRecord
{
    #[\Override]
    protected static string $resource = VendorContractResource::class;
}
