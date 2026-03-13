<?php

namespace App\Filament\Resources\EmballageResource\Pages;

use App\Filament\Resources\EmballageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmballage extends EditRecord
{
    protected static string $resource = EmballageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
