<?php

namespace App\Filament\Livewire\Patrimonio\BensImoveis;

use App\Filament\Resources\Patrimonio\BensImoveis\ReavaliacaoResource;
use App\Filament\Support\TableModalComponent;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Tables\Table;

class ReavaliacoesModal extends TableModalComponent implements HasActions
{
    use InteractsWithActions;

    public int $imovelId;

    public function mount(int $imovelId): void
    {
        $this->imovelId = $imovelId;
    }

    public function table(Table $table): Table
    {
        return ReavaliacaoResource::table($table)
            ->query(
                ReavaliacaoResource::getEloquentQuery()
                    ->where('Id_imovel', $this->imovelId)
            );
    }
}
