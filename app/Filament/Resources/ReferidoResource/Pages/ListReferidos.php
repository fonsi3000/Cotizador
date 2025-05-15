<?php

namespace App\Filament\Resources\ReferidoResource\Pages;

use App\Filament\Resources\ReferidoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReferidos extends ListRecords
{
    protected static string $resource = ReferidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
