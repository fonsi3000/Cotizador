<?php

namespace App\Filament\Resources\CotizacionResource\Pages;

use App\Filament\Resources\CotizacionResource;
use App\Mail\Cotizacion as CotizacionMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

        // Cargar relaciones necesarias para el PDF y correo
        $this->record->load(['items.producto', 'items.listaPrecio', 'usuario']);

        // Generar PDF
        $pdf = Pdf::loadView('Cotizacion.CotizacionPDF', [
            'cotizacion' => $this->record,
            'isPdfDownload' => true,
        ])->output();

        // Guardar PDF en storage/app/public/cotizaciones/
        Storage::put("public/cotizaciones/cotizacion-{$this->record->id}.pdf", $pdf);

        // Enviar por correo si hay correo electrónico
        if ($this->record->correo_electronico_cliente) {
            Mail::to($this->record->correo_electronico_cliente)
                ->send(new CotizacionMail($this->record));
        }

        // Nota: WhatsApp se envía manualmente desde el botón en la tabla
    }

    /**
     * Redirigir al index después de crear
     */
    protected function getRedirectUrl(): string
    {
        return CotizacionResource::getUrl();
    }
}
