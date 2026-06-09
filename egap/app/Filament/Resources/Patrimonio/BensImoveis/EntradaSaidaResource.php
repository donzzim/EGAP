<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\EntradaSaida;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class EntradaSaidaResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = EntradaSaida::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Entradas/Saídas';
    protected static ?string $modelLabel = 'Entrada/Saída';
    protected static ?string $pluralModelLabel = 'Entradas/Saídas';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 15;
    protected static ?string $slug = 'bens-imoveis/entrada-saida';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DateTimePicker::make('date_time')
                            ->label('date time')
                            ->default(now())
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('usuario')
                            ->label('usuario')
                            ->numeric()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('descricao')
                            ->label('descricao')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('tipo')
                            ->label('tipo')
                            ->required()
                            ->columnSpanFull()
                            ->rows(4),
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id', 'ID', isFirstColumn: true)
                    ->width('80px'),
                TableColumns::dateTime('date_time', 'Data', 'd/m/Y'),
                TableColumns::text('usuario', 'Usuário'),
                TableColumns::text('descricao', 'Descrição'),
                TableColumns::text('tipo', 'Tipo'),
            ])
            ->filters([
                //
            ])
            ->searchPlaceholder('Entre com a palavra-chave');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntradaSaidas::route('/'),
        ];
    }
}
