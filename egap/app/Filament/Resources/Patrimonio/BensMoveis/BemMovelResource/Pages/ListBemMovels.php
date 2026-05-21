<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\BemMovelResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListBemMovels extends ListRecords
{
    protected static string $resource = BemMovelResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    // 🌟 A CURA DO COMPONENTE GIGANTE: Força o Livewire a indexar o DOM de cada linha pelo ID real!
    // Quando você salvar a alteração e a linha atualizar, o JavaScript NÃO perde o botão Opções.
    public function getTableRecordKey(Model $record): string
    {
        return (string) $record->id;
    }

    // Mantém a paginação simples e ultra leve para 185 mil linhas
    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->simplePaginate($this->getTableRecordsPerPage() == 'all' ? 10 : $this->getTableRecordsPerPage());
    }
}
