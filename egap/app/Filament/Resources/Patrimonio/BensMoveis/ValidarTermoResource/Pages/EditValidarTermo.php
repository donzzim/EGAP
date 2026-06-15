<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource;
use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use Filament\Resources\Pages\EditRecord;

class EditValidarTermo extends EditRecord
{
    protected static string $resource = ValidarTermoResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['arquivo_digital'] = ArquivoDigital::caminhoArquivoDigitalNoDisco(
            $data['arquivo_digital'] ?? null,
        );

        return $data;
    }
}
