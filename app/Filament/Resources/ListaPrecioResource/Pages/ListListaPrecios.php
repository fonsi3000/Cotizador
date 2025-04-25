<?php

namespace App\Filament\Resources\ListaPrecioResource\Pages;

use App\Filament\Resources\ListaPrecioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListListaPrecios extends ListRecords
{
    protected static string $resource = ListaPrecioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
