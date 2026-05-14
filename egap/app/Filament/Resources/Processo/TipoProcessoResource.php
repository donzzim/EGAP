<?php

namespace App\Filament\Resources\Processo;

use App\Filament\Resources\Processo\TipoProcessoResource\Pages;
use App\Models\Processo\MatTipoProcesso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TipoProcessoResource extends Resource
{
    protected static ?string $model = MatTipoProcesso::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Processos';
    protected static ?string $navigationLabel = 'Tipos de Processo';
    protected static ?string $modelLabel = 'Tipo de Processo';
    protected static ?string $pluralModelLabel = 'Tipos de Processo';
    protected static ?string $slug = 'processos/tipos-processos';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->striped()
            ->deferLoading()
            ->searchPlaceholder('Entre com a palavra-chave')
            ->emptyStateHeading('Nenhum Tipo de Processo encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoProcessos::route('/'),
        ];
    }
}
