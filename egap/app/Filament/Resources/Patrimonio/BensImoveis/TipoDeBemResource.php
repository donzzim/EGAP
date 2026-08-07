<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource\Pages\CreateTipoDeBem;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource\Pages\EditTipoDeBem;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource\Pages\ListTipoDeBems;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensImoveis\TipoDeBem;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TipoDeBemResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoDeBem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Tipo de bem';

    protected static ?string $modelLabel = 'Tipo de bem';

    protected static ?string $pluralModelLabel = 'Tipos de bem';

    protected static string|\UnitEnum|null $navigationGroup = 'Bens Imóveis';

    protected static ?int $navigationSort = 16;

    protected static ?string $slug = 'bens-imoveis/tipos-de-bem';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('Descricao')
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
                TableColumns::text('Id', '#', isFirstColumn: true),
                TableColumns::text('Descricao', 'Descrição'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTipoDeBems::route('/'),
            'create' => CreateTipoDeBem::route('/create'),
            'edit' => EditTipoDeBem::route('/{record}/edit'),
        ];
    }
}
