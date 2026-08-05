<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Resources\Patrimonio\BensMoveis\DepreciacaoResource;
use App\Filament\Support\TableModalComponent;
use Filament\Tables\Table;

class DepreciacaoModal extends TableModalComponent
{
    public int $bemMovelId;

    public function mount(int $bemMovelId): void
    {
        $this->bemMovelId = $bemMovelId;

        $this->tableFilters = [
            'patrimonio' => ['patrimonio' => $this->bemMovelId],
        ];
    }

    public function table(Table $table): Table
    {
        return DepreciacaoResource::table($table)
            ->query(
                DepreciacaoResource::getEloquentQuery()
                    ->where('patrimonio', $this->bemMovelId)
            );
    }
}
