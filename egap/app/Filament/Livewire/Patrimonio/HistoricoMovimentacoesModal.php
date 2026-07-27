<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Resources\Patrimonio\BensMoveis\TransferenciaBemResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HistoricoMovimentacoesModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $numPatrimonio;

    public function mount(int $numPatrimonio): void
    {
        $this->numPatrimonio = $numPatrimonio;
    }

    public function table(Table $table): Table
    {
        return TransferenciaBemResource::table($table)
            ->query(
                TransferenciaBemResource::getEloquentQuery()
                    ->where('NumPatrimonio', $this->numPatrimonio)
            );
    }

    public function render(): View
    {
        return view('livewire.patrimonio.modal');
    }
}
