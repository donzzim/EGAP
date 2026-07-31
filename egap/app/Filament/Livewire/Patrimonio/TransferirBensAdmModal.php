<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Support\FormModalComponent;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Cadastro\ComplementoSetor;
use App\Models\Cadastro\Setores;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\TransferenciaBemMovel;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransferirBensAdmModal extends FormModalComponent
{
    public ?BemMovel $bem = null;

    public function mount(int $numPatrimonio, string $parentLivewireId): void
    {
        $this->parentLivewireId = $parentLivewireId;

        $this->bem = BemMovel::query()->where('NumPatrimonio', $numPatrimonio)->first();

        $this->form->fill([
            'UnidadeJudiciaria' => $this->bem?->UnidadeJudiciaria,
            'Setor' => $this->bem?->Setor,
            'ComplementoSetor' => $this->bem?->ComplementoSetor,
            'Andar' => $this->bem?->AndarSetor,
            'Observacao' => $this->bem?->Observacao,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Localização do Bem')
                    ->description('Informe a unidade judiciária e o setor onde o bem será transferido.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Select::make('UnidadeJudiciaria')
                            ->label('Unidade Judiciária')
                            ->placeholder('Selecione a unidade judiciária')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->native(false)
                            ->options(fn () => Setores::query()
                                ->whereColumn('id', 'CodigodaUO')
                                ->orderBy('UnidadeOrganizacional')
                                ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                ->toArray()
                            )
                            ->afterStateUpdated(fn (Set $set) => $set('Setor', null))
                            ->columnSpan(1),

                        Select::make('Setor')
                            ->label('Setor')
                            ->placeholder(fn (Get $get) => blank($get('UnidadeJudiciaria'))
                                    ? 'Selecione primeiro a unidade judiciária'
                                    : 'Selecione o setor'
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (Get $get) => Setores::query()
                                ->when(
                                    $get('UnidadeJudiciaria'),
                                    fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                )
                                ->orderBy('Setor')
                                ->pluck('Setor', 'id')
                                ->toArray()
                            )
                            ->disabled(fn (Get $get) => blank($get('UnidadeJudiciaria')))
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Detalhes da Localização')
                    ->description('Informe detalhes adicionais sobre a localização física do bem.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Select::make('ComplementoSetor')
                            ->label('Complemento')
                            ->placeholder('Selecione o complemento')
                            ->options(fn (): array => ComplementoSetor::query()
                                ->orderBy('descricao')
                                ->pluck('descricao', 'id')
                                ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->columnSpan(2),

                        Select::make('Andar')
                            ->label('Andar')
                            ->placeholder('Informe o andar')
                            ->options([
                                'SUB-SOLO' => 'Sub-solo',
                                'TERREO' => 'Térreo',
                                '1' => '1º andar',
                                '2' => '2º andar',
                                '3' => '3º andar',
                                '4' => '4º andar',
                                '5' => '5º andar',
                                '6' => '6º andar',
                                '7' => '7º andar',
                                '8' => '8º andar',
                                '9' => '9º andar',
                                '10' => '10º andar',
                                '11' => '11º andar',
                                '12' => '12º andar',
                                '13' => '13º andar',
                                '14' => '14º andar',
                                '15' => '15º andar',
                                '16' => '16º andar',
                                '17' => '17º andar',
                                '18' => '18º andar',
                                '19' => '19º andar',
                                '20' => '20º andar',
                            ])
                            ->native(false)
                            ->columnSpan(1),

                        Select::make('PedidoNo')
                            ->label('Pedido Nº')
                            ->placeholder('Selecione o pedido relacionado')
                            ->options(fn (): array => Pedidos::query()
                                ->orderByDesc('id')
                                ->limit(100)
                                ->get(['id', 'num_protocolo'])
                                ->mapWithKeys(fn (Pedidos $pedido): array => [
                                    $pedido->id => filled($pedido->num_protocolo)
                                        ? "{$pedido->id} - Protocolo {$pedido->num_protocolo}"
                                        : (string) $pedido->id,
                                ])
                                ->toArray()
                            )
                            ->searchable()
                            ->native(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Observações')
                    ->description('Adicione informações complementares sobre a transferência, se necessário.')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        RichEditor::make('Observacao')
                            ->label('')
                            ->placeholder('Digite aqui alguma observação ou informação adicional...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ])
            ->columns(1)
            ->statePath('data');
    }

    protected function submitMethod(): string
    {
        return 'transferir';
    }

    protected function submitLabel(): string
    {
        return 'Confirmar Transferência';
    }

    public function transferir(): void
    {
        if (! $this->bem) {
            Notification::make()
                ->title('Bem patrimonial não encontrado.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        try {
            DB::transaction(function () use ($data): void {
                TransferenciaBemMovel::create([
                    'NumPatrimonio' => $this->bem->NumPatrimonio,
                    'UnidadeAnterior' => $this->bem->UnidadeJudiciaria,
                    'SetorAnterior' => $this->bem->Setor,
                    'ComplementoAnterior' => $this->bem->ComplementoSetor,
                    'AndarAnterior' => $this->bem->AndarSetor,
                    'UnidadeAtual' => $data['UnidadeJudiciaria'],
                    'SetorAtual' => $data['Setor'],
                    'ComplementoAtual' => $data['ComplementoSetor'],
                    'AndarAtual' => $data['Andar'],
                    'pedido_no' => $data['PedidoNo'],
                ]);

                $this->bem->update([
                    'UnidadeJudiciaria' => $data['UnidadeJudiciaria'],
                    'Setor' => $data['Setor'],
                    'ComplementoSetor' => $data['ComplementoSetor'],
                    'AndarSetor' => $data['Andar'],
                    'Observacao' => $data['Observacao'],
                ]);
            });
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Não foi possível transferir o bem.')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Bem transferido com sucesso.')
            ->success()
            ->send();

        $this->dispatch('bem-movel-transferido');
        $this->fecharModal();
    }
}
