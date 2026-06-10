<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\TipoTributo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TipoTributoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoTributo::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Tipo de Tributo';
    protected static ?string $modelLabel = 'Tipo de Tributo';
    protected static ?string $pluralModelLabel = 'Tipos de Tributo';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 18;
    protected static ?string $slug = 'bens-imoveis/tipos-tributos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('descricao')
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
                TableColumns::text('id', '#', isFirstColumn: true),
                TableColumns::text('descricao', 'Descrição'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoTributos::route('/'),
            'create' => Pages\CreateTipoTributo::route('/create'),
            'edit' => Pages\EditTipoTributo::route('/{record}/edit'),
        ];
    }
}
