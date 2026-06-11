<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensIntangiveis\TipoBemIntagivel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class TipoBemIntangivelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoBemIntagivel::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Tipos de Intangíveis';

    protected static ?string $modelLabel = 'Tipo de Bem Intangível';

    protected static ?string $pluralModelLabel = 'Tipos de Bens Intangíveis';

    protected static ?string $navigationGroup = 'Bens Intangíveis';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'bens-intangiveis/tipos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação do Tipo')
                    ->description('Defina a categoria utilizada para classificar os bens intangíveis.')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Forms\Components\TextInput::make('descricao')
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
                TableColumns::text('atualizadoPorRef.name', 'Atualizado por')
                    ->toggleable(isToggledHiddenByDefault: true),
                TableColumns::dateTime('date_time', 'Atualizado em')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('descricao');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoBemIntangivels::route('/'),
            'create' => Pages\CreateTipoBemIntangivel::route('/create'),
            'edit' => Pages\EditTipoBemIntangivel::route('/{record}/edit'),
        ];
    }
}
