<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensIntangiveis\FabricanteResource\Pages\CreateFabricante;
use App\Filament\Resources\Patrimonio\BensIntangiveis\FabricanteResource\Pages\EditFabricante;
use App\Filament\Resources\Patrimonio\BensIntangiveis\FabricanteResource\Pages\ListFabricantes;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensIntangiveis\Fabricante;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FabricanteResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Fabricante::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Fabricantes';

    protected static ?string $modelLabel = 'Fabricante';

    protected static ?string $pluralModelLabel = 'Fabricantes';

    protected static string|\UnitEnum|null $navigationGroup = 'Bens Intangíveis';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'bens-intangiveis/fabricantes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação do Fabricante')
                    ->description('Informe o nome ou a razão social do fabricante do bem intangível.')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextInput::make('descricao')
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
                TableColumns::updatedBy('atualizadoPorRef.name'),
            ])
            ->defaultSort('descricao');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFabricantes::route('/'),
            'create' => CreateFabricante::route('/create'),
            'edit' => EditFabricante::route('/{record}/edit'),
        ];
    }
}
