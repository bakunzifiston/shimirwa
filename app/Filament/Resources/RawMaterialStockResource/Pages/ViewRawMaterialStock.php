<?php

namespace App\Filament\Resources\RawMaterialStockResource\Pages;

use App\Filament\Resources\RawMaterialStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRawMaterialStock extends ViewRecord
{
    protected static string $resource = RawMaterialStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
