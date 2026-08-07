<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Resources\Patrimonio\BensMoveis\TransferenciaBemResource;
use App\Filament\Support\TableModalComponent;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Tables\Table;

class HistoricoMovimentacoesModal extends TableModalComponent implements HasActions
{
    use InteractsWithActions;

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
}
