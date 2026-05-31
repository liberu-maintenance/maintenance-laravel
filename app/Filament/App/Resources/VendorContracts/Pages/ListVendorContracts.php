<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\VendorContracts\Pages;

use App\Filament\App\Resources\VendorContracts\VendorContractResource;
use Filament\Resources\Pages\ListRecords;

class ListVendorContracts extends ListRecords
{
    #[\Override]
    protected static string $resource = VendorContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
