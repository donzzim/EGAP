<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\TipoDeBem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TipoDeBemResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = TipoDeBem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Tipo de bem';
    protected static ?string $modelLabel = 'Tipo de bem';
    protected static ?string $pluralModelLabel = 'Tipos de bem';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 16;
    protected static ?string $slug = 'bens-imoveis/tipos-de-bem';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('Descricao')
                            ->label('Descricao')
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
                TableColumns::text('Descricao', 'Descricao'),
            ])
            ->filters([
                //
            ])
            ->searchPlaceholder('Entre com a palavra-chave');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTipoDeBems::route('/'),
            'create' => Pages\CreateTipoDeBem::route('/create'),
            'edit' => Pages\EditTipoDeBem::route('/{record}/edit'),
        ];
    }
}
