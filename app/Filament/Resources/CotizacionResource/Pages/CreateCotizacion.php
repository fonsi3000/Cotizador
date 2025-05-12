<?php

namespace App\Filament\Resources\CotizacionResource\Pages;

use App\Filament\Resources\CotizacionResource;
use App\Mail\Cotizacion as CotizacionMail;
use App\Services\WhatsAppService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
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
        // Calcular total de la cotización con base en los ítems
        $this->record->update([
            'total_cotizacion' => $this->record->items->sum('subtotal'),
        ]);

        // Enviar por correo si tiene dirección válida
        if ($this->record->correo_electronico_cliente) {
            Mail::to($this->record->correo_electronico_cliente)
                ->send(new CotizacionMail(
                    $this->record->load(['items.producto', 'items.listaPrecio', 'usuario'])
                ));
        }

        // Enviar mensaje por WhatsApp si tiene número válido
        if ($this->record->numero_celular_cliente) {
            WhatsAppService::enviarCotizacion($this->record);
        }
    }

    /**
     * Redirigir al index después de crear
     */
    protected function getRedirectUrl(): string
    {
        return CotizacionResource::getUrl();
    }
}
