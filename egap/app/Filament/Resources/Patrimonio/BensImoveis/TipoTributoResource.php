<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource\Pages\CreateTipoTributo;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource\Pages\EditTipoTributo;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource\Pages\ListTipoTributos;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensImoveis\TipoTributo;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TipoTributoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoTributo::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Tipo de Tributo';

    protected static ?string $modelLabel = 'Tipo de Tributo';

    protected static ?string $pluralModelLabel = 'Tipos de Tributo';

    protected static string|\UnitEnum|null $navigationGroup = 'Bens Imóveis';

    protected static ?int $navigationSort = 18;

    protected static ?string $slug = 'bens-imoveis/tipos-tributos';

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
                    ]),
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
            'index' => ListTipoTributos::route('/'),
            'create' => CreateTipoTributo::route('/create'),
            'edit' => EditTipoTributo::route('/{record}/edit'),
        ];
    }
}
