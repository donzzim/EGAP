<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\BaixaResource\Pages;
use App\Models\Patrimonio\BensMoveis\Baixa;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\ItemBaixa;
use Filament\Forms;
use Filament\Forms\Components\{DatePicker, Grid, Repeater, Section, Select, Textarea, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Support\Facades\DB;

class BaixaResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $model = Baixa::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Baixa de bens';
    protected static ?string $modelLabel = 'Baixa';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados da Baixa')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('NumeroProcesso')
                                ->label('Processo Nº')
                                ->options(fn () => Baixa::query()->whereNotNull('NumeroProcesso')->distinct()->pluck('NumeroProcesso', 'NumeroProcesso')->toArray())
                                ->searchable()
                                ->required()
                                ->unique(table: 'mat_baixa', column: 'NumeroProcesso', ignoreRecord: true)
                                ->validationMessages(['unique' => 'Processo já baixado! Verifique o histórico do Tribunal.']),

                            DatePicker::make('DataBaixa')->label('Data da Baixa')->default(now())->required(),
                            TextInput::make('Requisitante')->label('Requisitante'),
                            TextInput::make('RequisitanteCnpj')->label('CNPJ'),
                        ]),
                        Textarea::make('Endereco')->label('Endereço')->rows(2),
                        Textarea::make('Observacao')->label('Observação')->rows(3),
                    ]),

                Section::make('Materiais (Itens da Baixa)')
                    ->schema([
                        Repeater::make('itens')
                            ->relationship('itens')
                            ->schema([
                                Select::make('id_bem')
                                    ->label('Patrimônio')
                                    ->getSearchResultsUsing(function (string $search) {
                                        return BemMovel::query()->where('SituacaoBem', 1)
                                            ->where('NumPatrimonio', 'like', "%{$search}%")
                                            ->limit(50)->pluck('NumPatrimonio', 'id');
                                    })
                                    ->getOptionLabelUsing(fn ($value) => BemMovel::find($value)?->NumPatrimonio ?? $value)
                                    ->searchable()->required()->distinct()->columnSpan(3),

                                Select::make('id_situacao')
                                    ->label('Status Destino')
                                    ->options([3 => 'Baixado', 2 => 'Inservível'])
                                    ->default(3)->required()->columnSpan(1),
                            ])->columns(4)->createItemButtonLabel('Adicionar outro bem')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('NumeroProcesso')
                    ->label('Processo Nº')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('DataBaixa')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Requisitante')
                    ->label('Requisitante')
                    ->limit(30),
                Tables\Columns\TextColumn::make('itens_count')
                    ->alignCenter()
                    ->counts('itens')
                    ->label('Qtd. Materiais'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Editar'),
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip('Visualizar'),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir'),
                Action::make('baixar_bens_processo')
                    ->hiddenLabel()
                    ->tooltip('Baixar bens do processo')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        DB::connection('egap')->transaction(function () use ($record) {
                            $itens = ItemBaixa::where('id_baixa', $record->id)->get();
                            foreach ($itens as $item) {
                                BemMovel::where('id', $item->id_bem)
                                    ->update(['SituacaoBem' => $item->id_situacao, 'DataBaixa' => $record->DataBaixa]);
                            }
                        });
                        Notification::make()->title('Bens baixados definitivamente!')->success()->send();
                    }),

                Action::make('imprimir_bens_baixa')
                    ->hiddenLabel()
                    ->tooltip('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->disabled(fn ($record) => !ItemBaixa::where('id_baixa', $record->id)->exists())
                    ->url(fn ($record) => route('termo.baixa.imprimir', ['id' => $record->id]))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return
            [
                'index' => Pages\ListBaixas::route('/'),
                'create' => Pages\CreateBaixa::route('/create'),
                'edit' => Pages\EditBaixa::route('/{record}/edit')
            ];
    }
}
