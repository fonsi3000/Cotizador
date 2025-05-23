<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferidoResource\Pages;
use App\Models\Referido;
use App\Models\SalaVenta;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ReferidoResource extends Resource
{
    protected static ?string $model = Referido::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_referidor')
                    ->label('Nombre del que refiere')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('documento_referidor')
                    ->label('Documento del que refiere')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('nombre_referido')
                    ->label('Nombre del referido')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('documento_referido')
                    ->label('Documento del referido')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('correo_referido')
                    ->label('Correo del referido')
                    ->required()
                    ->email(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_referidor')->label('Quien refiere')->searchable(),
                Tables\Columns\TextColumn::make('documento_referidor')->searchable(),
                Tables\Columns\TextColumn::make('nombre_referido')->searchable(),
                Tables\Columns\TextColumn::make('documento_referido')->searchable(),
                Tables\Columns\TextColumn::make('vigencia')->date()->label('Vigente hasta')->sortable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'primary' => 'activo',
                        'success' => 'usado',
                        'danger' => 'vencido',
                    ])
                    ->label('Estado'),
            ])
            ->actions([
                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn($record) => 'Referido: ' . $record->nombre_referido)
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->action(fn() => null) // No hace nada
                    ->modalContent(function ($record) {
                        $record->load(['salaVenta', 'modificadoPor']);
                        return view('Referido.Show', ['referido' => $record]);
                    }),

                Tables\Actions\EditAction::make(),

                Action::make('validar_codigo')
                    ->label('Validar Código')
                    ->icon('heroicon-o-key')
                    ->visible(fn($record) => $record->estado === 'activo' && !$record->referido_validado)
                    ->form([
                        Forms\Components\TextInput::make('codigo_confirmacion')
                            ->label('Código recibido por el cliente')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        if ($data['codigo_confirmacion'] !== $record->codigo_referido) {
                            Notification::make()
                                ->title('Código inválido')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update([
                            'referido_validado' => true,
                        ]);

                        Notification::make()
                            ->title('Código validado correctamente')
                            ->success()
                            ->send();
                    }),

                Action::make('completar_venta')
                    ->label('Completar Venta')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) => $record->estado === 'activo' && $record->referido_validado)
                    ->form([
                        Forms\Components\TextInput::make('codigo_venta')
                            ->label('Código de pedido SAP')
                            ->required(),
                        Forms\Components\Select::make('sala_venta_id')
                            ->label('Sala de ventas')
                            ->options(SalaVenta::all()->pluck('nombre', 'id'))
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'estado' => 'usado',
                            'codigo_venta' => $data['codigo_venta'],
                            'sala_venta_id' => $data['sala_venta_id'],
                            'modificado_por_id' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Referido marcado como usado')
                            ->success()
                            ->send();
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
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('estado', '!=', 'pendiente');
    }
}
