<?php

namespace App\Filament\Resources\AkreditasiResource\Pages;

use Storage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\AkreditasiResource;

class EditAkreditasi extends EditRecord
{
    protected static string $resource = AkreditasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }




}
