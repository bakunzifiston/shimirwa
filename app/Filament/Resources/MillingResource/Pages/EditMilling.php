<?php

namespace App\Filament\Resources\MillingResource\Pages;

use App\Filament\Resources\MillingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMilling extends EditRecord
{
    protected static string $resource = MillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
