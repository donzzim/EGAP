<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\CidUf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class CidUfResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = CidUf::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Cidade/UF';
    protected static ?string $modelLabel = 'Cidade/UF';
    protected static ?string $pluralModelLabel = 'Cidades/UF';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 9;
    protected static ?string $slug = 'bens-imoveis/cidades-uf';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('id_cidade')
                            ->label('Cidade')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('cd_uf')
                            ->label('cd uf')
                            ->maxLength(2)
                            ->required(),

                        Forms\Components\TextInput::make('cd_cep_cidade')
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
                TableColumns::text('id', '#', isFirstColumn: true),
                TableColumns::text('id_cidade', 'id cidade'),
                TableColumns::text('cd_uf', 'cd uf'),
                TableColumns::text('cd_cep_cidade', 'cd cep cidade'),
            ])
            ->filters([
                //
            ])
            ->searchPlaceholder('Entre com a palavra-chave');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCidUfs::route('/'),
            'create' => Pages\CreateCidUf::route('/create'),
            'edit' => Pages\EditCidUf::route('/{record}/edit'),
        ];
    }
}
