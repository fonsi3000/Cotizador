<?php

namespace App\Filament\Resources\ProductoPrecioResource\Pages;

use App\Filament\Resources\ProductoPrecioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductoPrecios extends ListRecords
{
    protected static string $resource = ProductoPrecioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
