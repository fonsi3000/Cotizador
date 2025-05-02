<?php

namespace App\Filament\Resources\CotizacionResource\Pages;

use App\Filament\Resources\CotizacionResource;
use App\Mail\Cotizacion as CotizacionMail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;

class CreateCotizacion extends CreateRecord
{
    protected static string $resource = CotizacionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }

    protected function afterCreate(): void
    {
        // Actualizar total
        $this->record->update([
            'total_cotizacion' => $this->record->items->sum('subtotal'),
        ]);

        // Enviar correo solo si el cliente tiene correo
        if ($this->record->correo_electronico_cliente) {
            Mail::to($this->record->correo_electronico_cliente)
                ->send(new CotizacionMail($this->record->load(['items.producto', 'items.listaPrecio', 'usuario'])));
        }
    }

    protected function getRedirectUrl(): string
    {
        return CotizacionResource::getUrl();
    }
}
