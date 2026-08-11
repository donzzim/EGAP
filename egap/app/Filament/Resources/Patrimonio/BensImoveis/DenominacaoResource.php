<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages\ListDenominacaos;
use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages\CreateDenominacao;
use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages\EditDenominacao;
use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\Denominacao;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DenominacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Denominacao::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Denominação';
    protected static ?string $modelLabel = 'Denominação';
    protected static ?string $pluralModelLabel = 'Denominação';
    protected static string | \UnitEnum | null $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 11;
    protected static ?string $slug = 'bens-imoveis/denominacoes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('denominacao')
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
            'index' => ListDenominacaos::route('/'),
            'create' => CreateDenominacao::route('/create'),
            'edit' => EditDenominacao::route('/{record}/edit'),
        ];
    }
}
