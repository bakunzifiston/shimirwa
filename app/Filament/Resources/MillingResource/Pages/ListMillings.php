<?php

namespace App\Filament\Resources\MillingResource\Pages;

use App\Filament\Resources\MillingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMillings extends ListRecords
{
    protected static string $resource = MillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }
}


