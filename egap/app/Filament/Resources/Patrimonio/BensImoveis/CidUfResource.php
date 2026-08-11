<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource\Pages\ListCidUfs;
use App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource\Pages\CreateCidUf;
use App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource\Pages\EditCidUf;
use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\CidUf;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CidUfResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = CidUf::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Cidade/UF';
    protected static ?string $modelLabel = 'Cidade/UF';
    protected static ?string $pluralModelLabel = 'Cidades/UF';
    protected static string | \UnitEnum | null $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 9;
    protected static ?string $slug = 'bens-imoveis/cidades-uf';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('id_cidade')
                            ->label('Cidade')
                            ->numeric()
                            ->required(),

                        TextInput::make('cd_uf')
                            ->label('cd uf')
                            ->maxLength(2)
                            ->required(),

                        TextInput::make('cd_cep_cidade')
                            ->label('cd cep cidade')
                            ->maxLength(20)
                            ->required(),
                    ])
                    ->columns(1)
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id_cidade', '#', isFirstColumn: true),
                TableColumns::text('cd_uf', 'UF'),
                TableColumns::text('cd_cep_cidade', 'CEP'),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCidUfs::route('/'),
            'create' => CreateCidUf::route('/create'),
            'edit' => EditCidUf::route('/{record}/edit'),
        ];
    }
}
