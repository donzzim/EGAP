<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource\Pages;

use App\Filament\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource;
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
