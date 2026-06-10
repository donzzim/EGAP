<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\Responsavel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class ResponsavelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Responsavel::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Responsável';
    protected static ?string $modelLabel = 'Responsável';
    protected static ?string $pluralModelLabel = 'Responsáveis';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 13;
    protected static ?string $slug = 'bens-imoveis/responsaveis';

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

                        Forms\Components\TextInput::make('proprietario')
                            ->label('Proprietário')
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
                TableColumns::text('proprietario', 'Proprietário'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResponsavels::route('/'),
            'create' => Pages\CreateResponsavel::route('/create'),
            'edit' => Pages\EditResponsavel::route('/{record}/edit'),
        ];
    }
}
