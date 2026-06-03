<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Resources\Patrimonio\BensMoveis\ConciliacaoResource\Pages;
use App\Filament\Clusters\PatrimonioCluster;
use App\Models\Patrimonio\BensMoveis\Conciliacao;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Textarea, Grid, Section, Tabs};
use Filament\Pages\SubNavigationPosition;
use Filament\Notifications\Notification;

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

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Abas de Conciliação')
                    ->tabs([
                        // ✅ ABA 1: CADASTRO INDIVIDUAL
                        Tabs\Tab::make('Individual')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('numero_patrimonio')
                                        ->label('Número Patrimônio')
                                        ->searchable()
                                        ->live()
                                        ->getSearchResultsUsing(fn (string $search) =>
                                            BemMovel::where('NumPatrimonio', 'like', "%{$search}%")->limit(20)->pluck('NumPatrimonio', 'NumPatrimonio')
                                        )
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            $bem = BemMovel::where('NumPatrimonio', $state)->first();
                                            if ($bem) {
                                                $set('descricao', $bem->Descricao);
                                                $set('valor_aquisicao', $bem->ValorAquisicao);
                                            }
                                        }),

                                    TextInput::make('patrimonio_desmembrado')->label('Desmembrado'),

                                    Textarea::make('descricao')->label('Descrição')->rows(3)->columnSpanFull(),

                                    DatePicker::make('data_conciliacao')->label('Data Conciliação')->default(now())->required(),

                                    Select::make('comarca')
                                        ->label('Comarca')
                                        ->relationship('comarcaRef', 'Setor')
                                        ->searchable(),

                                    TextInput::make('valor_aquisicao')->label('Valor')->numeric()->prefix('R$'),
                                ]),
                            ]),

                        // ✅ ABA 2: PROCESSAMENTO EM MASSA (Substitui a página antiga)
                        Tabs\Tab::make('Processar em Massa')
                            ->icon('heroicon-m-squares-plus')
                            ->schema([
                                Section::make('Conciliação Rápida')
                                    ->description('Cole aqui a lista de patrimónios para conciliar vários de uma só vez.')
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
                                                    // Aqui entra a lógica de processamento do textarea
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
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('numero_patrimonio')->label('Patrimônio')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('descricao')->label('Descrição')->limit(40),
                Tables\Columns\TextColumn::make('comarcaRef.Setor')->label('Comarca'),
                Tables\Columns\TextColumn::make('data_conciliacao')->label('Data')->date('d/m/Y'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
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
