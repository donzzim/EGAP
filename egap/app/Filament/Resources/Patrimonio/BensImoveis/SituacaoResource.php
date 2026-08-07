<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource\Pages\CreateSituacao;
use App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource\Pages\EditSituacao;
use App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource\Pages\ListSituacaos;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensImoveis\Situacao;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SituacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Situacao::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Situação';

    protected static ?string $modelLabel = 'Situação';

    protected static ?string $pluralModelLabel = 'Situações';

    protected static string|\UnitEnum|null $navigationGroup = 'Bens Imóveis';

    protected static ?int $navigationSort = 14;

    protected static ?string $slug = 'bens-imoveis/situacao';

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
            'index' => ListSituacaos::route('/'),
            'create' => CreateSituacao::route('/create'),
            'edit' => EditSituacao::route('/{record}/edit'),
        ];
    }
}
