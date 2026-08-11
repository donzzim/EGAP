<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource\Pages\ListEntradaSaidas;
use App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource\Pages\CreateEntradaSaida;
use App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource\Pages\EditEntradaSaida;
use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\EntradaSaida;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EntradaSaidaResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = EntradaSaida::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Entradas/Saídas';
    protected static ?string $modelLabel = 'Entrada/Saída';
    protected static ?string $pluralModelLabel = 'Entradas/Saídas';
    protected static string | \UnitEnum | null $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 15;
    protected static ?string $slug = 'bens-imoveis/entrada-saida';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('tipo')
                            ->label('Tipo')
                            ->required()
                            ->columnSpanFull()
                            ->options([
                                'Entrada' => 'Entrada',
                                'Saída' => 'Saída'
                            ])
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id', '#', isFirstColumn: true),
                TableColumns::dateTime('date_time', 'Data', 'd/m/Y'),
                TableColumns::text('usuarioRef.name', 'Usuário'),
                TableColumns::text('descricao', 'Descrição'),
                TableColumns::text('tipo', 'Tipo'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntradaSaidas::route('/'),
            'create' => CreateEntradaSaida::route('/create'),
            'edit' => EditEntradaSaida::route('/{record}/edit'),
        ];
    }
}
