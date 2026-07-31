<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource;
use App\Filament\Support\TableModalComponent;
use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use Filament\Tables\Table;

class ValidarTermoModal extends TableModalComponent
{
    public int $termoId;

    public function mount(int $termoId): void
    {
        $this->termoId = $termoId;
    }

    public function table(Table $table): Table
    {
        return ValidarTermoResource::table($table)
            ->query(
                ArquivoDigital::query()->where('termo', $this->termoId)
            );
    }
}
