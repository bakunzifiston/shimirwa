<?php

namespace App\Filament\Resources\RawMaterialStockResource\Pages;

use App\Filament\Resources\RawMaterialStockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRawMaterialStock extends EditRecord
{
    protected static string $resource = RawMaterialStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
