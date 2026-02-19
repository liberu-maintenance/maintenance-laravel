<?php

namespace App\Filament\App\Resources\VendorContracts\Pages;

use App\Filament\App\Resources\VendorContracts\VendorContractResource;
use Filament\Resources\Pages\EditRecord;

class EditVendorContract extends EditRecord
{
    protected static string $resource = VendorContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
