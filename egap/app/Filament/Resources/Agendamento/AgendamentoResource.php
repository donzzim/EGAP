<?php

namespace App\Filament\Resources\Agendamento;

use App\Filament\Resources\Agendamento\SolicitacaoResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Agendamento\Solicitacao;
use App\Models\Cadastro\Setores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AgendamentoResource extends Resource
{
    protected static ?string $model = Solicitacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Agendamento';
    protected static ?string $slug = 'agendamento/agendamento-de-veiculos';
    protected static ?string $modelLabel = 'Solicitação';
    protected static ?string $pluralModelLabel = 'Solicitações';
    protected static ?string $navigationLabel = 'Agendamento de Veículos';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Tabs::make('Solicitação')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Dados principais')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Forms\Components\Section::make('Informações gerais')
                                ->description('Dados centrais da solicitação e vínculos administrativos.')
                                ->schema([
                                    Forms\Components\Select::make('id_solicitante')
                                        ->label('Solicitante')
                                        ->relationship('idSolicitanteRef', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Selecione o solicitante')
                                        ->native(false)
                                        ->required()
                                        ->markAsRequired(),

                                    Forms\Components\Select::make('id_situacao')
                                        ->label('Situação')
                                        ->relationship('idSituacaoRef', 'Descricao')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Selecione a situação')
                                        ->native(false)
                                        ->required()
                                        ->markAsRequired(),

                                    Forms\Components\Select::make('tipo')
                                        ->label('Tipo')
                                        ->options([
                                            '1' => 'Agendamento de Veículos',
                                            '2' => 'Transporte de Carga',
                                        ])
                                        ->required()
                                        ->native(false)
                                        ->markAsRequired()
                                        ->placeholder('Informe o tipo da solicitação'),

                                    Forms\Components\Select::make('regiao')
                                        ->label('Região')
                                        ->relationship('regiaoRef', 'regiao')
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Selecione a região')
                                        ->native(false)
                                        ->required()
                                        ->markAsRequired(),

                                    Forms\Components\Select::make('unidade_solicitante')
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

                                    Forms\Components\Select::make('setor_solicitante')
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

                    Forms\Components\Tabs\Tab::make('Período')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Forms\Components\Section::make('Datas e horários')
                                ->description('Defina o intervalo previsto para a solicitação.')
                                ->schema([
                                    Forms\Components\DatePicker::make('data_inicio')
                                        ->label('Data início')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->closeOnDateSelection()
                                        ->required()
                                        ->markAsRequired(),

                                    Forms\Components\TimePicker::make('hora_inicio')
                                        ->label('Hora início')
                                        ->seconds(false)
                                        ->required()
                                        ->markAsRequired(),

                                    Forms\Components\DatePicker::make('data_termino')
                                        ->label('Data término')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->closeOnDateSelection()
                                        ->required()
                                        ->markAsRequired(),

                                    Forms\Components\TimePicker::make('hora_termino')
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

                    Forms\Components\Tabs\Tab::make('Locais e observações')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Forms\Components\Section::make('Deslocamento')
                                ->description('Informações de origem e destino.')
                                ->schema([
                                    Forms\Components\TextInput::make('local_saida')
                                        ->label('Local de saída')
                                        ->placeholder('Informe o local de saída')
                                        ->maxLength(255)
                                        ->required()
                                        ->markAsRequired(),

                                    Forms\Components\TextInput::make('local_destino')
                                        ->label('Local de destino')
                                        ->placeholder('Informe o local de destino')
                                        ->maxLength(255)
                                        ->required()
                                        ->markAsRequired(),
                                ])
                                ->columns(2),

                            Forms\Components\Section::make('Observações e justificativas')
                                ->description('Detalhamentos complementares da solicitação.')
                                ->schema([
                                    Forms\Components\Textarea::make('justificativa')
                                        ->label('Justificativa')
                                        ->rows(5)
                                        ->autosize()
                                        ->columnSpanFull()
                                        ->placeholder('Descreva a justificativa da solicitação')
                                        ->required()
                                        ->markAsRequired(),

                                    Forms\Components\Textarea::make('motivo_cancelamento')
                                        ->label('Motivo do cancelamento')
                                        ->rows(4)
                                        ->autosize()
                                        ->columnSpanFull()
                                        ->placeholder('Informe o motivo do cancelamento, se houver'),

                                    Forms\Components\Textarea::make('motivo_edicao')
                                        ->label('Motivo da edição')
                                        ->rows(4)
                                        ->autosize()
                                        ->columnSpanFull()
                                        ->placeholder('Informe o motivo da edição, se houver'),

                                    Forms\Components\Textarea::make('finalizar')
                                        ->label('Finalização')
                                        ->rows(4)
                                        ->autosize()
                                        ->columnSpanFull()
                                        ->placeholder('Informações de finalização'),

                                    Forms\Components\TextInput::make('anexo')
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
            ->actions([
                ...TableDefaults::actions(),
                self::agendarTableAction()
            ])
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
            ->form([
                TextInput::make('agendar_codigo')
                    ->label('Código'),
                TextInput::make('agendar_descricao')
                    ->label('Descrição'),
                Select::make('agendar_situacao')
                    ->label('Situação')
                    ->options([
                        "Qualificação Técnica" => "Qualificação Técnica",
                        "Qualificação Econômico-Financeira" => "Qualificação Econômico-Financeira"
                    ])
            ])
            ->action(function (array $data): void {
                //
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitacaos::route('/'),
            'create' => Pages\CreateSolicitacao::route('/create'),
            'edit' => Pages\EditSolicitacao::route('/{record}/edit'),
        ];
    }
}
