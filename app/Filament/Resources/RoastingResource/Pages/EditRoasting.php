<?php

namespace App\Filament\Resources\RoastingResource\Pages;

use App\Filament\Resources\RoastingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoasting extends EditRecord
{
    protected static string $resource = RoastingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
