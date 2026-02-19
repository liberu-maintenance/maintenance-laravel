<?php

namespace App\Filament\App\Resources\Vendors\Pages;

use App\Filament\App\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\ListRecords;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
