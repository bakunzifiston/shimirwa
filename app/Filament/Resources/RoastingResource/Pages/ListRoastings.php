<?php

namespace App\Filament\Resources\RoastingResource\Pages;

use App\Filament\Resources\RoastingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoastings extends ListRecords
{
    protected static string $resource = RoastingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }
}


