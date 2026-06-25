<?php

namespace App\Filament\Resources\RawMaterialStockResource\Pages;

use App\Filament\Resources\RawMaterialStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRawMaterialStocks extends ListRecords
{
    protected static string $resource = RawMaterialStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }
}


