<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use EightyNine\ExcelImport\ExcelImportAction;
use App\Imports\ProductoImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->processCollectionUsing(function (string $modelClass, Collection $collection) {
                    Log::info('Procesando colección en ExcelImportAction', [
                        'model_class' => $modelClass,
                        'collection_count' => $collection->count()
                    ]);

                    $importer = new ProductoImport();
                    $importer->collection($collection);

                    return $collection;
                })
                ->use(ProductoImport::class),
            Actions\CreateAction::make(),
        ];
    }
}
