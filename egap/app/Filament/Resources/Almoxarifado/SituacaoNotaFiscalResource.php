<?php

namespace App\Filament\Resources\Almoxarifado;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource\Pages\ListSituacaoNotaFiscals;
use App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource\Pages\CreateSituacaoNotaFiscal;
use App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource\Pages\EditSituacaoNotaFiscal;
use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource\Pages;
use App\Filament\Support\TableColumns;
use App\Models\Almoxarifado\SituacaoNotaFiscal;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SituacaoNotaFiscalResource extends Resource
{
    protected static ?string $model = SituacaoNotaFiscal::class;
    protected static ?string $cluster = AlmoxarifadoCluster::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $slug = 'situacao-notas-fiscais';
    protected static ?string $navigationLabel = 'Situação da Nota Fiscal';
    protected static ?string $pluralLabel = 'Situações da Nota Fiscal';
    protected static ?string $pluralModelLabel = 'Situações da Nota Fiscal';

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 4;

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
                DeleteBulkAction::make(),
            ]);
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSituacaoNotaFiscals::route('/'),
            'create' => CreateSituacaoNotaFiscal::route('/create'),
            'edit' => EditSituacaoNotaFiscal::route('/{record}/edit'),
        ];
    }
}
