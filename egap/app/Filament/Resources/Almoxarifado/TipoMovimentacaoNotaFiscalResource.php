<?php

namespace App\Filament\Resources\Almoxarifado;

use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\TipoMovimentacaoNotaFiscalResource\Pages;
use App\Filament\Support\TableColumns;
use App\Models\Almoxarifado\TipoMovimentacaoNotaFiscal;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;

class TipoMovimentacaoNotaFiscalResource extends Resource
{
    protected static ?string $model = TipoMovimentacaoNotaFiscal::class;
    protected static ?string $cluster = AlmoxarifadoCluster::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $slug = 'tipo-movimentacao';
    protected static ?string $navigationLabel = 'Tipo de Movimentação';
    protected static ?string $pluralLabel = 'Tipos de Movimentação';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable(),

                TableColumns::updatedBy('atualizadoPor.name'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Excluir')
                    ->modalHeading('Excluir registro')
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoMovimentacaoNotaFiscals::route('/'),
            'create' => Pages\CreateTipoMovimentacaoNotaFiscal::route('/create'),
            'edit' => Pages\EditTipoMovimentacaoNotaFiscal::route('/{record}/edit'),
        ];
    }
}
