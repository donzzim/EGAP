<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\EstadoConservacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class EstadoConservacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = EstadoConservacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Estado de Conservação';
    protected static ?string $modelLabel = 'Estado de Conservação';
    protected static ?string $pluralModelLabel = 'Estados de Conservação';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 12;
    protected static ?string $slug = 'bens-imoveis/estado-conservacao';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('descEstadoConservacao')
                            ->label('descEstadoConservacao')
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
                TableColumns::text('Id', 'Id', isFirstColumn: true)
                    ->width('80px'),
                TableColumns::text('descEstadoConservacao', 'descEstadoConservacao'),
            ])
            ->filters([
                //
            ])
            ->searchPlaceholder('Entre com a palavra-chave');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEstadoConservacaos::route('/'),
        ];
    }
}
