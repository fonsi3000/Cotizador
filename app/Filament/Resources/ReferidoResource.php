<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferidoResource\Pages;
use App\Models\Referido;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ReferidoResource extends Resource
{
    protected static ?string $model = Referido::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del que refiere')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('referido')
                    ->label('Nombre del referido')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('documento')
                    ->label('Documento del referido')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('sala_venta_id')
                    ->label('Sala de ventas')
                    ->relationship('salaVenta', 'nombre')
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->label('Quien refiere')->searchable(),
                Tables\Columns\TextColumn::make('referido')->searchable(),
                Tables\Columns\TextColumn::make('documento')->searchable(),
                Tables\Columns\TextColumn::make('salaVenta.nombre')->label('Sala de ventas')->searchable(),
                Tables\Columns\TextColumn::make('vigencia')->date()->label('Vigente hasta')->sortable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'primary' => 'activo',
                        'success' => 'usado',
                        'danger' => 'vencido',
                    ])
                    ->label('Estado')
                    ->getStateUsing(function ($record) {
                        if ($record->estado === 'usado') return 'usado';
                        if ($record->vigencia < now()->toDateString()) return 'vencido';
                        return 'activo';
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Creado')->date('d/m/Y')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('marcar_usado')
                    ->label('Marcar como Usado')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->estado === 'activo')
                    ->action(function ($record) {
                        $record->estado = 'usado';
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferidos::route('/'),
            'create' => Pages\CreateReferido::route('/create'),
            'edit' => Pages\EditReferido::route('/{record}/edit'),
        ];
    }
}
