<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class ListBemMovels extends ListRecords
{
    protected static string $resource = BemMovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }

    #[On('bem-movel-transferido')]
    public function refreshBensMoveisTable(): void
    {
        // Apenas força o rerender após uma transferência.
    }

    // Mantém a identidade estável das linhas durante atualizações do Livewire.
    public function getTableRecordKey(Model|array $record): string
    {
        return (string) $record->id;
    }
}
