<?php

namespace App\Filament\Resources\CotizacionResource\Pages;

use App\Filament\Resources\CotizacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCotizacion extends EditRecord
{
    protected static string $resource = CotizacionResource::class;

    protected function afterSave(): void
    {
        $this->record->update([
            'total_cotizacion' => $this->record->items->sum('subtotal'),
        ]);
    }
}
