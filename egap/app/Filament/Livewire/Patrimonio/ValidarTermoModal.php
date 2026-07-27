<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource;
use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ValidarTermoModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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

    public function render(): View
    {
        return view('livewire.patrimonio.modal');
    }
}
