<?php

namespace App\Filament\Resources\MillingResource\Pages;

use App\Filament\Resources\MillingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMilling extends ViewRecord
{
    protected static string $resource = MillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
