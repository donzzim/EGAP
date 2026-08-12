<?php

namespace App\Filament\Livewire\Patrimonio\BensMoveis;

use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource;
use App\Filament\Support\TableModalComponent;
use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Tables\Table;

class ValidarTermoModal extends TableModalComponent implements HasActions
{
    use InteractsWithActions;

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
