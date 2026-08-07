<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource\Pages\ListEstadoConservacaos;
use App\Filament\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource\Pages\CreateEstadoConservacao;
use App\Filament\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource\Pages\EditEstadoConservacao;
use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\EstadoConservacao;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EstadoConservacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = EstadoConservacao::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Estado de Conservação';
    protected static ?string $modelLabel = 'Estado de Conservação';
    protected static ?string $pluralModelLabel = 'Estados de Conservação';
    protected static string | \UnitEnum | null $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 12;
    protected static ?string $slug = 'bens-imoveis/estado-conservacao';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('descEstadoConservacao')
                            ->label('Estado de Conservação')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('Id', '#', isFirstColumn: true),
                TableColumns::text('descEstadoConservacao', 'Estado de Conservação'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEstadoConservacaos::route('/'),
            'create' => CreateEstadoConservacao::route('/create'),
            'edit' => EditEstadoConservacao::route('/{record}/edit'),
        ];
    }
}
