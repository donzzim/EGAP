<?php

namespace App\Filament\Livewire\Patrimonio\BensImoveis;

use App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource;
use App\Filament\Support\TableModalComponent;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Tables\Table;

class DepreciacaoImovelModal extends TableModalComponent implements HasActions
{
    use InteractsWithActions;

    public int $imovelId;

    public function mount(int $imovelId): void
    {
        $this->imovelId = $imovelId;

        $this->tableFilters = [
            'Id_imovel' => ['value' => $this->imovelId],
        ];
    }

    public function table(Table $table): Table
    {
        return DepreciacaoResource::table($table)
            ->query(
                DepreciacaoResource::getEloquentQuery()
                    ->where('Id_imovel', $this->imovelId)
            );
    }
}
