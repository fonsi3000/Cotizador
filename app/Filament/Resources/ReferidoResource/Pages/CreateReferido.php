<?php

namespace App\Filament\Resources\ReferidoResource\Pages;

use App\Filament\Resources\ReferidoResource;
use App\Mail\CodigoReferidoMail;
use App\Models\Referido;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateReferido extends CreateRecord
{
    protected static string $resource = ReferidoResource::class;

    protected function getRedirectUrl(): string
    {
        return ReferidoResource::getUrl();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['estado'] = 'activo';
        $data['vigencia'] = now()->addMonth()->toDateString();
        $data['codigo_referido'] = (string) rand(100000, 999999); // Código numérico de 6 dígitos
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        if (!empty($record->correo_referido)) {
            Mail::to($record->correo_referido)
                ->send(new CodigoReferidoMail($record->codigo_referido));
        }
    }
}
