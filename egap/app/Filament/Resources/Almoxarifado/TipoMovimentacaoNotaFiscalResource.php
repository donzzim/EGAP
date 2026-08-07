<?php

namespace App\Filament\Resources\Almoxarifado;

use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\TipoMovimentacaoNotaFiscalResource\Pages\CreateTipoMovimentacaoNotaFiscal;
use App\Filament\Resources\Almoxarifado\TipoMovimentacaoNotaFiscalResource\Pages\EditTipoMovimentacaoNotaFiscal;
use App\Filament\Resources\Almoxarifado\TipoMovimentacaoNotaFiscalResource\Pages\ListTipoMovimentacaoNotaFiscals;
use App\Filament\Support\TableColumns;
use App\Models\Almoxarifado\TipoMovimentacaoNotaFiscal;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TipoMovimentacaoNotaFiscalResource extends Resource
{
    protected static ?string $model = TipoMovimentacaoNotaFiscal::class;

    protected static ?string $cluster = AlmoxarifadoCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $slug = 'tipo-movimentacao';

    protected static ?string $navigationLabel = 'Tipo de Movimentação';

    protected static ?string $pluralLabel = 'Tipos de Movimentação';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ->recordActions([
                EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                DeleteAction::make()
                    ->tooltip('Excluir')
                    ->modalHeading('Excluir registro')
                    ->hiddenLabel(),
            ])
            ->toolbarActions([
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
            'index' => ListTipoMovimentacaoNotaFiscals::route('/'),
            'create' => CreateTipoMovimentacaoNotaFiscal::route('/create'),
            'edit' => EditTipoMovimentacaoNotaFiscal::route('/{record}/edit'),
        ];
    }
}
