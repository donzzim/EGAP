<?php

namespace App\Filament\Livewire\Externo\Agendamento;

use App\Models\Agendamento\Regiao;
use App\Models\Agendamento\Solicitacao;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Throwable;

class SolicitacaoVeiculoForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    use RestrictsFileUploadsToSchemaComponents;

    protected const DIAS_UTEIS_ANTECEDENCIA_MINIMA = 3;

    protected const TIPO_AGENDAMENTO_VEICULOS = 1;

    protected const SITUACAO_EM_ANALISE = 6;

    public ?array $dataIdaVolta = [];

    public ?array $dataIda = [];

    public function mount(): void
    {
        $this->formIdaVolta->fill($this->getDefaultFormStateIdaVolta());
        $this->formIda->fill($this->getDefaultFormStateIda());
    }

    public function formIdaVolta(Schema $schema): Schema
    {
        return $schema
            ->components($this->camposIdaEVolta())
            ->statePath('dataIdaVolta');
    }

    public function formIda(Schema $schema): Schema
    {
        return $schema
            ->components($this->camposIda())
            ->statePath('dataIda');
    }

    protected function camposIdaEVolta(): array
    {
        return [
            Section::make('Saída')
                ->description('Data, local de saída e passageiros que utilizarão o veículo.')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    DateTimePicker::make('datetime_saida')
                        ->label('Data e hora da saída')
                        ->native(false)
                        ->seconds(false)
                        ->minDate(now())
                        ->required()
                        ->live()
                        ->columnSpan(1),

                    TextInput::make('origem')
                        ->label('Local de saída')
                        ->placeholder('Informe o local de saída')
                        ->maxLength(255)
                        ->required()
                        ->columnSpan(1),

                    TagsInput::make('passageiros')
                        ->label('Passageiros')
                        ->placeholder('Digite o nome e pressione Enter')
                        ->splitKeys([','])
                        ->required()
                        ->columnSpanFull(),

                    Radio::make('motorista')
                        ->label('Veículo com motorista')
                        ->options([
                            'Sim' => 'Sim',
                            'Nao' => 'Não',
                        ])
                        ->default('Nao')
                        ->inline()
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    FileUpload::make('doc_declaracao')
                        ->label('Documentos e declaração')
                        ->disk('public')
                        ->directory('agendamento/veiculos')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Retorno')
                ->description('Data, destino e contatos para o trajeto de volta.')
                ->icon('heroicon-o-arrow-uturn-left')
                ->schema([
                    DateTimePicker::make('datetime_retorno')
                        ->label('Data e hora de volta')
                        ->native(false)
                        ->seconds(false)
                        ->after('datetime_saida')
                        ->required()
                        ->columnSpan(1),

                    TagsInput::make('destino')
                        ->label('Destino')
                        ->placeholder('Digite o destino e pressione Enter')
                        ->splitKeys([','])
                        ->required()
                        ->columnSpan(1),

                    TextInput::make('telefone')
                        ->label('Telefone')
                        ->tel()
                        ->mask('****-****')
                        ->columnSpan(1),

                    TextInput::make('celular')
                        ->label('Celular')
                        ->tel()
                        ->mask('(**)*****-****')
                        ->required()
                        ->columnSpan(1),
                ])
                ->columns(2),

            Section::make('Motorista aguardando no destino')
                ->description('Informe se o motorista deverá permanecer no destino durante o atendimento.')
                ->icon('heroicon-o-clock')
                ->schema([
                    Radio::make('aguardar')
                        ->label('Motorista deverá aguardar no destino')
                        ->options([
                            'Sim' => 'Sim',
                            'Nao' => 'Não',
                        ])
                        ->default('Nao')
                        ->inline()
                        ->live()
                        ->columnSpanFull(),

                    Textarea::make('justificativa_motorista')
                        ->label('Justificativa e detalhamento da permanência')
                        ->rows(3)
                        ->visible(fn (Get $get): bool => $get('aguardar') === 'Sim')
                        ->required(fn (Get $get): bool => $get('aguardar') === 'Sim')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get): bool => $get('motorista') === 'Sim')
                ->columns(2),

            Section::make('Justificativa')
                ->schema([
                    Textarea::make('justificativa')
                        ->label('Justificativas e detalhamento do agendamento')
                        ->helperText('Informe além da justificativa todos os detalhes importantes para a execução do atendimento (ex.: quantidade de passageiros, nomes, tipo de material transportado etc).')
                        ->rows(4)
                        ->required()
                        ->columnSpanFull(),
                ]),

            Section::make('Justificativa de intempestividade')
                ->description('A solicitação está sendo feita com menos de '.self::DIAS_UTEIS_ANTECEDENCIA_MINIMA.' dias úteis de antecedência. Anexe a justificativa obrigatória.')
                ->icon('heroicon-o-exclamation-triangle')
                ->schema([
                    FileUpload::make('doc_just')
                        ->label('Anexo da justificativa')
                        ->disk('public')
                        ->directory('agendamento/veiculos')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->columnSpanFull(),

                    Textarea::make('justificativa_escrita_intempestividade')
                        ->label('Justificativa e informações')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get): bool => $this->exigeJustificativaIntempestividade($get('datetime_saida')))
                ->columns(1),
        ];
    }

    protected function camposIda(): array
    {
        return [
            Section::make('Deslocamento')
                ->description('Data, origem, destino e passageiros do trajeto somente de ida.')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    DateTimePicker::make('datetime_saida')
                        ->label('Data e hora da saída')
                        ->native(false)
                        ->seconds(false)
                        ->minDate(now())
                        ->required()
                        ->live()
                        ->columnSpan(1),

                    TextInput::make('origem')
                        ->label('Local de saída')
                        ->placeholder('Informe o local de saída')
                        ->maxLength(255)
                        ->required()
                        ->columnSpan(1),

                    TagsInput::make('destino')
                        ->label('Destino')
                        ->placeholder('Digite o destino e pressione Enter')
                        ->splitKeys([','])
                        ->required()
                        ->columnSpan(1),

                    TagsInput::make('passageiros')
                        ->label('Nomes dos passageiros (caso existam)')
                        ->placeholder('Digite o nome e pressione Enter')
                        ->splitKeys([','])
                        ->columnSpan(1),

                    TextInput::make('telefone')
                        ->label('Telefone')
                        ->tel()
                        ->mask('****-****')
                        ->columnSpan(1),

                    TextInput::make('celular')
                        ->label('Celular')
                        ->tel()
                        ->mask('(**)*****-****')
                        ->columnSpan(1),

                    FileUpload::make('doc_declaracao')
                        ->label('Documentos e declaração')
                        ->disk('public')
                        ->directory('agendamento/veiculos')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Justificativa')
                ->schema([
                    Textarea::make('justificativa')
                        ->label('Justificativa e informações')
                        ->helperText('Informe além da justificativa todos os detalhes importantes para a execução do atendimento (ex.: quantidade de passageiros, nomes, tipo de material transportado etc).')
                        ->rows(4)
                        ->required()
                        ->columnSpanFull(),
                ]),

            Section::make('Justificativa de intempestividade')
                ->description('A solicitação está sendo feita com menos de '.self::DIAS_UTEIS_ANTECEDENCIA_MINIMA.' dias úteis de antecedência. Anexe a justificativa obrigatória.')
                ->icon('heroicon-o-exclamation-triangle')
                ->schema([
                    FileUpload::make('doc_just')
                        ->label('Anexo da justificativa')
                        ->disk('public')
                        ->directory('agendamento/veiculos')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->columnSpanFull(),

                    Textarea::make('justificativa_escrita_intempestividade')
                        ->label('Justificativa e informações')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get): bool => $this->exigeJustificativaIntempestividade($get('datetime_saida')))
                ->columns(1),
        ];
    }

    /**
     * Replica o fluxo "confirma-both" do legado (solicitacao_veiculo.php): quando o
     * motorista leva mas não aguarda no destino, a ida e a volta viram duas
     * solicitações independentes (dois despachos de frota); nos demais casos, uma
     * única solicitação com início e término.
     */
    public function enviarSolicitacaoIdaVolta(): void
    {
        $data = $this->formIdaVolta->getState();

        $solicitante = $this->resolverSolicitante();

        if (! $solicitante) {
            $this->notificarSolicitanteNaoIdentificado();

            return;
        }

        [$dataSaida, $horaSaida] = $this->separarDataHora($data['datetime_saida']);
        [$dataRetorno, $horaRetorno] = $this->separarDataHora($data['datetime_retorno']);

        $aguardar = $data['motorista'] === 'Sim' ? ($data['aguardar'] ?? 'Nao') : 'Nao';

        $complementoBase = [
            'telefone' => $data['telefone'] ?? null,
            'celular' => $data['celular'] ?? null,
            'doc_declaracao' => $data['doc_declaracao'] ?? null,
            'doc_just' => $data['doc_just'] ?? null,
            'justificativa' => $data['justificativa'],
            'veiculo_motorista' => $data['motorista'],
            'aguardar_destino' => $aguardar,
            'passageiros' => $data['passageiros'] ?? [],
            'justificativa_intempestividade' => $data['justificativa_escrita_intempestividade'] ?? null,
            'justificativa_motorista' => $data['justificativa_motorista'] ?? null,
        ];

        $dadosComuns = [
            'tipo' => self::TIPO_AGENDAMENTO_VEICULOS,
            'id_situacao' => self::SITUACAO_EM_ANALISE,
            'id_solicitante' => $solicitante['usuario']->id,
            'setor_solicitante' => $solicitante['setor'],
            'regiao' => $solicitante['regiao'],
            'unidade_solicitante' => $solicitante['unidade'],
        ];

        try {
            if ($data['motorista'] === 'Sim' && $aguardar === 'Nao') {
                [$ida, $volta] = DB::transaction(function () use ($data, $dadosComuns, $complementoBase, $dataSaida, $horaSaida, $dataRetorno, $horaRetorno): array {
                    $ida = Solicitacao::create([
                        ...$dadosComuns,
                        'data_inicio' => $dataSaida,
                        'hora_inicio' => $horaSaida,
                        'local_saida' => $data['origem'],
                        'justificativa' => [...$complementoBase, 'destino' => $data['destino'] ?? []],
                    ]);

                    $volta = Solicitacao::create([
                        ...$dadosComuns,
                        'data_inicio' => $dataRetorno,
                        'hora_inicio' => $horaRetorno,
                        'local_saida' => collect($data['destino'] ?? [])->last() ?? $data['origem'],
                        'justificativa' => [...$complementoBase, 'destino' => [$data['origem']]],
                    ]);

                    return [$ida, $volta];
                });

                $this->notificarSucesso("Solicitações nº {$ida->id} e nº {$volta->id} realizadas!");
            } else {
                $solicitacao = DB::transaction(fn (): Solicitacao => Solicitacao::create([
                    ...$dadosComuns,
                    'data_inicio' => $dataSaida,
                    'hora_inicio' => $horaSaida,
                    'data_termino' => $dataRetorno,
                    'hora_termino' => $horaRetorno,
                    'local_saida' => $data['origem'],
                    'justificativa' => [...$complementoBase, 'destino' => $data['destino'] ?? []],
                ]));

                $this->notificarSucesso("Solicitação nº {$solicitacao->id} realizada!");
            }

            $this->formIdaVolta->fill($this->getDefaultFormStateIdaVolta());
        } catch (Throwable $exception) {
            $this->notificarErro($exception);
        }
    }

    /**
     * Replica o fluxo "confirma-go" do legado: sempre uma única solicitação de
     * apenas ida, com motorista e sem espera no destino.
     */
    public function enviarSolicitacaoIda(): void
    {
        $data = $this->formIda->getState();

        $solicitante = $this->resolverSolicitante();

        if (! $solicitante) {
            $this->notificarSolicitanteNaoIdentificado();

            return;
        }

        [$dataInicio, $horaInicio] = $this->separarDataHora($data['datetime_saida']);

        try {
            $solicitacao = DB::transaction(fn (): Solicitacao => Solicitacao::create([
                'tipo' => self::TIPO_AGENDAMENTO_VEICULOS,
                'id_situacao' => self::SITUACAO_EM_ANALISE,
                'id_solicitante' => $solicitante['usuario']->id,
                'setor_solicitante' => $solicitante['setor'],
                'regiao' => $solicitante['regiao'],
                'unidade_solicitante' => $solicitante['unidade'],
                'data_inicio' => $dataInicio,
                'hora_inicio' => $horaInicio,
                'local_saida' => $data['origem'],
                'local_destino' => implode(', ', $data['destino'] ?? []),
                'justificativa' => [
                    'telefone' => $data['telefone'] ?? null,
                    'celular' => $data['celular'] ?? null,
                    'doc_declaracao' => $data['doc_declaracao'] ?? null,
                    'doc_just' => $data['doc_just'] ?? null,
                    'justificativa' => $data['justificativa'],
                    'veiculo_motorista' => 'Sim',
                    'aguardar_destino' => 'Nao',
                    'passageiros' => $data['passageiros'] ?? [],
                    'justificativa_intempestividade' => $data['justificativa_escrita_intempestividade'] ?? null,
                    'destino' => $data['destino'] ?? [],
                ],
            ]));

            $this->notificarSucesso("Solicitação nº {$solicitacao->id} realizada!");

            $this->formIda->fill($this->getDefaultFormStateIda());
        } catch (Throwable $exception) {
            $this->notificarErro($exception);
        }
    }

    /**
     * Equivalente a `$_SESSION["codigousuario"]`/`$_SESSION["codigosetor"]` do
     * legado: identifica o usuário autenticado e o setor da sua lotação mais
     * recente, e a partir dele resolve a unidade e a região da solicitação
     * (mesmo JOIN de mat_setores/age_regiao feito no PHP legado).
     *
     * @return array{usuario: UserEgap, setor: int, unidade: ?int, regiao: ?int}|null
     */
    protected function resolverSolicitante(): ?array
    {
        $usuario = UserEgap::currentAuthenticated();
        $setorId = $usuario?->lotacoes()->first()?->setor;

        if (! $usuario || blank($setorId)) {
            return null;
        }

        $unidade = Setores::query()->find($setorId)?->CodigodaUO;
        $regiaoId = filled($unidade)
            ? Regiao::query()->where('unidade', $unidade)->value('id')
            : null;

        return [
            'usuario' => $usuario,
            'setor' => (int) $setorId,
            'unidade' => $unidade,
            'regiao' => $regiaoId,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function separarDataHora(string $dataHora): array
    {
        $carbon = Carbon::parse($dataHora);

        return [$carbon->toDateString(), $carbon->format('H:i:s')];
    }

    protected function exigeJustificativaIntempestividade(?string $dataHoraSaida): bool
    {
        if (blank($dataHoraSaida)) {
            return false;
        }

        return Carbon::parse($dataHoraSaida)->lt($this->limiteAntecedenciaMinima());
    }

    protected function limiteAntecedenciaMinima(): Carbon
    {
        $data = now();
        $diasUteis = self::DIAS_UTEIS_ANTECEDENCIA_MINIMA;

        while ($diasUteis > 0) {
            $data = $data->addDay();

            if (! $data->isWeekend()) {
                $diasUteis--;
            }
        }

        return $data;
    }

    protected function notificarSucesso(string $mensagem): void
    {
        Notification::make()
            ->title($mensagem)
            ->success()
            ->send();
    }

    protected function notificarSolicitanteNaoIdentificado(): void
    {
        Notification::make()
            ->title('Não foi possível identificar seu setor de lotação.')
            ->body('Entre em contato com o suporte para regularizar seu cadastro antes de enviar a solicitação.')
            ->danger()
            ->send();
    }

    protected function notificarErro(Throwable $exception): void
    {
        report($exception);

        Notification::make()
            ->title('Erro ao enviar a solicitação.')
            ->body($exception->getMessage())
            ->danger()
            ->send();
    }

    protected function getDefaultFormStateIdaVolta(): array
    {
        return [
            'motorista' => 'Nao',
            'aguardar' => 'Nao',
        ];
    }

    protected function getDefaultFormStateIda(): array
    {
        return [];
    }

    public function render(): View
    {
        return view('livewire.externo.form');
    }
}
