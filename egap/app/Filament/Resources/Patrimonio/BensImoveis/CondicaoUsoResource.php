<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages\CreateCondicaoUso;
use App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages\EditCondicaoUso;
use App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages\ListCondicaoUsos;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensImoveis\CondicaoUso;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CondicaoUsoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = CondicaoUso::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Condição de Uso';

    protected static ?string $modelLabel = 'Condição de Uso';

    protected static ?string $pluralModelLabel = 'Condições de Uso';

    protected static string|\UnitEnum|null $navigationGroup = 'Bens Imóveis';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'bens-imoveis/condicoes-uso';

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
            'index' => ListCondicaoUsos::route('/'),
            'create' => CreateCondicaoUso::route('/create'),
            'edit' => EditCondicaoUso::route('/{record}/edit'),
        ];
    }
}
