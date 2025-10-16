<?php

namespace App\Filament\Resources\TalentaResource\Pages;

use App\Filament\Resources\TalentaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTalenta extends EditRecord
{
    protected static string $resource = TalentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
