<?php

namespace App\Filament\Resources\SorotanResource\Pages;

use App\Filament\Resources\SorotanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSorotan extends EditRecord
{
    protected static string $resource = SorotanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
