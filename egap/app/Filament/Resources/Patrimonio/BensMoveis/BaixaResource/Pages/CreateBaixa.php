<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\BaixaResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\BaixaResource;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use Filament\Resources\Pages\CreateRecord;

class CreateBaixa extends CreateRecord
{
    protected static string $resource = BaixaResource::class;

    protected function afterCreate(): void
    {
        $baixa = $this->record;

        foreach ($baixa->itens as $item) {
            BemMovel::where('id', $item->id_bem)
                ->where('SituacaoBem', 1)
                ->update([
                    'SituacaoBem' => 7,
                    'ProcessoBaixa' => $baixa->NumeroProcesso,
                ]);
        }
    }
}
