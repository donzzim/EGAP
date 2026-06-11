<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\ConciliacaoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\Conciliacao;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ConciliacaoResource extends Resource
{
    protected static ?string $model = Conciliacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Conciliação (Inventário)';

    protected static ?string $pluralModelLabel = 'Conciliações';

    protected static ?string $modelLabel = 'Conciliação';

    protected static ?int $navigationSort = 15;

    protected static ?string $slug = 'bens-moveis/conciliacoes';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Abas de Conciliação')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Individual')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Section::make('Dados da Conciliação')
                                    ->description('Selecione o patrimônio e confira os dados recuperados do cadastro.')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('numero_patrimonio')
                                                ->label('Número do Patrimônio')
                                                ->placeholder('Busque pelo patrimônio')
                                                ->searchable()
                                                ->live()
                                                ->getSearchResultsUsing(fn (string $search) => BemMovel::where('NumPatrimonio', 'like', "%{$search}%")
                                                    ->limit(20)
                                                    ->pluck('NumPatrimonio', 'NumPatrimonio'))
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    $bem = BemMovel::where('NumPatrimonio', $state)->first();
                                                    if ($bem) {
                                                        $set('descricao', $bem->Descricao);
                                                        $set('valor_aquisicao', $bem->ValorAquisicao);
                                                    }
                                                }),
                                            TextInput::make('patrimonio_desmembrado')->label('Desmembrado'),
                                            Textarea::make('descricao')->label('Descrição')->rows(3)->columnSpanFull(),
                                            DatePicker::make('data_conciliacao')
                                                ->label('Data da Conciliação')
                                                ->default(now())
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->required(),
                                            Select::make('comarca')
                                                ->label('Comarca')
                                                ->relationship('comarcaRef', 'Setor')
                                                ->placeholder('Selecione a comarca')
                                                ->searchable()
                                                ->preload()
                                                ->native(false),
                                            TextInput::make('valor_aquisicao')->label('Valor')->numeric()->prefix('R$'),
                                        ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Processar em Massa')
                            ->icon('heroicon-m-squares-plus')
                            ->schema([
                                Section::make('Conciliação Rápida')
                                    ->description('Cole aqui a lista de patrimónios para conciliar vários de uma só vez.')
                                    ->icon('heroicon-o-squares-plus')
                                    ->schema([
                                        Textarea::make('massa_dados')
                                            ->label('Dados para Conciliar')
                                            ->placeholder('Ex: 10020, 10021, 10022...')
                                            ->rows(12),

                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('processar')
                                                ->label('Executar Conciliação')
                                                ->color('success')
                                                ->requiresConfirmation()
                                                ->action(function (Forms\Get $get) {
                                                    Notification::make()->title('Processado com sucesso!')->success()->send();
                                                }),
                                        ]),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('numero_patrimonio', 'Patrimônio', isFirstColumn: true)
                    ->badge()
                    ->copyable()
                    ->weight('medium'),
                TableColumns::text('descricao', 'Descrição')
                    ->limit(45)
                    ->tooltip(fn ($record): ?string => $record->descricao),
                TableColumns::text('comarcaRef.Setor', 'Comarca')
                    ->wrap(),
                TableColumns::date('data_conciliacao', 'Data'),
                TableColumns::money('valor_aquisicao', 'Valor de Aquisição')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConciliacaos::route('/'),
            'create' => Pages\CreateConciliacao::route('/create'),
            'edit' => Pages\EditConciliacao::route('/{record}/edit'),
        ];
    }
}
