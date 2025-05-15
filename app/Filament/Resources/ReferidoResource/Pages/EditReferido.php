<?php

namespace App\Filament\Resources\ReferidoResource\Pages;

use App\Filament\Resources\ReferidoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReferido extends EditRecord
{
    protected static string $resource = ReferidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return ReferidoResource::getUrl();
    }
}
