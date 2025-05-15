<?php

namespace App\Filament\Resources\ReferidoResource\Pages;

use App\Filament\Resources\ReferidoResource;
use App\Models\Referido;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReferido extends CreateRecord
{
    protected static string $resource = ReferidoResource::class;

    /**
     * Redirigir al index después de crear
     */
    protected function getRedirectUrl(): string
    {
        return ReferidoResource::getUrl();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['estado'] = 'activo';
        $data['vigencia'] = now()->addMonth()->toDateString();
        return $data;
    }
}
