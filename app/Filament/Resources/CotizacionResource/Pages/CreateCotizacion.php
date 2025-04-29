<?php

namespace App\Filament\Resources\CotizacionResource\Pages;

use App\Filament\Resources\CotizacionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCotizacion extends CreateRecord
{
    protected static string $resource = CotizacionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Crear la cotización sin items aún
        $record = static::getModel()::create($data);

        return $record;
    }

    protected function afterCreate(): void
    {
        // Ahora sí los items ya fueron creados
        $this->record->update([
            'total_cotizacion' => $this->record->items->sum('subtotal'),
        ]);
    }
}
