<?php

namespace App\Filament\Resources\Agendamento;

use App\Filament\Resources\Agendamento\TransporteResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Agendamento\Equipe;
use App\Models\Agendamento\Frota;
use App\Models\Agendamento\Solicitacao;
use App\Models\Cadastro\Setores;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;

class TransporteResource extends Resource
{
    protected static ?string $model = Solicitacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Agendamento';
    protected static ?string $modelLabel = 'Transporte de Carga';
    protected static ?string $pluralModelLabel = 'Transporte de Carga';
    protected static ?string $navigationLabel = 'Transporte de Carga';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Transporte')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Transporte de Carga')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('tipo')
                                                ->label('Tipo de Agendamento')
                                                ->options([
                                                    1 => 'Agendamento de Veículos',
                                                    2 => 'Transporte de Carga',
                                                ])
                                                ->default(2)
                                                ->disabled()
                                                ->dehydrated(true)
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\Select::make('id_situacao')
                                                ->label('Situação')
                                                ->relationship('idSituacaoRef', 'Descricao')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\Select::make('id_solicitante')
                                                ->label('Solicitante')
                                                ->relationship('idSolicitanteRef', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\Select::make('regiao')
                                                ->label('Região')
                                                ->relationship('regiaoRef', 'regiao')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\Select::make('unidade_solicitante')
                                                ->label('Unidade Solicitante')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->options(fn () => Setores::query()
                                                    ->whereColumn('id', 'CodigodaUO')
                                                    ->orderBy('UnidadeOrganizacional')
                                                    ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                                    ->toArray()
                                                )
                                                ->afterStateUpdated(fn (Set $set) => $set('setor_solicitante', null)),

                                            Forms\Components\Select::make('setor_solicitante')
                                                ->label('Setor Solicitante')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false)
                                                ->options(fn (Get $get) => Setores::query()
                                                    ->when(
                                                        $get('unidade_solicitante'),
                                                        fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                                    )
                                                    ->orderBy('Setor')
                                                    ->pluck('Setor', 'id')
                                                    ->toArray()
                                                )
                                                ->disabled(fn (Get $get) => blank($get('unidade_solicitante'))),

                                            Forms\Components\TextInput::make('local_saida')
                                                ->label('Local Saída')
                                                ->maxLength(255)
                                                ->required(),

                                            Forms\Components\TextInput::make('local_destino')
                                                ->label('Local Destino')
                                                ->maxLength(255)
                                                ->required(),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Textarea::make('justificativa')
                                                ->label('Detalhamento')
                                                ->rows(6)
                                                ->autosize()
                                                ->required(),

                                            Forms\Components\Textarea::make('motivo_cancelamento')
                                                ->label('Motivo Cancelamento')
                                                ->rows(6)
                                                ->autosize(),
                                        ]),
                                ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('Agendar')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\DatePicker::make('data_inicio')
                                                ->label('Data início')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\TimePicker::make('hora_inicio')
                                                ->label('Hora Início')
                                                ->seconds(false)
                                                ->required(),

                                            Forms\Components\DatePicker::make('data_termino')
                                                ->label('Data Término')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->required(),

                                            Forms\Components\TimePicker::make('hora_termino')
                                                ->label('Hora Término')
                                                ->seconds(false)
                                                ->required(),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Textarea::make('finalizar')
                                                ->label('Observação')
                                                ->rows(6)
                                                ->autosize(),

                                            Forms\Components\Textarea::make('motivo_edicao')
                                                ->label('Motivo da Edição')
                                                ->rows(6)
                                                ->autosize(),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('agendamento_pai')
                                                ->label('Agendamento Pai'),

                                            Forms\Components\DateTimePicker::make('date_time')
                                                ->label('Data Alteração')
                                                ->seconds(false)
                                                ->native(false),
                                        ]),

                                    Forms\Components\TextInput::make('anexo')
                                        ->label('Anexo')
                                        ->maxLength(255),
                                ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('Requisição/Termos')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\DateTimePicker::make('data_alteracao')
                                                ->label('Atualizado em')
                                                ->seconds(false)
                                                ->native(false),

                                            Forms\Components\Select::make('id_user')
                                                ->label('Atualizado por')
                                                ->relationship('idUserRef', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Por favor selecione')
                                                ->native(false),

                                            Forms\Components\TextInput::make('requisicao')
                                                ->label('Requisição')
                                                ->placeholder('Por favor selecione'),

                                            Forms\Components\TextInput::make('termo')
                                                ->label('Termo')
                                                ->placeholder('Por favor selecione'),
                                        ]),
                                ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('age_recursos')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Placeholder::make('age_recursos_info')
                                        ->label('')
                                        ->content('Campos relacionados a age_recursos devem ser vinculados ao model/tabela correspondente.'),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('age_recursos_id_user')
                                                ->label('id user')
                                                ->dehydrated(false),

                                            Forms\Components\TextInput::make('age_recursos_condutor')
                                                ->label('condutor')
                                                ->dehydrated(false),

                                            Forms\Components\TextInput::make('age_recursos_veiculo')
                                                ->label('veiculo')
                                                ->dehydrated(false),

                                            Forms\Components\TextInput::make('age_recursos_id_solicitacao')
                                                ->label('id solicitacao')
                                                ->dehydrated(false),

                                            Forms\Components\Textarea::make('age_recursos_observacao')
                                                ->label('observacao')
                                                ->rows(6)
                                                ->dehydrated(false)
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ]),
                ])
                ->persistTabInQueryString()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->modifyQueryUsing(fn (Builder $query, $livewire) => self::hasActiveFilter($livewire)
                ? $query
                : $query->whereRaw('1 = 0'))
            ->emptyStateIcon('heroicon-o-funnel')
            ->emptyStateHeading(fn ($livewire): string => self::hasActiveFilter($livewire)
                ? 'Nenhum registro encontrado'
                : 'Selecione um filtro para começar')
            ->emptyStateDescription(fn ($livewire): string => self::hasActiveFilter($livewire)
                ? 'Não há registros para os filtros selecionados.'
                : 'A tabela só carrega registros após aplicar o filtro de Situação ou Unidade Solicitante.')
            ->columns([
                TableColumns::text('id', '#', true)
                    ->badge(),

                TableColumns::text('idSituacaoRef.Descricao', 'Situação')
                    ->badge()
                    ->color(fn (?string $state) => match (mb_strtolower($state ?? '')) {
                        'pendente' => 'warning',
                        'aprovado', 'deferido', 'ativo' => 'success',
                        'cancelado', 'indeferido' => 'danger',
                        default => 'gray',
                    }),

                TableColumns::text('idSolicitanteRef.name', 'Solicitante'),

                TableColumns::text('setorSolicitanteRef.Setor', 'Setor'),

                TableColumns::text('local_saida', 'Local de saída'),

                TableColumns::text('local_destino', 'Local de destino'),

                TableColumns::text('justificativa_lista', 'Detalhamento')
                    ->tooltip(fn ($state) => $state),

                TableColumns::date('data_inicio', 'Data de início'),

                TableColumns::text('requisicao', 'Requisição'),

                TableColumns::text('termo', 'Termo'),
            ])
            ->filters([
                SelectFilter::make('id_situacao')
                    ->label('Situação')
                    ->options([
                        "8" => "Agendado",
                        "6" => "Em análise"
                    ]),
                SelectFilter::make('unidade_solicitante')
                    ->label('Unidade Solicitante')
                    ->columnSpan(2)
                    ->options(fn () => Setores::query()
                        ->whereColumn('id', 'CodigodaUO')
                        ->orderBy('UnidadeOrganizacional')
                        ->pluck('UnidadeOrganizacional', 'CodigoPai')
                        ->toArray()
                    )
                    ->searchable(),

            ], Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                ...TableDefaults::actions(),
                ActionGroup::make([
                    self::agendarTableAction(),
                    self::imprimirMateriaisTableAction(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    private static function hasActiveFilter($livewire): bool
    {
        return collect(['id_situacao', 'unidade_solicitante'])
            ->contains(fn (string $filter) => filled(data_get($livewire, "tableFilters.{$filter}.value")));
    }

    private static function agendarTableAction(): Action
    {
        return Action::make('agendar')
            ->hiddenLabel()
            ->tooltip('Agendar')
            ->icon('heroicon-o-calendar')
            ->modalWidth('4xl')
            ->modalHeading(fn ($record) => 'Agendamento - Transporte de carga #' . $record->id)
            ->modalDescription('Defina o período, a equipe e os veículos que atenderão esta solicitação.')
            ->modalSubmitActionLabel('Agendar')
            ->form([
                Section::make('Período do Agendamento')
                    ->description('Informe o intervalo de datas em que o transporte será realizado.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        DatePicker::make('data_inicio')
                            ->label('Data início')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required()
                            ->columnSpan(1),

                        DatePicker::make('data_fim')
                            ->label('Data fim')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required()
                            ->afterOrEqual('data_inicio')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Recursos Alocados')
                    ->description('Selecione os motoristas/carregadores e os veículos que atenderão este transporte.')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Select::make('motoristas_carregadores')
                            ->label('Motoristas e Carregadores')
                            ->placeholder('Selecione um ou mais integrantes da equipe')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->options(fn () => Equipe::query()
                                ->with('idPessoaRef')
                                ->get()
                                ->sortBy(fn (Equipe $equipe) => $equipe->idPessoaRef->name ?? '')
                                ->mapWithKeys(fn (Equipe $equipe) => [
                                    $equipe->id => trim(($equipe->idPessoaRef->name ?? '-') . ' - ' . $equipe->funcao),
                                ])
                                ->toArray()
                            )
                            ->columnSpan(1),

                        Select::make('veiculos')
                            ->label('Veículos')
                            ->placeholder('Selecione um ou mais veículos')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->options(fn () => Frota::query()
                                ->orderBy('descricao')
                                ->get()
                                ->mapWithKeys(fn (Frota $frota) => [
                                    $frota->id => $frota->descricao . ' - ' . ($frota->placa ?? 'Sem placa'),
                                ])
                                ->toArray()
                            )
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Observações')
                    ->description('Adicione informações complementares sobre o agendamento, se necessário.')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Textarea::make('observacao')
                            ->label('')
                            ->placeholder('Digite aqui alguma observação ou informação adicional...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ])
            ->action(function (array $data): void {

            });
    }

    private static function imprimirMateriaisTableAction(): Action
    {
        return Action::make('imprimir_materiais')
            ->hiddenLabel()
            ->tooltip('Imprimir materiais')
            ->icon('heroicon-o-printer')
            ->visible(fn (Solicitacao $record): bool => $record->materiaisRef()->exists())
            ->url(fn (Solicitacao $record): string => route('agendamento.materiais.imprimir', ['id' => $record->id]))
            ->openUrlInNewTab();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransportes::route('/'),
            'create' => Pages\CreateTransporte::route('/create'),
            'edit' => Pages\EditTransporte::route('/{record}/edit'),
        ];
    }
}
