<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\ObraResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensImoveis\Obra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class ObraResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Obra::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Obras e Ampliações';
    protected static ?string $modelLabel = 'Obra e Ampliação';
    protected static ?string $pluralModelLabel = 'Obras e Ampliações';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'bens-imoveis/obras';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('id_imovel')
                            ->label('Imóveis')
                            ->relationship('imovelRelacaoref', 'descricao')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('descricao')
                            ->label('Descrição')
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('data')
                            ->label('Data')
                            ->default(now())
                            ->displayFormat('d/m/Y'),

                        MoneyInput::make('valor')
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('imovelRelacaoref.descricao', 'Imóveis', isFirstColumn: true),
                TableColumns::text('descricao', 'Descrição'),
                TableColumns::date('data', 'Data'),
                TableColumns::text('valor', 'Valor (R$)'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListObras::route('/'),
            'create' => Pages\CreateObra::route('/create'),
            'edit' => Pages\EditObra::route('/{record}/edit'),
        ];
    }
}
