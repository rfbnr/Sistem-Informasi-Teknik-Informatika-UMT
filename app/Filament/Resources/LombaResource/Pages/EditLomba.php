<?php

namespace App\Filament\Resources\LombaResource\Pages;

use App\Filament\Resources\LombaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLomba extends EditRecord
{
    protected static string $resource = LombaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
