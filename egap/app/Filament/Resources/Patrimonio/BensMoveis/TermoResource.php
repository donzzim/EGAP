<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\TermoResource\Pages;
use App\Models\Egap\Patrimonio\BensMoveis\Termo;
use App\Filament\Egap\Clusters\PatrimonioCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Forms\Components\{TextInput, Textarea, Grid, Section, Placeholder, Select, FileUpload};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TermoResource extends Resource
{
    protected static ?string $model = Termo::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $cluster = PatrimonioCluster::class;
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Termos de Responsabilidade';
    protected static ?string $pluralModelLabel = 'Termos de Responsabilidade';
    protected static ?int $navigationSort = 5;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SEÇÃO 1: Cabeçalho do Termo (image_e6a9f6.png)
                Section::make('Termos de Responsabilidade')
                    ->columns(3)
                    ->schema([
                        TextInput::make('num_termo')
                            ->label('Num. Termo')
                            ->required(),
                        
                        TextInput::make('ano_termo')
                            ->label('Ano Termo')
                            ->numeric()
                            ->required(),

                        Grid::make(1)->columnSpan(1)->schema([
                            Placeholder::make('atualizado_em_display')
                                ->label('Atualizado em')
                                ->content(fn ($record) => $record?->updated_at?->format('d/m/Y H:i') ?? '-'),
                            
                            Placeholder::make('atualizado_por_display')
                                ->label('Atualizado por')
                                ->content(fn ($record) => $record?->atualizado_por ?? 'Sistema'),
                        ]),

                        // PEDIDO NO: Mantendo a conexão 'egap' para evitar Erro 500
                        Select::make('pedido_no')
                            ->label('Pedido No')
                            ->placeholder('Por favor selecione')
                            ->searchable()
                            ->options(function () {
                                return DB::connection('egap')->table('age_solicitacao')
                                    ->select('id', 'date_time')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        $ano = Carbon::parse($item->date_time)->format('Y');
                                        return [$item->id => "{$item->id}/{$ano} - Prot. {$item->id}"];
                                    });
                            })
                            ->getSearchResultsUsing(fn (string $search): array => 
                                DB::connection('egap')->table('age_solicitacao')
                                    ->where('id', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        $ano = Carbon::parse($item->date_time)->format('Y');
                                        return [$item->id => "{$item->id}/{$ano} - Prot. {$item->id}"];
                                    })
                                    ->toArray()
                            ),

                        Select::make('situacao_entrega')
                            ->label('Situação Entrega')
                            ->options([
                                'Reservado' => 'Reservado',
                                'Em rota' => 'Em rota',
                                'Entregue' => 'Entregue',
                                'Encaminhado para Logística' => 'Encaminhado para Logística',
                            ])
                            ->native(false),
                    ]),

                // SEÇÃO 2: Anexos e Observações
                Section::make('Anexos do Termo')
                    ->schema([
                        Grid::make(4)->schema([
                            FileUpload::make('arquivo_digital')
                                ->label('Arquivo Digital')
                                ->directory('termos-patrimonio')
                                ->columnSpan(2),

                            Select::make('situacao') 
                                ->label('Situação')
                                ->options([
                                    'Validado' => 'Validado',
                                    'Pendente' => 'Pendente',
                                ])
                                ->default('Validado'),

                            TextInput::make('web_status')
                                ->label('WEB')
                                ->numeric()
                                ->default(0),
                        ]),

                        Textarea::make('observacao')
                            ->label('Observação')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('termo_completo')
                    ->label('Termo')
                    ->searchable(['num_termo', 'ano_termo'])
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observação')
                    ->limit(40)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('arquivo_virtual')
                    ->label('Arquivo Digital')
                    ->getStateUsing(fn ($record) => $record->situacao_entrega === 'Validado' ? "termo_{$record->num_termo}.html" : "-")
                    ->color('primary')
                    ->weight('bold')
                    ->url(fn ($record) => $record->situacao_entrega === 'Validado' ? route('termo.imprimir.dinamico', ['id' => $record->id]) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('situacao_entrega')
                    ->label('Situação')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Validado' => 'success',
                        'Em rota' => 'info',
                        'Cancelado' => 'danger',
                        'Entregue' => 'success',
                        default => 'warning',
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Visualizar Detalhes'),
                    Tables\Actions\EditAction::make()->label('Editar Termo'),

                    // ✅ AÇÃO ADICIONADA: Imprimir conforme o print image_dbd397.png
                    Action::make('imprimir')
                        ->label('Imprimir termo')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn ($record) => route('termo.imprimir.dinamico', ['id' => $record->id]))
                        ->openUrlInNewTab(),
                    
                    Action::make('encaminhar')
                        ->label('Encaminhar para Logística')
                        ->icon('heroicon-o-arrow-uturn-right')
                        ->color('gray')
                        ->form([
                            Forms\Components\Textarea::make('observacao_logistica')
                                ->label('Observação')
                                ->placeholder('Justificativa para a logística...')
                                ->required(),
                        ])
                        ->action(function (Termo $record, array $data) {
                            DB::connection('egap')->transaction(function () use ($record, $data) {
                                $idSolicitacao = DB::connection('egap')->table('age_solicitacao')->insertGetId([
                                    'date_time' => now(),
                                    'id_user' => auth()->id(),
                                    'tipo' => 2,
                                    'id_situacao' => 6,
                                    'justificativa' => $data['observacao_logistica'],
                                    'local_saida' => 'Seção de Patrimônio'
                                ]);

                                DB::connection('egap')->table('age_materiais')->insert([
                                    'date_time' => now(),
                                    'id_user' => auth()->id(),
                                    'id_termo' => $record->id,
                                    'id_solicitacao' => $idSolicitacao
                                ]);

                                $record->update([
                                    'situacao_entrega' => 'Em rota',
                                    'pedido_no' => $idSolicitacao
                                ]);
                            });
                            Notification::make()->title('Encaminhado com sucesso')->success()->send();
                        })
                        ->visible(fn ($record) => $record->situacao_entrega === 'Validado'),
                ])->label('Opções')->button(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTermos::route('/'),
            'create' => Pages\CreateTermo::route('/create'),
            'edit' => Pages\EditTermo::route('/{record}/edit'),
        ];
    }
}