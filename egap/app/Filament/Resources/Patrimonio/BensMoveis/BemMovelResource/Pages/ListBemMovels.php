<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListBemMovels extends ListRecords
{
    protected static string $resource = BemMovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo'),
        ];
    }

    // Mantém a identidade estável das linhas durante atualizações do Livewire.
    public function getTableRecordKey(Model $record): string
    {
        return (string) $record->id;
    }

    // Evita a consulta de contagem total na tabela com grande volume de registros.
    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->simplePaginate($this->getTableRecordsPerPage() == 'all' ? 10 : $this->getTableRecordsPerPage());
    }
}
