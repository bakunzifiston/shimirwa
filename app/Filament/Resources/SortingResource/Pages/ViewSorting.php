<?php

namespace App\Filament\Resources\SortingResource\Pages;

use App\Filament\Resources\SortingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSorting extends ViewRecord
{
    protected static string $resource = SortingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
