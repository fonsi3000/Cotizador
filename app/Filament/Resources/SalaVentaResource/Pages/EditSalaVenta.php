<?php

namespace App\Filament\Resources\SalaVentaResource\Pages;

use App\Filament\Resources\SalaVentaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalaVenta extends EditRecord
{
    protected static string $resource = SalaVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
