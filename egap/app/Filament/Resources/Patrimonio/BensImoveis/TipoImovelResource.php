<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoImovelResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\TipoImovel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TipoImovelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoImovel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Tipo de Imóvel';
    protected static ?string $modelLabel = 'Tipo de Imóvel';
    protected static ?string $pluralModelLabel = 'Tipos de Imóvel';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 17;
    protected static ?string $slug = 'bens-imoveis/tipos-imoveis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('desc_tipo_imovel')
                            ->label('Descrição')
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
                TableColumns::text('desc_tipo_imovel', 'Descrição'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoImovels::route('/'),
            'create' => Pages\CreateTipoImovel::route('/create'),
            'edit' => Pages\EditTipoImovel::route('/{record}/edit'),
        ];
    }
}
