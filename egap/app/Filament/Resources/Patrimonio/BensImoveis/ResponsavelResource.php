<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages\ListResponsavels;
use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages\CreateResponsavel;
use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages\EditResponsavel;
use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\Responsavel;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResponsavelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Responsavel::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Responsável';
    protected static ?string $modelLabel = 'Responsável';
    protected static ?string $pluralModelLabel = 'Responsáveis';
    protected static string | \UnitEnum | null $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 13;
    protected static ?string $slug = 'bens-imoveis/responsaveis';

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

                        TextInput::make('proprietario')
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
            'index' => ListResponsavels::route('/'),
            'create' => CreateResponsavel::route('/create'),
            'edit' => EditResponsavel::route('/{record}/edit'),
        ];
    }
}
