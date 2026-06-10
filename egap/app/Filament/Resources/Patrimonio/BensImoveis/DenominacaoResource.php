<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\Denominacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class DenominacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Denominacao::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Denominação';
    protected static ?string $modelLabel = 'Denominação';
    protected static ?string $pluralModelLabel = 'Denominação';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 11;
    protected static ?string $slug = 'bens-imoveis/denominacoes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('denominacao')
                    ->label('Denominação')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id', '#', isFirstColumn: true),
                TableColumns::text('denominacao', 'Denominação'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDenominacaos::route('/'),
            'create' => Pages\CreateDenominacao::route('/create'),
            'edit' => Pages\EditDenominacao::route('/{record}/edit'),
        ];
    }
}
