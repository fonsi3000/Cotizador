<?php

namespace App\Filament\Resources\SalaVentaResource\Pages;

use App\Filament\Resources\SalaVentaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalaVentas extends ListRecords
{
    protected static string $resource = SalaVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
