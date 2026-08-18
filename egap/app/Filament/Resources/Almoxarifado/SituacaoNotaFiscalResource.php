<?php

namespace App\Filament\Resources\Almoxarifado;

use App\Filament\Support\TableDefaults;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource\Pages\ListSituacaoNotaFiscals;
use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Support\TableColumns;
use App\Models\Almoxarifado\SituacaoNotaFiscal;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
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
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('descricao', 'Descrição', true)
                    ->searchable(),
                TableColumns::updatedBy('atualizadoPor.name'),
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
        ];
    }
}
