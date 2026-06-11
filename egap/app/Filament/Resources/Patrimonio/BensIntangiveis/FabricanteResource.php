<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensIntangiveis\FabricanteResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensIntangiveis\Fabricante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class FabricanteResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Fabricante::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Fabricantes';

    protected static ?string $modelLabel = 'Fabricante';

    protected static ?string $pluralModelLabel = 'Fabricantes';

    protected static ?string $navigationGroup = 'Bens Intangíveis';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'bens-intangiveis/fabricantes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação do Fabricante')
                    ->description('Informe o nome ou a razão social do fabricante do bem intangível.')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Forms\Components\TextInput::make('descricao')
                            ->label('Fabricante')
                            ->placeholder('Ex.: Microsoft, Adobe ou Oracle')
                            ->prefixIcon('heroicon-o-building-office')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('descricao', 'Fabricante', isFirstColumn: true)
                    ->icon('heroicon-o-building-office')
                    ->weight('medium')
                    ->wrap(),
                TableColumns::text('atualizadoPorRef.name', 'Atualizado por'),
                TableColumns::dateTime('date_time', 'Atualizado em'),
            ])
            ->defaultSort('descricao');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFabricantes::route('/'),
            'create' => Pages\CreateFabricante::route('/create'),
            'edit' => Pages\EditFabricante::route('/{record}/edit'),
        ];
    }
}
