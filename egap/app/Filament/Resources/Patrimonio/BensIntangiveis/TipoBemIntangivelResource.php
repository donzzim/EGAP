<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages\ListTipoBemIntangivels;
use App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages\CreateTipoBemIntangivel;
use App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages\EditTipoBemIntangivel;
use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensIntangiveis\TipoBemIntagivel;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class TipoBemIntangivelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoBemIntagivel::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Tipos de Intangíveis';

    protected static ?string $modelLabel = 'Tipo de Bem Intangível';

    protected static ?string $pluralModelLabel = 'Tipos de Bens Intangíveis';

    protected static string | \UnitEnum | null $navigationGroup = 'Bens Intangíveis';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'bens-intangiveis/tipos';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação do Tipo')
                    ->description('Defina a categoria utilizada para classificar os bens intangíveis.')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->placeholder('Ex.: Software, licença, marca ou patente')
                            ->prefixIcon('heroicon-o-tag')
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
                TableColumns::text('descricao', 'Tipo de Bem Intangível', isFirstColumn: true)
                    ->icon('heroicon-o-tag')
                    ->weight('medium')
                    ->wrap(),
                TableColumns::updatedBy('atualizadoPorRef.name')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('descricao');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTipoBemIntangivels::route('/'),
            'create' => CreateTipoBemIntangivel::route('/create'),
            'edit' => EditTipoBemIntangivel::route('/{record}/edit'),
        ];
    }
}
