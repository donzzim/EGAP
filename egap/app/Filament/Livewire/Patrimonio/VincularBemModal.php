<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Support\FormModalComponent;
use App\Models\Patrimonio\BensMoveis\Baixa;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\ItemBaixa;
use App\Models\Patrimonio\BensMoveis\SituacaoBemMovel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Throwable;

class VincularBemModal extends FormModalComponent
{
    public ?BemMovel $bem = null;

    public function mount(int $numPatrimonio, string $parentLivewireId): void
    {
        $this->parentLivewireId = $parentLivewireId;

        $this->bem = BemMovel::query()->where('NumPatrimonio', $numPatrimonio)->first();

        $this->form->fill([
            'Detalhes' => $this->bem?->Observacao,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_baixa')
                    ->label('Processo')
                    ->placeholder('Selecione o processo de baixa')
                    ->options(fn (): array => Baixa::query()
                        ->whereNull('DataBaixa')
                        ->orderByDesc('id')
                        ->get(['id', 'NumeroProcesso', 'Requisitante'])
                        ->mapWithKeys(fn (Baixa $baixa): array => [
                            $baixa->id => "{$baixa->NumeroProcesso} - {$baixa->Requisitante}",
                        ])
                        ->toArray()
                    )
                    ->searchable()
                    ->native(false)
                    ->required(),

                Select::make('id_situacao')
                    ->label('Tipo da Baixa')
                    ->placeholder('Selecione o tipo da baixa')
                    ->options(fn (): array => SituacaoBemMovel::query()
                        ->orderBy('descricao')
                        ->pluck('descricao', 'id')
                        ->toArray()
                    )
                    ->searchable()
                    ->native(false)
                    ->required(),

                Textarea::make('Detalhes')
                    ->label('Detalhes')
                    ->placeholder('Informe detalhes sobre a baixa deste bem')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->columns(1)
            ->statePath('data');
    }

    protected function submitMethod(): string
    {
        return 'vincular';
    }

    protected function submitLabel(): string
    {
        return 'Vincular Bem';
    }

    public function vincular(): void
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
                ItemBaixa::create([
                    'id_baixa' => $data['id_baixa'],
                    'id_bem' => $this->bem->id,
                    'id_situacao' => $data['id_situacao'],
                ]);

                $this->bem->update([
                    'Observacao' => $data['Detalhes'],
                ]);
            });
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Não foi possível vincular o bem.')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Bem vinculado com sucesso.')
            ->success()
            ->send();

        $this->dispatch('bem-movel-vinculado');
        $this->fecharModal();
    }
}
