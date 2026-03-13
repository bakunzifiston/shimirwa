<?php

namespace App\Filament\Resources\RoastingResource\Pages;

use App\Filament\Resources\RoastingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRoasting extends ViewRecord
{
    protected static string $resource = RoastingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
