<?php

namespace App\Filament\Resources\SorotanResource\Pages;

use App\Filament\Resources\SorotanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSorotans extends ListRecords
{
    protected static string $resource = SorotanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
