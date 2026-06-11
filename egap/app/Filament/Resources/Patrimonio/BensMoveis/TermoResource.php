<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\TermoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Agendamento\Materiais;
use App\Models\Agendamento\Solicitacao;
use App\Models\Patrimonio\BensMoveis\Termo;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TermoResource extends Resource
{
    protected static ?string $model = Termo::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Termos de Responsabilidade';

    protected static ?string $modelLabel = 'Termo de Responsabilidade';

    protected static ?string $pluralModelLabel = 'Termos de Responsabilidade';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'bens-moveis/termos';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Termos de Responsabilidade')
                    ->description('Identificação, vínculo com pedido e situação atual do termo.')
                    ->icon('heroicon-o-document-text')
                    ->columns(3)
                    ->schema([
                        TextInput::make('num_termo')
                            ->label('Num. Termo')
                            ->placeholder('Informe o número')
                            ->required(),

                        TextInput::make('ano_termo')
                            ->label('Ano Termo')
                            ->numeric()
                            ->default(now()->year)
                            ->placeholder((string) now()->year)
                            ->required(),

                        Grid::make(1)->columnSpan(1)->schema([
                            Placeholder::make('atualizado_em_display')
                                ->label('Atualizado em')
                                ->content(fn ($record) => $record?->updated_at?->format('d/m/Y H:i') ?? '-'),

                            Placeholder::make('atualizado_por_display')
                                ->label('Atualizado por')
                                ->content(fn ($record) => $record?->atualizado_por ?? 'Sistema'),
                        ]),

                        Select::make('pedido_no')
                            ->label('Pedido No')
                            ->placeholder('Selecione o pedido')
                            ->searchable()
                            ->native(false)
                            ->options(function () {
                                return Solicitacao::select('id', 'date_time')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        $ano = Carbon::parse($item->date_time)->format('Y');

                                        return [$item->id => "{$item->id}/{$ano} - Prot. {$item->id}"];
                                    });
                            })
                            ->getSearchResultsUsing(fn (string $search): array => Solicitacao::where('id', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    $ano = Carbon::parse($item->date_time)->format('Y');

                                    return [$item->id => "{$item->id}/{$ano} - Prot. {$item->id}"];
                                })
                                ->toArray()),

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

                Section::make('Anexos do Termo')
                    ->description('Anexe o documento e registre informações complementares.')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Grid::make(4)->schema([
                            FileUpload::make('arquivo_digital')
                                ->label('Arquivo Digital')
                                ->directory('termos-patrimonio')
                                ->acceptedFileTypes(['application/pdf'])
                                ->columnSpan(2),

                            Select::make('situacao')
                                ->label('Situação')
                                ->options([
                                    'Validado' => 'Validado',
                                    'Pendente' => 'Pendente',
                                ])
                                ->native(false)
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
        return TableDefaults::apply($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('transferencias'))
            ->columns([
                TableColumns::text('termo_completo', 'Termo', isFirstColumn: true)
                    ->searchable(['num_termo', 'ano_termo'])
                    ->badge()
                    ->weight('medium'),

                TableColumns::text('observacao', 'Observação')
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->observacao)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('arquivo_virtual')
                    ->label('Arquivo Digital')
                    ->getStateUsing(fn ($record) => $record->situacao_entrega === 'Validado' ? "termo_{$record->num_termo}.html" : '-')
                    ->color('primary')
                    ->weight('bold')
                    ->url(fn ($record) => $record->situacao_entrega === 'Validado' ? route('termo.imprimir', ['id' => $record->id]) : null)
                    ->openUrlInNewTab(),

                TableColumns::text('situacao_entrega', 'Situação')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Validado' => 'success',
                        'Em rota' => 'info',
                        'Cancelado' => 'danger',
                        'Entregue' => 'success',
                        default => 'warning',
                    }),

                TableColumns::text('transferencias_count', 'Materiais')
                    ->getStateUsing(fn ($record) => "{$record->transferencias_count} materiais")
                    ->searchable(false)
                    ->icon('heroicon-o-rectangle-stack')
                    ->color('info')
                    ->weight('medium')
                    ->disabledClick(false)
                    ->action(
                        Action::make('ver_materiais_coluna')
                            ->modalHeading(fn ($record) => "Materiais Vinculados ao Termo: {$record->termo_completo}")
                            ->modalWidth(MaxWidth::ExtraLarge)
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Fechar')
                            ->infolist([
                                RepeatableEntry::make('transferencias')
                                    ->label('')
                                    ->schema([
                                        \Filament\Infolists\Components\Grid::make(4)->schema([
                                            TextEntry::make('NumPatrimonio')
                                                ->label('Nº Patrimônio')
                                                ->weight('bold')
                                                ->color('primary'),
                                            TextEntry::make('MaterialDescricao')
                                                ->label('Descrição do Bem')
                                                ->columnSpan(3),
                                            TextEntry::make('UnidadeJudiciariaAtual')
                                                ->label('Unidade Judiciária'),
                                            TextEntry::make('SetorAtual')
                                                ->label('Setor'),
                                            TextEntry::make('ComplementoAtual')
                                                ->label('Complemento')
                                                ->default('-'),
                                            TextEntry::make('date_time')
                                                ->label('Movimentado em')
                                                ->dateTime('d/m/Y H:i'),
                                        ]),
                                    ])->grid(1),
                            ]),
                    ),
                TableColumns::dateTime('atualizado_em', 'Atualizado em')
                    ->toggleable(isToggledHiddenByDefault: true),
                TableColumns::text('responsavelRef.name', 'Atualizado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('situacao_entrega')
                    ->label('Situação')
                    ->options([
                        'Reservado' => 'Reservado',
                        'Em rota' => 'Em rota',
                        'Entregue' => 'Entregue',
                        'Validado' => 'Validado',
                        'Cancelado' => 'Cancelado',
                    ]),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Visualizar Detalhes'),
                    Tables\Actions\EditAction::make()->label('Editar Termo'),

                    Action::make('ver_materiais_menu')
                        ->label('Ver materiais')
                        ->icon('heroicon-o-rectangle-stack')
                        ->color('info')
                        ->modalHeading(fn ($record) => "Materiais do Termo: {$record->termo_completo}")
                        ->modalWidth(MaxWidth::ExtraLarge)
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->infolist([
                            RepeatableEntry::make('transferencias')
                                ->label('')
                                ->schema([
                                    \Filament\Infolists\Components\Grid::make(4)->schema([
                                        TextEntry::make('NumPatrimonio')
                                            ->label('Nº Patrimônio')
                                            ->weight('bold'),
                                        TextEntry::make('MaterialDescricao')
                                            ->label('Descrição')
                                            ->columnSpan(3),
                                        TextEntry::make('UnidadeJudiciariaAtual')
                                            ->label('Comarca/Unidade'),
                                        TextEntry::make('SetorAtual')
                                            ->label('Setor'),
                                        TextEntry::make('date_time')
                                            ->label('Data')
                                            ->dateTime('d/m/Y H:i'),
                                    ]),
                                ]),
                        ]),

                    Action::make('imprimir')
                        ->label('Imprimir termo')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn ($record) => route('termo.imprimir', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    Action::make('encaminhar')
                        ->label('Encaminhar para Logística')
                        ->icon('heroicon-o-arrow-uturn-right')
                        ->color('gray')
                        ->form([
                            Textarea::make('observacao_logistica')
                                ->label('Observação')
                                ->placeholder('Justificativa para a logística...')
                                ->required(),
                        ])
                        ->action(function (Termo $record, array $data) {
                            DB::connection('egap')->transaction(function () use ($record, $data) {
                                $idSolicitacao = Solicitacao::insertGetId([
                                    'date_time' => now(),
                                    'id_user' => auth()->id(),
                                    'tipo' => 2,
                                    'id_situacao' => 6,
                                    'justificativa' => $data['observacao_logistica'],
                                    'local_saida' => 'Seção de Patrimônio',
                                ]);

                                Materiais::insert([
                                    'date_time' => now(),
                                    'id_user' => auth()->id(),
                                    'id_termo' => $record->id,
                                    'id_solicitacao' => $idSolicitacao,
                                ]);

                                $record->update([
                                    'situacao_entrega' => 'Em rota',
                                    'pedido_no' => $idSolicitacao,
                                ]);
                            });
                            Notification::make()->title('Encaminhado com sucesso')->success()->send();
                        })
                        ->visible(fn ($record) => $record->situacao_entrega === 'Validado'),
                ])->label('Opções')->button(),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
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
