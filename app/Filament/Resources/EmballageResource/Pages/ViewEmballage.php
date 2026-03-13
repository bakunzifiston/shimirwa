<?php

namespace App\Filament\Resources\EmballageResource\Pages;

use App\Filament\Resources\EmballageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmballage extends ViewRecord
{
    protected static string $resource = EmballageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
