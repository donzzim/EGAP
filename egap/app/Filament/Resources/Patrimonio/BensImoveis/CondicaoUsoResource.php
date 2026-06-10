<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\CondicaoUso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class CondicaoUsoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = CondicaoUso::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Condição de Uso';
    protected static ?string $modelLabel = 'Condição de Uso';
    protected static ?string $pluralModelLabel = 'Condições de Uso';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 10;
    protected static ?string $slug = 'bens-imoveis/condicoes-uso';

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
            'index' => Pages\ListCondicaoUsos::route('/'),
            'create' => Pages\CreateCondicaoUso::route('/create'),
            'edit' => Pages\EditCondicaoUso::route('/{record}/edit'),
        ];
    }
}
