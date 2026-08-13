<?php

namespace App\Filament\Resources\Agendamento;

use App\Models\Agendamento\Equipe;
use App\Models\Agendamento\Frota;
use App\Models\Agendamento\Recursos;
use App\Models\Almoxarifado\SituacaoPedido;
use App\Models\Patrimonio\BensImoveis\Situacao;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;
use App\Filament\Resources\Agendamento\SolicitacaoResource\Pages\ListSolicitacaos;
use App\Filament\Resources\Agendamento\SolicitacaoResource\Pages\CreateSolicitacao;
use App\Filament\Resources\Agendamento\SolicitacaoResource\Pages\EditSolicitacao;
use App\Filament\Resources\Agendamento\SolicitacaoResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Agendamento\Solicitacao;
use App\Models\Cadastro\Setores;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

class AgendamentoResource extends Resource
{
    protected static ?string $model = Solicitacao::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static string | \UnitEnum | null $navigationGroup = 'Agendamento';
    protected static ?string $slug = 'agendamento/agendamento-de-veiculos';
    protected static ?string $modelLabel = 'Solicitação';
    protected static ?string $pluralModelLabel = 'Solicitações';
    protected static ?string $navigationLabel = 'Agendamento de Veículos';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            Tabs::make('Solicitação')
                ->tabs([
                    Tab::make('Dados principais')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('Informações gerais')
                                ->description('Dados centrais da solicitação e vínculos administrativos.')
                                ->schema([
                                    Select::make('id_solicitante')
                                        ->label('Solicitante')
                                        ->relationship('idSolicitanteRef', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Selecione o solicitante')
                                        ->native(false)
                                        ->required()
                                        ->markAsRequired(),

                                    Select::make('id_situacao')
                                        ->label('Situação')
                                        ->relationship('idSituacaoRef', 'Descricao')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Selecione a situação')
                                        ->native(false)
                                        ->required()
                                        ->markAsRequired(),

                                    Select::make('tipo')
                                        ->label('Tipo')
                                        ->options([
                                            '1' => 'Agendamento de Veículos',
                                            '2' => 'Transporte de Carga',
                                        ])
                                        ->required()
                                        ->native(false)
                                        ->markAsRequired()
                                        ->placeholder('Informe o tipo da solicitação'),

                                    Select::make('regiao')
                                        ->label('Região')
                                        ->relationship('regiaoRef', 'regiao')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Selecione a região')
                                        ->native(false)
                                        ->required()
                                        ->markAsRequired(),

                                    Select::make('unidade_solicitante')
                                        ->label('Unidade Judiciária')
                                        ->required()
                                        ->markAsRequired()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->options(fn () => Setores::query()
                                            ->whereColumn('id', 'CodigodaUO')
                                            ->orderBy('UnidadeOrganizacional')
                                            ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                            ->toArray()
                                        )
                                        ->afterStateUpdated(fn (Set $set) => $set('setor_solicitante', null)),

                                    Select::make('setor_solicitante')
                                        ->label('Setor solicitante')
                                        ->required()
                                        ->markAsRequired()
                                        ->searchable()
                                        ->preload()
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
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl' => 3,
                                ]),
                        ]),

                    Tab::make('Período')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Section::make('Datas e horários')
                                ->description('Defina o intervalo previsto para a solicitação.')
                                ->schema([
                                    DatePicker::make('data_inicio')
                                        ->label('Data início')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->closeOnDateSelection()
                                        ->required()
                                        ->markAsRequired(),

                                    TimePicker::make('hora_inicio')
                                        ->label('Hora início')
                                        ->seconds(false)
                                        ->required()
                                        ->markAsRequired(),

                                    DatePicker::make('data_termino')
                                        ->label('Data término')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->closeOnDateSelection()
                                        ->required()
                                        ->markAsRequired(),

                                    TimePicker::make('hora_termino')
                                        ->label('Hora término')
                                        ->seconds(false)
                                        ->required()
                                        ->markAsRequired(),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'xl' => 4,
                                ]),
                        ]),

                    Tab::make('Locais e observações')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Section::make('Deslocamento')
                                ->description('Informações de origem e destino.')
                                ->schema([
                                    TextInput::make('local_saida')
                                        ->label('Local de saída')
                                        ->placeholder('Informe o local de saída')
                                        ->maxLength(255)
                                        ->required()
                                        ->markAsRequired(),

                                    TextInput::make('local_destino')
                                        ->label('Local de destino')
                                        ->placeholder('Informe o local de destino')
                                        ->maxLength(255)
                                        ->required()
                                        ->markAsRequired(),
                                ])
                                ->columns(2),

                            Section::make('Observações e justificativas')
                                ->description('Detalhamentos complementares da solicitação.')
                                ->schema([
                                    Textarea::make('justificativa')
                                        ->label('Justificativa')
                                        ->rows(5)
                                        ->autosize()
                                        ->columnSpanFull()
                                        ->placeholder('Descreva a justificativa da solicitação')
                                        ->required()
                                        ->markAsRequired(),

                                    Textarea::make('motivo_cancelamento')
                                        ->label('Motivo do cancelamento')
                                        ->rows(4)
                                        ->autosize()
                                        ->columnSpanFull()
                                        ->placeholder('Informe o motivo do cancelamento, se houver'),

                                    Textarea::make('motivo_edicao')
                                        ->label('Motivo da edição')
                                        ->rows(4)
                                        ->autosize()
                                        ->columnSpanFull()
                                        ->placeholder('Informe o motivo da edição, se houver'),

                                    Textarea::make('finalizar')
                                        ->label('Finalização')
                                        ->rows(4)
                                        ->autosize()
                                        ->columnSpanFull()
                                        ->placeholder('Informações de finalização'),

                                    TextInput::make('anexo')
                                        ->label('Anexo')
                                        ->maxLength(255)
                                        ->placeholder('Caminho ou referência do anexo'),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->persistTabInQueryString()
                ->columnSpanFull(),
        ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id', '#', true),

                TableColumns::text('idSituacaoRef.Descricao', 'Situação')
                    ->badge()
                    ->color(fn (?string $state) => match (mb_strtolower($state ?? '')) {
                        'pendente' => 'warning',
                        'aprovado', 'deferido', 'ativo' => 'success',
                        'cancelado', 'indeferido' => 'danger',
                        default => 'gray',
                    }),

                TableColumns::text('idSolicitanteRef.name', 'Solicitante'),

                TableColumns::text('unidadeSolicitanteRef.UnidadeOrganizacional', 'Unidade')
                    ->description(fn ($record) :string => $record->setorSolicitanteRef->Setor ?? '-'),

                TableColumns::text('regiaoRef.sigla', 'Região')
                    ->badge()
                    ->color('info'),

                TableColumns::date('data_inicio', 'Data início'),

                TableColumns::text('local_saida', 'Local de saída'),

                TableColumns::text('justificativa', 'Detalhamento'),
            ])
            ->recordActions([
                ...TableDefaults::actions(),
                self::agendarTableAction()
            ])
            ->filters([
                SelectFilter::make('situacao_filter')
                    ->label('Situação')
                    ->columnSpan(2)
                    ->relationship('idSituacaoRef', 'Descricao')
                    ->default(6)
            ], Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('date_time', 'desc');
    }

    private static function agendarTableAction(): Action
    {
        return Action::make('agendar_veiculo')
            ->hiddenLabel()
            ->tooltip('Agendar')
            ->icon('heroicon-o-calendar-days')
            ->modalHeading(fn ($record) => 'Agendar veículo #' . $record->id)
            ->modalSubmitActionLabel('Agendar')
            ->schema([
                Select::make('condutor_veiculo')
                    ->required()
                    ->label('Condutor')
                    ->options(Equipe::all()->pluck('idPessoaRef.name', 'id')),
                Select::make('veiculo')
                    ->label('Veículo')
                    ->required()
                    ->options(Frota::all()->pluck('descricao', 'id')),
                Textarea::make('observacao')
                    ->required()
                    ->label('Observação')
                    ->rows(4)
            ])
            ->action(function (array $data, $record): void {
                if ($data) {
                    Recursos::create([
                        'condutor' => $data['condutor_veiculo'],
                        'veiculo' => $data['veiculo'],
                        'id_solicitacao' => $record->id,
                        'observacao' => $data['observacao'],
                    ]);

                    $record->update(['id_situacao' => 8]);

                    Notification::make()
                        ->color('success')
                        ->icon(Heroicon::CalendarDays)
                        ->title('Agendamento concluído!')
                        ->send();
                } else {
                    Notification::make()
                        ->color('danger')
                        ->icon(Heroicon::ExclamationCircle)
                        ->title('Erro ao concluir agendamento')
                        ->send();
                }
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSolicitacaos::route('/'),
            'create' => CreateSolicitacao::route('/create'),
            'edit' => EditSolicitacao::route('/{record}/edit'),
        ];
    }
}
