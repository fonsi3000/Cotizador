<?php

namespace App\Filament\Resources\CotizacionResource\Pages;

use App\Filament\Resources\CotizacionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCotizacion extends CreateRecord
{
    protected static string $resource = CotizacionResource::class;

    /**
     * Crea la cotización base (sin items aún)
     */
    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }

    /**
     * Lógica adicional después de crear
     */
    protected function afterCreate(): void
    {
        // Calcular el total de la cotización
        $this->record->update([
            'total_cotizacion' => $this->record->items->sum('subtotal'),
        ]);
    }

    /**
     * Redirigir al index después de crear
     */
    protected function getRedirectUrl(): string
    {
        return CotizacionResource::getUrl();
    }
}
