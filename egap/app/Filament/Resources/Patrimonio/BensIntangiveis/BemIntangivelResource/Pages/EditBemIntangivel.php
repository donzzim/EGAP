<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBemIntangivel extends EditRecord
{
    protected static string $resource = BemIntangivelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
