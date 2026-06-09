<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TributoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensImoveis\Tributo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TributoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Tributo::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Tributos';
    protected static ?string $modelLabel = 'Tributo';
    protected static ?string $pluralModelLabel = 'Tributos';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 6;
    protected static ?string $slug = 'bens-imoveis/tributos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Tributos')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('IdentificaÃ§Ã£o')
                                    ->description('Vincule o tributo ao imÃ³vel e informe a natureza da cobranÃ§a.')
                                    ->icon('heroicon-o-home-modern')
                                    ->schema([
                                        Forms\Components\Select::make('Id_imovel')
                                            ->label('ImÃ³vel')
                                            ->relationship('imovelRelacaoref', 'descricao')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->placeholder('Selecione o imÃ³vel')
                                            ->columnSpan(7),

                                        Forms\Components\Select::make('tipo_tributo')
                                            ->label('Tipo do tributo')
                                            ->relationship('tipoTributoRelacaoref', 'descricao')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->placeholder('Selecione o tipo')
                                            ->columnSpan(5),
                                    ])
                                    ->columns(12),

                                Forms\Components\Section::make('Valores e vencimento')
                                    ->description('Dados financeiros previstos para o tributo.')
                                    ->icon('heroicon-o-calendar-days')
                                    ->schema([
                                        Forms\Components\DatePicker::make('vencimento')
                                            ->label('Vencimento')
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->placeholder('dd/mm/aaaa')
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('valor')
                                            ->label('Valor')
                                            ->required()
                                            ->numeric()
                                            ->prefix('R$')
                                            ->placeholder('0.00')
                                            ->columnSpan(4),
                                    ])
                                    ->columns(12),

                                Forms\Components\Section::make('Pagamento')
                                    ->description('Preencha quando houver registro de quitaÃ§Ã£o.')
                                    ->icon('heroicon-o-credit-card')
                                    ->schema([
                                        Forms\Components\DatePicker::make('pago_em')
                                            ->label('Pago em')
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->placeholder('dd/mm/aaaa')
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('valor_pago')
                                            ->label('Valor pago')
                                            ->required()
                                            ->numeric()
                                            ->prefix('R$')
                                            ->placeholder('0.00')
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('processo_pagto')
                                            ->label('Processo de pagamento')
                                            ->required()
                                            ->placeholder('Informe o processo, se houver')
                                            ->columnSpan(4),
                                    ])
                                    ->columns(12),

                                Forms\Components\Section::make('Auditoria e observaÃ§Ãµes')
                                    ->description('Controle interno de atualizaÃ§Ã£o e informaÃ§Ãµes complementares.')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->schema([
                                        Forms\Components\Select::make('atualizado_por')
                                            ->label('Atualizado por')
                                            ->relationship('atualizadoPorRelacaoref', 'name')
                                            ->required()
                                            ->default(fn () => auth()->id())
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->columnSpan(6),

                                        Forms\Components\DatePicker::make('date_time')
                                            ->label('Atualizado em')
                                            ->required()
                                            ->default(now())
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(6),

                                        Forms\Components\Textarea::make('observacao')
                                            ->label('ObservaÃ§Ã£o')
                                            ->required()
                                            ->rows(4)
                                            ->placeholder('Registre informaÃ§Ãµes relevantes sobre o tributo')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(12),
                            ]),

                        Forms\Components\Tabs\Tab::make('Eventos')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Forms\Components\Section::make('HistÃ³rico de eventos')
                                    ->description('Registre ocorrÃªncias relacionadas ao tributo.')
                                    ->icon('heroicon-o-queue-list')
                                    ->schema([
                                        Forms\Components\Repeater::make('eventos')
                                            ->required()
                                            ->schema([
                                                Forms\Components\DatePicker::make('data')
                                                    ->label('Data')
                                                    ->required()
                                                    ->displayFormat('d/m/Y')
                                                    ->native(false)
                                                    ->placeholder('dd/mm/aaaa')
                                                    ->columnSpanFull(),

                                                Forms\Components\Textarea::make('descricao')
                                                    ->label('DescriÃ§Ã£o')
                                                    ->required()
                                                    ->rows(2)
                                                    ->placeholder('Descreva o evento')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(12)
                                            ->defaultItems(1)
                                            ->minItems(1)
                                            ->addActionLabel('Adicionar evento')
                                            ->collapsible()
                                            ->reorderableWithButtons()
                                            ->hiddenLabel(),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('imovelRelacaoref.descricao', 'Imóvel', isFirstColumn: true),
                TableColumns::text('tipoTributoRelacaoref.descricao', 'Tipo do tributo'),
                TableColumns::date('vencimento', 'Vencimento'),
                TableColumns::money('valor', 'Valor'),
                TableColumns::date('pago_em', 'Pago em'),
                TableColumns::money('valor_pago', 'Valor Pago'),
                TableColumns::text('processo_pagto', 'Processo Pagto')
                    ->badge(),
                TableColumns::text('observacao', 'Observação')
                    ->wrap(),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTributos::route('/'),
            'create' => Pages\CreateTributo::route('/create'),
            'edit' => Pages\EditTributo::route('/{record}/edit'),
        ];
    }
}
