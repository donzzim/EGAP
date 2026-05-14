<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource\Pages;

use App\Filament\Resources\Patrimonio\BensIntangiveis\TipoBemIntangivelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoBemIntangivel extends EditRecord
{
    protected static string $resource = TipoBemIntangivelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
